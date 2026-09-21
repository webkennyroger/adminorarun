<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class StoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users
        $users = User::all();

        if ($users->isEmpty()) {
            return;
        }

        foreach ($users as $user) {
            // 50% chance of having a story
            // 50% chance of having a story
            if (rand(0, 1)) {
                // \App\Models\Story::factory()->create(['user_id' => $user->id]);
            }
        }
    }
}
