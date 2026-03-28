<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EconomySnapshot extends Model
{
    protected $fillable = [
        'month',
        'year',
        'snapshot_data',
        'total_income',
        'total_expenses',
        'total_savings',
    ];

    protected $casts = [
        'snapshot_data' => 'array',
        'total_income' => 'decimal:2',
        'total_expenses' => 'decimal:2',
        'total_savings' => 'decimal:2',
    ];
}
