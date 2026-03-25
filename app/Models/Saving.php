<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Saving extends Model
{
    use Auditable, HasFactory;
    protected $fillable = ['name', 'amount', 'saver', 'location', 'sort_order'];
}
