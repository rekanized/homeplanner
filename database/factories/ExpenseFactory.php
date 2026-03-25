<?php

namespace Database\Factories;

use App\Models\Expense;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'amount' => $this->faker->randomFloat(2, 10, 500),
            'category' => 'Food',
            'payer' => ['Me'],
            'split' => false,
            'delayed' => false,
            'handling' => null,
            'sort_order' => 0,
        ];
    }
}
