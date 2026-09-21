<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Story;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();

        foreach ($users as $user) {
            // 1. Create 3 Activities per User
            $this->createActivities($user);

            // 2. Create 3 Stories per User
            $this->createStories($user);
        }
    }

    private function createActivities(User $user)
    {
        $activities = [
            [
                'title' => 'Corrida Matinal',
                'sport_type' => 'run',
                'distance' => 5.2, // km
                'duration' => 1800, // 30 min in seconds
                'calories' => 350,
                'description' => 'Ótimo ritmo hoje!',
                'start_time' => Carbon::now()->subDays(rand(0, 3))->subHours(rand(6, 10)),
            ],
            [
                'title' => 'Pedal de Fim de Tarde',
                'sport_type' => 'ride',
                'distance' => 20.5,
                'duration' => 3600, // 1h
                'calories' => 600,
                'description' => 'Vento contra na volta.',
                'start_time' => Carbon::now()->subDays(rand(1, 5))->subHours(rand(16, 19)),
            ],
            [
                'title' => 'Caminhada Regenerativa',
                'sport_type' => 'walk',
                'distance' => 3.0,
                'duration' => 2400, // 40 min
                'calories' => 150,
                'description' => 'Relaxando as pernas.',
                'start_time' => Carbon::now()->subDays(rand(0, 2))->subHours(rand(12, 14)),
            ],
        ];

        foreach ($activities as $data) {
            Activity::create([
                'user_id' => $user->id,
                'app_id' => 'mere_generated_'.uniqid(),
                'title' => $data['title'],
                'sport_type' => $data['sport_type'],
                'start_time' => $data['start_time'],
                'distance' => $data['distance'],
                'duration' => $data['duration'],
                'calories' => $data['calories'],
                'description' => $data['description'],
                'privacy' => 'public',
                'media' => [], // Empty array for now
                'polylines' => [], // Empty array for now
            ]);
        }
    }

    private function createStories(User $user)
    {
        for ($i = 0; $i < 3; $i++) {
            // Mix in already-expired stories (i === 0) alongside active ones,
            // so the admin Stories screen has real examples of both states.
            $expiresAt = $i === 0
                ? Carbon::now()->subHours(rand(1, 20))
                : Carbon::now()->addHours(rand(4, 24));

            Story::create([
                'user_id' => $user->id,
                'image_url' => 'https://picsum.photos/seed/'.$user->id.$i.'/1080/1920', // Vertical layout
                'expires_at' => $expiresAt,
                'created_at' => Carbon::now()->subMinutes(rand(10, 300)), // Posted recently
            ]);
        }
    }
}
