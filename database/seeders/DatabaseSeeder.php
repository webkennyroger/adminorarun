<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('Starting database seeding...');

        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            SubscriptionSeeder::class,
            ChallengeSeeder::class, // Triggers Notifications for Admin
            TrainingPlanSeeder::class,
            GoalSeeder::class,
            SupportSeeder::class,
            ScheduleSeeder::class,
            ActivitySeeder::class,
            FollowersSeeder::class,
            ContentSeeder::class,
            DemoUsageSeeder::class,
        ]);

        $this->command->info('Database seeded successfully!');
        $this->command->info('--------------------------------------');
        $this->command->info('Admin: orarunbr@gmail.com / 123456789');
        $this->command->info('Free User: usuario1@example.com / password');
        $this->command->info('Monthly (Premium) User: usuario5@example.com / password');
        $this->command->info('Annual User: usuario8@example.com / password');
        $this->command->info('--------------------------------------');
        $this->command->info('Notifications synced for generic Challenges!');
    }
}
