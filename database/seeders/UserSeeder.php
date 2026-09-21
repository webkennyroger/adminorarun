<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating Users...');

        // 1. Admin User (Kenny Roger)
        $admin = User::firstOrCreate(
            ['email' => 'orarunbr@gmail.com'],
            [
                'name' => 'Kenny Roger',
                'password' => bcrypt('123456789'),
                'email_verified_at' => now(),
            ]
        );

        if (! $admin->profile) {
            Profile::create([
                'user_id' => $admin->id,
                'role' => 'admin',
                'plan' => 'annual',
                'phone' => '(11) 98765-4321',
            ]);
        }

        // 2. Generic Users (Total 10)
        // We will create 10 users with varying plans to test different scenarios
        for ($i = 1; $i <= 10; $i++) {
            $user = User::firstOrCreate(
                ['email' => "usuario{$i}@example.com"],
                [
                    'name' => "Usuário {$i}",
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                ]
            );

            if (! $user->profile) {
                // Distribute plans: 4 Free, 3 Monthly, 3 Annual
                $plan = 'free';
                if ($i > 4 && $i <= 7) {
                    $plan = 'monthly';
                }
                if ($i > 7) {
                    $plan = 'annual';
                }

                Profile::create([
                    'user_id' => $user->id,
                    'role' => 'user',
                    'plan' => $plan,
                    'phone' => '(11) 9'.rand(1000, 9999).'-'.rand(1000, 9999),
                ]);
            }
        }
    }
}
