<?php

namespace App\Livewire\Goals;

use App\Models\Goal;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class GoalIndex extends Component
{
    use WithPagination;

    public $search = '';

    public $perPage = 10;

    public $showCreateModal = false;

    public $showEditModal = false;

    public $showDeleteModal = false;

    public $goalId;

    public $title;

    public $metric = 'users'; // default

    public $period = 'monthly'; // default

    public $target_value;

    public $start_date;

    public $end_date;

    protected $rules = [
        'title' => 'required|string|max:255',
        'metric' => 'required|in:users,sales,expenses,revenue',
        'period' => 'required|in:monthly,quarterly,semiannual,annual',
        'target_value' => 'required|numeric|min:0',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
    ];

    public $selected = [];

    public $selectAll = false;

    public function updatedAppPage($value)
    {
        // Reset selected when page changes
        $this->selected = [];
        $this->selectAll = false;
    }

    public function toggleSelectAll()
    {
        // Ensure $selected is always an array
        if (! is_array($this->selected)) {
            $this->selected = [];
        }

        $perPage = $this->perPage == -1 ? 100000 : $this->perPage;

        if (count($this->selected) > 0) {
            // If any are selected, deselect all
            $this->selected = [];
            $this->selectAll = false;
        } else {
            // Select all on current page
            $this->selected = $this->getGoalsQuery()
                ->paginate($perPage)
                ->pluck('id')
                ->toArray();
            $this->selectAll = true;
        }
    }

    public function deleteSelected()
    {
        $count = count($this->selected);
        Goal::whereIn('id', $this->selected)->delete();
        $this->selected = [];
        $this->selectAll = false;

        $message = $count === 1
            ? 'A meta selecionada foi excluída com sucesso!'
            : "{$count} metas foram excluídas do sistema!";

        $this->dispatch('toast', [
            'type' => 'error',
            'message' => $message,
            'title' => 'Exclusão realizada',
        ]);
    }

    public function updatedSearch()
    {
        $this->resetPage();
        $this->selected = [];
        $this->selectAll = false;
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->reset(['title', 'metric', 'period', 'target_value', 'start_date', 'end_date', 'goalId']);
        $this->start_date = now()->startOfMonth()->format('Y-m-d');
        $this->end_date = now()->endOfMonth()->format('Y-m-d');
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate();

        Goal::create([
            'title' => $this->title,
            'metric' => $this->metric,
            'period' => $this->period,
            'target_value' => $this->target_value,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
        ]);

        $this->showCreateModal = false;
        $this->dispatch('toast', [
            'type' => 'success',
            'message' => 'A meta "'.$this->title.'" foi criada e está disponível!',
            'title' => 'Nova meta criada',
        ]);
    }

    public function edit(Goal $goal)
    {
        $this->goalId = $goal->id;
        $this->title = $goal->title;
        $this->metric = $goal->metric;
        $this->period = $goal->period;
        $this->target_value = $goal->target_value;
        $this->start_date = $goal->start_date->format('Y-m-d');
        $this->end_date = $goal->end_date->format('Y-m-d');
        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->resetValidation();
    }

    public function update()
    {
        $this->validate();

        $goal = Goal::findOrFail($this->goalId);
        $goal->update([
            'title' => $this->title,
            'metric' => $this->metric,
            'period' => $this->period,
            'target_value' => $this->target_value,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
        ]);

        $this->showEditModal = false;
        $this->dispatch('toast', [
            'type' => 'info',
            'message' => 'As alterações na meta "'.$this->title.'" foram salvas!',
            'title' => 'Meta atualizada',
        ]);
    }

    public function confirmDelete($id)
    {
        $this->goalId = $id;
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
    }

    public function delete()
    {
        $goal = Goal::findOrFail($this->goalId);
        $goalTitle = $goal->title;
        $goal->delete();
        $this->showDeleteModal = false;
        $this->dispatch('toast', [
            'type' => 'error',
            'message' => 'A meta "'.$goalTitle.'" foi removida do sistema!',
            'title' => 'Meta excluída',
        ]);
    }

    private function getGoalsQuery()
    {
        return Goal::query()
            ->when($this->search, function ($query) {
                $query->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('metric', 'like', '%'.$this->search.'%');
            })
            ->latest();
    }

    public function render()
    {
        if ($this->perPage === -1) {
            $goals = $this->getGoalsQuery()->get();

            $goals = new LengthAwarePaginator(
                $goals,
                $goals->count(),
                $goals->count(),
                1,
                ['path' => request()->url()]
            );
        } else {
            $goals = $this->getGoalsQuery()->paginate($this->perPage);
        }

        return view('livewire.goals.goal-index', [
            'goals' => $goals,
        ])->layout('components.layouts.app');
    }
}
