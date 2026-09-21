<?php

namespace App\Console\Commands;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateNicknames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:generate-nicknames';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate unique nicknames for users who don\'t have one';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::whereHas('profile', function ($q) {
            $q->whereNull('nickname');
        })->orWhereDoesntHave('profile')->get();

        $this->info("Found {$users->count()} users without nicknames");

        foreach ($users as $user) {
            // Generate nickname from name
            $baseName = Str::slug(Str::lower($user->name));
            $nickname = $baseName;
            $counter = 1;

            // Ensure uniqueness
            while (Profile::where('nickname', $nickname)->exists()) {
                $nickname = $baseName.$counter;
                $counter++;
            }

            // Create or update profile
            if (! $user->profile) {
                $user->profile()->create([
                    'nickname' => $nickname,
                    'role' => 'user',
                    'plan' => 'free',
                ]);
            } else {
                $user->profile->update(['nickname' => $nickname]);
            }

            $this->line("✓ {$user->name} → @{$nickname}");
        }

        $this->info("\n✅ All users now have unique nicknames!");
    }
}
