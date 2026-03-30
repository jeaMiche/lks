<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LoanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'amount'        => fake()->numberBetween(5_000_000, 50_000_000),
            'tenor_months'  => fake()->randomElement([6, 12, 24, 36]),
            'interest_rate' => fake()->randomElement([9, 10, 11, 12, 13, 14]), // ← tidak boleh 0
            'purpose'       => fake()->sentence(10),
            'status'        => 'pending',
        ];
    }
}