<?php

namespace App\Livewire\Stories;

use App\Models\Story;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class StoryIndex extends Component
{
    use WithPagination;

    public $search = '';

    public $perPage = 10;

    public $statusFilter = 'active';

    public $selected = [];

    public $selectAll = false;

    public $storyId;

    public $confirmingDeletion = false;

    protected $queryString = ['search', 'statusFilter'];

    private function getStoriesQuery()
    {
        $query = Story::with('user')
            ->when($this->search, function ($query) {
                $query->whereHas('user', function ($userQuery) {
                    $userQuery->where('name', 'like', '%'.$this->search.'%');
                });
            });

        if ($this->statusFilter === 'active') {
            $query->where('expires_at', '>', now())->orderBy('expires_at', 'asc');
        } elseif ($this->statusFilter === 'expired') {
            $query->where('expires_at', '<=', now())->orderByDesc('created_at');
        } else {
            $query->orderByDesc('created_at');
        }

        return $query;
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
        $this->selected = [];
        $this->selectAll = false;
    }

    public function toggleSelectAll()
    {
        if (! is_array($this->selected)) {
            $this->selected = [];
        }

        $perPage = $this->perPage == -1 ? 100000 : $this->perPage;

        if (count($this->selected) > 0) {
            $this->selected = [];
            $this->selectAll = false;
        } else {
            $this->selected = $this->getStoriesQuery()
                ->paginate($perPage)
                ->pluck('id')
                ->toArray();
            $this->selectAll = true;
        }
    }

    public function deleteSelected()
    {
        Story::whereIn('id', $this->selected)->delete();
        $this->selected = [];
        $this->selectAll = false;
        $this->dispatch('toast', ['type' => 'success', 'message' => 'Stories selecionadas foram excluídas com sucesso!']);
    }

    public function confirmDelete($id)
    {
        $this->storyId = $id;
        $this->confirmingDeletion = true;
    }

    public function delete()
    {
        if ($this->storyId) {
            Story::findOrFail($this->storyId)->delete();
            $this->confirmingDeletion = false;
            $this->storyId = null;
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'A story foi removida do sistema!',
                'title' => 'Story excluída',
            ]);
        }
    }

    public function cancelDelete()
    {
        $this->confirmingDeletion = false;
        $this->storyId = null;
    }

    public function render()
    {
        if ($this->perPage == -1) {
            $stories = $this->getStoriesQuery()->get();

            $stories = new LengthAwarePaginator(
                $stories,
                $stories->count(),
                $stories->count(),
                1,
                ['path' => request()->url()]
            );
        } else {
            $stories = $this->getStoriesQuery()->paginate($this->perPage);
        }

        return view('livewire.stories.story-index', [
            'stories' => $stories,
        ]);
    }
}
