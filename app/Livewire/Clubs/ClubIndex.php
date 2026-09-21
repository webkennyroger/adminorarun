<?php

namespace App\Livewire\Clubs;

use App\Models\Club;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ClubIndex extends Component
{
    use WithFileUploads, WithPagination;

    public $search = '';

    public $filterCategory = '';

    public $filterPublic = '';

    public $perPage = 10;

    public $selected = [];

    public $selectAll = false;

    // Modal states
    public $showCreateModal = false;

    public $showEditModal = false;

    public $showMembersModal = false;

    public $confirmingDeletion = false;

    // Club form data
    public $clubId;

    public $name;

    public $description;

    public $city;

    public $state;

    public $category;

    public $is_public = true;

    public $image;

    public $avatar;

    public $existing_image;

    public $existing_avatar;

    // Members modal state
    public $selectedClub = null;

    protected $queryString = ['search'];

    private function getClubsQuery()
    {
        return Club::with('creator')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('city', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filterCategory, function ($query) {
                $query->where('category', $this->filterCategory);
            })
            ->when($this->filterPublic !== '', function ($query) {
                $query->where('is_public', $this->filterPublic === '1');
            })
            ->latest();
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'is_public' => 'boolean',
            'image' => 'nullable|image|max:2048',
            'avatar' => 'nullable|image|max:2048',
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

    public function updatingFilterCategory()
    {
        $this->resetPage();
    }

    public function updatingFilterPublic()
    {
        $this->resetPage();
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
            $this->selected = $this->getClubsQuery()
                ->paginate($perPage)
                ->pluck('id')
                ->toArray();
            $this->selectAll = true;
        }
    }

    public function deleteSelected()
    {
        Club::whereIn('id', $this->selected)->delete();
        $this->selected = [];
        $this->selectAll = false;
        $this->dispatch('toast', ['type' => 'success', 'message' => 'Clubes selecionados foram excluídos com sucesso!']);
    }

    public function create()
    {
        $this->reset(['clubId', 'name', 'description', 'city', 'state', 'category', 'image', 'avatar', 'existing_image', 'existing_avatar']);
        $this->is_public = true;
        $this->resetValidation();
        $this->showCreateModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'city' => $this->city,
            'state' => $this->state,
            'category' => $this->category,
            'is_public' => $this->is_public,
            // Clubes criados pelo admin não têm um usuário "dono" real no app,
            // mas a coluna creator_id é NOT NULL (FK para users) e creator_name
            // também é obrigatória. Usamos o próprio admin autenticado como criador.
            'creator_id' => auth()->id(),
            'creator_name' => auth()->user()->name,
            'members_count' => 0,
            'followers_count' => 0,
        ];

        if ($this->image) {
            $data['image'] = $this->image->store('clubs', 'public');
        }

        if ($this->avatar) {
            $data['avatar'] = $this->avatar->store('clubs', 'public');
        }

        Club::create($data);

        $this->showCreateModal = false;
        $this->reset(['clubId', 'name', 'description', 'city', 'state', 'category', 'image', 'avatar', 'existing_image', 'existing_avatar']);

        $this->dispatch('toast', ['type' => 'success', 'message' => 'Clube criado com sucesso!']);
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->resetValidation();
    }

    public function edit($id)
    {
        $club = Club::findOrFail($id);

        $this->clubId = $club->id;
        $this->name = $club->name;
        $this->description = $club->description;
        $this->city = $club->city;
        $this->state = $club->state;
        $this->category = $club->category;
        $this->is_public = $club->is_public;
        $this->existing_image = $club->image;
        $this->existing_avatar = $club->avatar;
        $this->image = null;
        $this->avatar = null;

        $this->showEditModal = true;
    }

    public function update()
    {
        $this->validate();

        $club = Club::findOrFail($this->clubId);

        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'city' => $this->city,
            'state' => $this->state,
            'category' => $this->category,
            'is_public' => $this->is_public,
        ];

        if ($this->image) {
            $data['image'] = $this->image->store('clubs', 'public');
        }

        if ($this->avatar) {
            $data['avatar'] = $this->avatar->store('clubs', 'public');
        }

        $club->update($data);

        $this->showEditModal = false;
        $this->reset(['clubId', 'name', 'description', 'city', 'state', 'category', 'image', 'avatar', 'existing_image', 'existing_avatar']);

        $this->dispatch('toast', ['type' => 'info', 'message' => 'Clube atualizado com sucesso!']);
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->reset(['clubId', 'name', 'description', 'city', 'state', 'category', 'image', 'avatar', 'existing_image', 'existing_avatar']);
        $this->resetValidation();
    }

    public function confirmDelete($id)
    {
        $this->clubId = $id;
        $this->confirmingDeletion = true;
    }

    public function delete()
    {
        if ($this->clubId) {
            // Club usa o trait SoftDeletes, então delete() apenas marca deleted_at.
            Club::findOrFail($this->clubId)->delete();
            $this->confirmingDeletion = false;
            $this->clubId = null;
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'O clube foi removido do sistema!',
                'title' => 'Clube excluído',
            ]);
        }
    }

    public function cancelDelete()
    {
        $this->confirmingDeletion = false;
        $this->clubId = null;
    }

    public function manageMembers($id)
    {
        $this->selectedClub = Club::with('members')->findOrFail($id);
        $this->showMembersModal = true;
    }

    public function closeMembersModal()
    {
        $this->showMembersModal = false;
        $this->selectedClub = null;
    }

    public function promoteMember($userId)
    {
        if (! $this->selectedClub) {
            return;
        }

        $this->selectedClub->members()->updateExistingPivot($userId, ['role' => 'admin']);
        $this->selectedClub = $this->selectedClub->fresh(['members']);

        $this->dispatch('toast', ['type' => 'success', 'message' => 'Membro promovido a admin do clube!']);
    }

    public function demoteMember($userId)
    {
        if (! $this->selectedClub) {
            return;
        }

        $this->selectedClub->members()->updateExistingPivot($userId, ['role' => 'member']);
        $this->selectedClub = $this->selectedClub->fresh(['members']);

        $this->dispatch('toast', ['type' => 'success', 'message' => 'Membro rebaixado para membro comum.']);
    }

    public function removeMember($userId)
    {
        if (! $this->selectedClub) {
            return;
        }

        $this->selectedClub->members()->detach($userId);

        if ($this->selectedClub->members_count > 0) {
            $this->selectedClub->decrement('members_count');
        }

        $this->selectedClub = $this->selectedClub->fresh(['members']);

        $this->dispatch('toast', ['type' => 'success', 'message' => 'Membro removido do clube.']);
    }

    public function render()
    {
        $categories = Club::query()->distinct()->pluck('category')->filter()->sort()->values();

        if ($this->perPage == -1) {
            $clubs = $this->getClubsQuery()->get();

            $clubs = new LengthAwarePaginator(
                $clubs,
                $clubs->count(),
                $clubs->count(),
                1,
                ['path' => request()->url()]
            );
        } else {
            $clubs = $this->getClubsQuery()->paginate($this->perPage);
        }

        return view('livewire.clubs.club-index', [
            'clubs' => $clubs,
            'categories' => $categories,
        ]);
    }
}
