<?php

namespace Database\Factories;

use App\Models\TodoItem;
use App\Models\Todo;
use Illuminate\Database\Eloquent\Factories\Factory;

class TodoItemFactory extends Factory
{
    protected $model = TodoItem::class;

    public function definition(): array
    {
        return [
            'todo_id' => Todo::factory(),
            'name' => $this->faker->sentence,
            'is_done' => $this->faker->boolean,
            'sort_order' => $this->faker->numberBetween(1, 100),
            'completed_at' => null,
        ];
    }
}
