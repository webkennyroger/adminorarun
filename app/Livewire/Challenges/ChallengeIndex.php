<?php

namespace App\Livewire\Challenges;

use App\Models\Category;
use App\Models\Challenge;
use App\Notifications\ChallengeCreated;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ChallengeIndex extends Component
{
    use WithFileUploads, WithPagination;

    public $search = '';

    public $perPage = 10;

    public $selected = [];

    public $selectAll = false;

    public $isEmbedded = false;

    // Modal states
    public $showViewModal = false;

    public $showEditModal = false;

    public $showCreateModal = false;

    public $confirmingDeletion = false;

    // Challenge data
    public $selectedChallenge = null;

    public $challengeId;

    public $title;

    public $description;

    public $start_date;

    public $end_date;

    public $goal_km;

    public $category_id;

    public $image;

    public $existing_image;

    public $is_featured = false;

    protected $queryString = ['search'];

    private function getChallengesQuery()
    {
        return Challenge::with('category')
            ->when($this->search, function ($query) {
                $query->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            })
            ->latest();
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
            $this->selected = $this->getChallengesQuery()
                ->paginate($perPage)
                ->pluck('id')
                ->toArray();
            $this->selectAll = true;
        }
    }

    public function deleteSelected()
    {
        Challenge::whereIn('id', $this->selected)->delete();
        $this->selected = [];
        $this->selectAll = false;
        $this->dispatch('toast', ['type' => 'success', 'message' => 'Desafios selecionados foram excluídos com sucesso!']);
    }

    protected function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'goal_km' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|max:2048',
            'is_featured' => 'boolean',

        ];
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function view($id)
    {
        $this->selectedChallenge = Challenge::with('category')->findOrFail($id);
        $this->showViewModal = true;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->selectedChallenge = null;
    }

    public function edit($id)
    {
        $challenge = Challenge::findOrFail($id);

        $this->challengeId = $challenge->id;
        $this->title = $challenge->title;
        $this->description = $challenge->description;
        $this->start_date = $challenge->start_date->format('Y-m-d');
        $this->end_date = $challenge->end_date->format('Y-m-d');
        $this->goal_km = $challenge->goal_km;
        $this->category_id = $challenge->category_id;
        $this->category_id = $challenge->category_id;
        $this->is_featured = $challenge->is_featured;
        $this->existing_image = $challenge->image;

        $this->showEditModal = true;
    }

    public function update()
    {
        $this->validate();

        $challenge = Challenge::findOrFail($this->challengeId);

        $data = [
            'title' => $this->title,
            'description' => $this->description,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'goal_km' => $this->goal_km,
            'category_id' => $this->category_id,
            'is_featured' => $this->is_featured,

        ];

        if ($this->image) {
            $data['image'] = $this->image->store('challenges', 'public');
        }

        if ($this->is_featured) {
            Challenge::where('id', '!=', $challenge->id)->update(['is_featured' => false]);
        }

        $challenge->update($data);

        $this->showEditModal = false;
        $this->reset(['challengeId', 'title', 'description', 'start_date', 'end_date', 'goal_km', 'category_id', 'image', 'existing_image', 'is_featured']);

        $this->dispatch('toast', ['type' => 'info', 'message' => 'Desafio atualizado com sucesso!']);
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->reset(['challengeId', 'title', 'description', 'start_date', 'end_date', 'goal_km', 'category_id', 'image', 'existing_image', 'is_featured']);
        $this->resetValidation();
    }

    public function confirmDelete($id)
    {
        $this->challengeId = $id;
        $this->confirmingDeletion = true;
    }

    public function delete()
    {
        if ($this->challengeId) {
            Challenge::findOrFail($this->challengeId)->delete();
            $this->confirmingDeletion = false;
            $this->challengeId = null;
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'O desafio foi removido do sistema!',
                'title' => 'Desafio excluído',
            ]);
        }
    }

    public function cancelDelete()
    {
        $this->confirmingDeletion = false;
        $this->challengeId = null;
    }

    public function create()
    {
        $this->reset(['challengeId', 'title', 'description', 'start_date', 'end_date', 'goal_km', 'category_id', 'image', 'existing_image', 'is_featured']);
        $this->resetValidation();
        $this->showCreateModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'description' => $this->description,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'goal_km' => $this->goal_km,
            'category_id' => $this->category_id,
            'is_featured' => $this->is_featured,
        ];

        if ($this->image) {
            $data['image'] = $this->image->store('challenges', 'public');
        }

        if ($this->is_featured) {
            Challenge::where('id', '>', 0)->update(['is_featured' => false]);
        }

        $challenge = Challenge::create($data);

        auth()->user()->notify(new ChallengeCreated($challenge));

        $this->showCreateModal = false;
        $this->reset(['challengeId', 'title', 'description', 'start_date', 'end_date', 'goal_km', 'category_id', 'image', 'existing_image', 'is_featured']);

        $this->dispatch('toast', ['type' => 'success', 'message' => 'Desafio criado com sucesso!']);
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
    }

    public function render()
    {
        $categories = Category::orderBy('name')->get();

        if ($this->perPage == -1) {
            $challenges = $this->getChallengesQuery()->get();

            $challenges = new LengthAwarePaginator(
                $challenges,
                $challenges->count(),
                $challenges->count(),
                1,
                ['path' => request()->url()]
            );
        } else {
            $challenges = $this->getChallengesQuery()->paginate($this->perPage);
        }

        return view('livewire.challenges.challenge-index', [
            'challenges' => $challenges,
            'categories' => $categories,
        ]);
    }
}
