<?php

namespace Database\Factories;

use App\Models\SupportReply;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportReply>
 */
class SupportReplyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'message' => $this->faker->paragraph(),
            'attachment' => null, // or valid path if needed
        ];
    }
}
