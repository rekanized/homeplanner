<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = ['name', 'amount', 'category', 'payer', 'split', 'delayed', 'handling', 'sort_order'];

    protected $casts = [
        'payer' => 'array',
        'split' => 'boolean',
        'delayed' => 'boolean',
    ];
}
