<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppVersion extends Model
{
    protected $fillable = [
        'version',
        'changes',
        'released_at',
    ];

    protected $casts = [
        'changes' => 'array',
        'released_at' => 'datetime',
    ];
}
