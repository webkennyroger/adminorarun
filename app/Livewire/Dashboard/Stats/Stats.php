<?php

namespace App\Livewire\Dashboard\Stats;

use App\Models\Goal;
use App\Models\User;
use Livewire\Component;

class Stats extends Component
{
    public $totalUsers;

    public $freeUsers;

    public $premiumUsers;

    public $revenueGoal;

    public $activeGoals = [];

    public function mount()
    {
        $this->totalUsers = User::count();

        // Count premium users (users with a non-null and non-free plan in their profile)
        $this->premiumUsers = User::whereHas('profile', function ($query) {
            $query->whereNotNull('plan')
                ->where('plan', '!=', '')
                ->where('plan', '!=', 'free');
        })->count();

        // Free users are users with plan = 'free' or without profile
        $this->freeUsers = User::where(function ($query) {
            $query->doesntHave('profile')
                ->orWhereHas('profile', function ($q) {
                    $q->where('plan', 'free')
                        ->orWhereNull('plan')
                        ->orWhere('plan', '');
                });
        })->count();

        // Fetch goals for the current month and key by metric
        $this->activeGoals = Goal::where('period', 'monthly')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->get()
            ->keyBy('metric');

        $this->revenueGoal = $this->activeGoals['revenue']->target_value ?? 0;
    }

    public function render()
    {
        return view('livewire.dashboard.stats.stats');
    }
}
