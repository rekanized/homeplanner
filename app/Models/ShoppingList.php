<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Auditable;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShoppingList extends Model
{
    use HasFactory, Auditable;

    protected $fillable = ['name', 'sort_order'];

    public function items(): HasMany
    {
        return $this->hasMany(ShoppingItem::class)->orderBy('sort_order');
    }
}
