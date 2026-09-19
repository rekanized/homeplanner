<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TodoTag extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    protected static function booted(): void
    {
        static::saving(function (TodoTag $tag) {
            $tag->name = trim((string) $tag->name);
            $tag->normalized_name = mb_strtolower($tag->name);
        });
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(TodoItem::class, 'todo_item_tag');
    }
}
