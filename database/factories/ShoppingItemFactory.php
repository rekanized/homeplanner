<?php

namespace Database\Factories;

use App\Models\ShoppingItem;
use App\Models\ShoppingList;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShoppingItemFactory extends Factory
{
    protected $model = ShoppingItem::class;

    public function definition(): array
    {
        return [
            'shopping_list_id' => ShoppingList::factory(),
            'name' => $this->faker->word(),
            'quantity' => $this->faker->numberBetween(1, 10),
            'is_checked' => false,
            'sort_order' => 0,
        ];
    }
}
