<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Chore extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'title',
        'description',
        'score',
        'user_id',
        'is_completed',
        'completed_at',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the user assigned to the chore.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
