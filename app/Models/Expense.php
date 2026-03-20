<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = ['name', 'amount', 'category', 'payer', 'month', 'year', 'split', 'delayed', 'handling'];

    protected $casts = [
        'split' => 'boolean',
        'delayed' => 'boolean',
    ];
}
