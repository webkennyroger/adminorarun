<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SocialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Ensure we have users
        // Ensure we have users
        if (User::count() < 10) {
            // \App\Models\User::factory(10)->create();
            $this->command->info('Skipping user factory generation in production.');
        }

        $me = User::first();
        $me->email = 'admin@admin.com'; // Ensure known user
        $me->save();

        $this->command->info("Main User: {$me->name} (ID: {$me->id})");

        // Make sure I follow someone
        $others = User::where('id', '!=', $me->id)->take(5)->get();
        foreach ($others as $other) {
            if (! $me->following()->where('following_id', $other->id)->exists()) {
                $me->following()->attach($other->id);
                $this->command->info("Followed: {$other->name}");
            }

            // Ensure they have activities
            if ($other->activities()->count() == 0) {
                Activity::create([
                    'user_id' => $other->id,
                    'app_id' => Str::uuid(),
                    'title' => 'Morning Run',
                    'sport_type' => 'Run',
                    'start_time' => now()->subHours(rand(1, 24)),
                    'distance' => 5000,
                    'duration' => 1800,
                    'calories' => 300,
                    'privacy' => 'public',
                    'polylines' => [],
                ]);
            }
        }

        // Make sure someone follows me
        $fans = User::where('id', '!=', $me->id)->skip(5)->take(3)->get();
        foreach ($fans as $fan) {
            if (! $fan->following()->where('following_id', $me->id)->exists()) {
                $fan->following()->attach($me->id);
                $this->command->info("New Fan: {$fan->name}");
            }
        }
    }
}
