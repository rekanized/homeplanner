<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Saving extends Model
{
    protected $fillable = ['name', 'amount', 'saver', 'location', 'month', 'year', 'handling'];
}
