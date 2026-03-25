<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Auditable;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShoppingItem extends Model
{
    use HasFactory, Auditable;

    protected $fillable = ['shopping_list_id', 'name', 'quantity', 'is_checked', 'sort_order'];

    protected $casts = [
        'is_checked' => 'boolean',
    ];

    public function shoppingList(): BelongsTo
    {
        return $this->belongsTo(ShoppingList::class);
    }
}
