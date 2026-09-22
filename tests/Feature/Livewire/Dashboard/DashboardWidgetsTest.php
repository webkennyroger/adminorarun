<?php

use App\Livewire\Dashboard\Countries\TopCountries;
use App\Livewire\Dashboard\GoalChart;
use App\Livewire\Dashboard\SocialMediaStats;
use App\Livewire\Dashboard\Stats\Stats;
use App\Livewire\Dashboard\UserGrowthChart;
use App\Models\Goal;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

function makeAdmin(): User
{
    $admin = User::factory()->create();
    $admin->profile()->update(['role' => 'admin']);

    return $admin;
}

test('Stats widget computes user/plan counts and revenue goal from seeded data', function () {
    $admin = makeAdmin();

    // 2 premium (non-free) users, 3 free users (explicit 'free' plan), admin itself has no profile plan set (free by default)
    User::factory()->create()->profile()->update(['plan' => 'pro']);
    User::factory()->create()->profile()->update(['plan' => 'pro']);
    User::factory()->create()->profile()->update(['plan' => 'free']);
    User::factory()->create()->profile()->update(['plan' => 'free']);
    User::factory()->create()->profile()->update(['plan' => 'free']);

    Goal::factory()->create([
        'metric' => 'revenue',
        'period' => 'monthly',
        'target_value' => 12345.67,
        'start_date' => now()->startOfMonth(),
        'end_date' => now()->endOfMonth(),
    ]);

    $totalUsers = User::count();
    $premiumUsers = 2;
    $freeUsers = $totalUsers - $premiumUsers;

    Livewire::actingAs($admin)
        ->test(Stats::class)
        ->assertOk()
        ->assertSet('totalUsers', $totalUsers)
        ->assertSet('premiumUsers', $premiumUsers)
        ->assertSet('freeUsers', $freeUsers)
        ->assertSet('revenueGoal', '12345.67');
});

test('GoalChart widget computes goal percentage from the active monthly users goal', function () {
    $admin = makeAdmin();

    Goal::factory()->create([
        'metric' => 'users',
        'period' => 'monthly',
        'target_value' => 100,
        'start_date' => now()->startOfMonth(),
        'end_date' => now()->endOfMonth(),
    ]);

    $usersThisMonth = User::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count();

    Livewire::actingAs($admin)
        ->test(GoalChart::class)
        ->assertOk()
        ->assertSet('targetValue', '100.00')
        ->assertSet('goalPercentage', round(($usersThisMonth / 100) * 100, 2));
});

test('UserGrowthChart widget computes current period user counts and chart series', function () {
    $admin = makeAdmin();

    $now = Carbon::now();
    User::factory()->count(3)->create(['created_at' => $now]);

    $totalUsers = User::count();
    $currentMonthUsers = User::whereMonth('created_at', $now->month)
        ->whereYear('created_at', $now->year)
        ->count();

    $component = Livewire::actingAs($admin)
        ->test(UserGrowthChart::class)
        ->assertOk()
        ->assertSet('totalUsers', $totalUsers)
        ->assertSet('currentPeriodUsers', $currentMonthUsers);

    expect($component->get('chartLabels'))->toHaveCount(12);
    expect($component->get('usersChartValues'))->toHaveCount(12);
    // Today's bucket (last element) should reflect the users created "now".
    expect(array_sum($component->get('usersChartValues')))->toBeGreaterThanOrEqual(3);
});

test('TopCountries widget renders its static country ranking sorted by users descending', function () {
    $admin = makeAdmin();

    $component = Livewire::actingAs($admin)
        ->test(TopCountries::class)
        ->assertOk();

    $countries = $component->get('countries');
    expect($countries)->not->toBeEmpty();

    $userCounts = array_column($countries, 'users');
    $sorted = $userCounts;
    rsort($sorted);
    expect($userCounts)->toEqual($sorted);
});

test('SocialMediaStats widget renders its channel stats', function () {
    $admin = makeAdmin();

    $component = Livewire::actingAs($admin)
        ->test(SocialMediaStats::class)
        ->assertOk();

    expect($component->get('stats'))->not->toBeEmpty();
    foreach ($component->get('stats') as $stat) {
        expect($stat)->toHaveKeys(['name', 'handle', 'growth', 'path', 'color_bg', 'color_text']);
    }
});
