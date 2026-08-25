<?php

namespace App\Traits;

use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Throwable;

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
            if (empty($new)) {
                return;
            }
        } elseif ($event === 'created') {
            $new = $this->getAttributes();
        } elseif ($event === 'deleted') {
            $old = $this->getOriginal();
        }

        // Filter sensitive fields.
        $ignored = ['password', 'remember_token', 'created_at', 'updated_at', 'email_verified_at'];
        if ($new) {
            $new = array_diff_key($new, array_flip($ignored));
        }
        if ($old) {
            $old = array_diff_key($old, array_flip($ignored));
        }

        if ($this instanceof Setting && $this->hasSensitiveValue()) {
            if (array_key_exists('value', $new ?? [])) {
                $new['value'] = '[REDACTED]';
            }
            if (array_key_exists('value', $old ?? [])) {
                $old['value'] = '[REDACTED]';
            }
        }

        // Final check: if after filtering there's nothing new or old to log on an update, skip it
        if ($event === 'updated' && empty($new)) {
            return;
        }

        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => $event,
                'auditable_type' => get_class($this),
                'auditable_id' => $this->id,
                'old_values' => $old,
                'new_values' => $new,
                // Query strings can contain OAuth codes and other sensitive values.
                'url' => Str::limit(Request::url(), 255, ''),
                'ip_address' => Request::ip(),
                'user_agent' => Str::limit((string) Request::userAgent(), 255, ''),
            ]);
        } catch (Throwable $exception) {
            // Audit infrastructure must not make an otherwise valid user action fail.
            Log::warning('Unable to write audit log.', [
                'auditable_type' => get_class($this),
                'auditable_id' => $this->id,
                'event' => $event,
                'exception' => $exception::class,
            ]);
        }
    }
}
