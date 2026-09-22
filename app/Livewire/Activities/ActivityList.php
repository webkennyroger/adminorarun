<?php

namespace App\Livewire\Activities;

use App\Models\Activity;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ActivityList extends Component
{
    use WithFileUploads, WithPagination;

    public $search = '';

    #[Layout('components.layouts.app')]
    public function render()
    {
        $activities = Activity::with('user.profile', 'comments', 'likes')
            ->where(function ($q) {
                $q->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            })
            ->latest('start_time')
            ->paginate(10);

        return view('livewire.activities.activity-index', [
            'activities' => $activities,
        ]);
    }
}
