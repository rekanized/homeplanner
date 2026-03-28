<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PredefinedChore extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'score',
        'recurrence_type',
        'recurrence_day',
        'assigned_user_ids',
        'last_generated_at',
    ];

    protected $casts = [
        'recurrence_day' => 'array',
        'assigned_user_ids' => 'array',
        'last_generated_at' => 'datetime',
    ];

    public function assignedUsers()
    {
        return User::whereIn('id', $this->assigned_user_ids ?? [])->get();
    }
}
