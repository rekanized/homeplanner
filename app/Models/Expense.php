<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use Auditable, HasFactory;
    protected $fillable = ['name', 'amount', 'category', 'payer', 'split', 'delayed', 'handling', 'sort_order'];

    protected $casts = [
        'payer' => 'array',
        'split' => 'boolean',
        'delayed' => 'boolean',
    ];
}
