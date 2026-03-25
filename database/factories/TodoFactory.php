<?php

namespace Database\Factories;

use App\Models\Todo;
use Illuminate\Database\Eloquent\Factories\Factory;

class TodoFactory extends Factory
{
    protected $model = Todo::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word . ' Tasks',
            'sort_order' => $this->faker->numberBetween(1, 100),
        ];
    }
}
