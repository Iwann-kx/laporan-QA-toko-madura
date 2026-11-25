<?php

namespace Database\Factories;

use App\Models\FinanceEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinanceEntry>
 */
class FinanceEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tanggal' => fake()->date(),
            'type' => fake()->randomElement(['income', 'expense']),
            'category' => fake()->word(),
            'description' => fake()->sentence(),
            'amount' => fake()->numberBetween(1000, 100000),
            'created_by' => User::factory(),
        ];
    }
}
