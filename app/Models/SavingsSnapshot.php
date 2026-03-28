<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavingsSnapshot extends Model
{
    protected $fillable = [
        'month',
        'year',
        'snapshot_data',
        'total_amount',
    ];

    protected $casts = [
        'snapshot_data' => 'array',
        'total_amount' => 'decimal:2',
    ];
}
