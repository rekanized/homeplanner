<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavingsBalance extends Model
{
    use Auditable, HasFactory;

    protected $fillable = ['name', 'amount', 'saver_id', 'location', 'sort_order'];

    public function saver()
    {
        return $this->belongsTo(User::class, 'saver_id');
    }
}
