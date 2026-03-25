<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TodoItem extends Model
{
    use HasFactory, Auditable;

    protected $fillable = ['todo_id', 'name', 'is_done', 'sort_order', 'completed_at', 'due_date', 'category'];

    protected $casts = [
        'is_done' => 'boolean',
        'completed_at' => 'datetime',
        'due_date' => 'date',
    ];

    public function todo(): BelongsTo
    {
        return $this->belongsTo(Todo::class);
    }
}
