<?php

namespace Database\Factories;

use App\Models\Goal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Goal>
 */
class GoalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'metric' => $this->faker->randomElement(['users', 'sales', 'expenses', 'revenue']),
            'period' => $this->faker->randomElement(['monthly', 'quarterly', 'semiannual', 'annual']),
            'target_value' => $this->faker->randomFloat(2, 1000, 100000),
            'start_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'end_date' => $this->faker->dateTimeBetween('+2 months', '+1 year'),
        ];
    }
}
