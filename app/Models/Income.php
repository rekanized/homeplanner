<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    use Auditable, HasFactory;
    protected $fillable = ['name', 'amount', 'recipient_id', 'sort_order'];

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
}
