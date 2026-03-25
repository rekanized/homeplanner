<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function ($model) {
            $model->logAudit('created');
        });

        static::updated(function ($model) {
            $model->logAudit('updated');
        });

        static::deleted(function ($model) {
            $model->logAudit('deleted');
        });
    }

    protected function logAudit(string $event)
    {
        $old = null;
        $new = null;

        if ($event === 'updated') {
            $new = $this->getDirty();
            $old = array_intersect_key($this->getOriginal(), $new);
            
            // Don't log if no actual changes (or only ignored fields)
            if (empty($new)) return;
        } elseif ($event === 'created') {
            $new = $this->getAttributes();
        } elseif ($event === 'deleted') {
            $old = $this->getOriginal();
        }

        // Filter sensitive fields
        $ignored = ['password', 'remember_token', 'created_at', 'updated_at', 'email_verified_at'];
        if ($new) $new = array_diff_key($new, array_flip($ignored));
        if ($old) $old = array_diff_key($old, array_flip($ignored));

        // Final check: if after filtering there's nothing new or old to log on an update, skip it
        if ($event === 'updated' && empty($new)) return;

        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => $event,
            'auditable_type' => get_class($this),
            'auditable_id' => $this->id,
            'old_values' => $old,
            'new_values' => $new,
            'url' => Request::fullUrl(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
