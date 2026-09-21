<?php

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class UserIndex extends Component
{
    use WithFileUploads;
    use WithPagination;

    public $search = '';

    public $perPage = 10;

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public $showModal = false;

    public $isEditMode = false;

    public $userId;

    public $selected = [];

    public $selectAll = false;

    public $name = '';

    public $email = '';

    public $password = '';

    public $phone = '';

    public $city = '';

    public $state = '';

    public $image;

    public $currentImage;

    // Social Media
    public $mere = '';

    public $instagram = '';

    public $x = '';

    public $facebook = '';

    public $youtube = '';

    public $role = 'user';

    public $plan = 'free';

    protected function rules()
    {
        return [
            'name' => 'required|string|min:3',
            'email' => 'required|email|unique:users,email,'.$this->userId,
            'password' => $this->isEditMode ? 'nullable|min:6' : 'required|min:6',
            'phone' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'image' => 'nullable|image|max:1024', // 1MB Max
            'mere' => 'nullable|string',
            'instagram' => 'nullable|string',
            'x' => 'nullable|string',
            'facebook' => 'nullable|string',
            'youtube' => 'nullable|string',
            'role' => 'sometimes|in:admin,manager,user',
            'plan' => 'sometimes|in:free,monthly,annual',
        ];
    }

    public function deleteSelected()
    {
        $count = count($this->selected);
        User::whereIn('id', $this->selected)->delete();
        $this->selected = [];
        $this->selectAll = false;

        $message = $count === 1
            ? 'O usuário selecionado foi excluído do sistema!'
            : "{$count} usuários foram excluídos com sucesso!";

        $this->dispatch('toast', [
            'type' => 'error',
            'message' => $message,
            'title' => 'Exclusão realizada',
        ]);
    }

    private function getUsersQuery()
    {
        return User::with('profile')
            ->where(function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            });
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
            $this->selected = $this->getUsersQuery()
                ->paginate($perPage)
                ->pluck('id')
                ->toArray();
            $this->selectAll = true;
        }
    }

    public function create()
    {
        $this->resetValidation();
        $this->reset(['name', 'email', 'password', 'phone', 'city', 'state', 'image', 'currentImage', 'mere', 'instagram', 'x', 'facebook', 'youtube', 'userId', 'role', 'plan']);
        $this->isEditMode = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->resetValidation();
        $user = User::with('profile')->findOrFail($id);
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;

        // Profile Data
        $profile = $user->profile;
        $this->phone = $profile?->phone;
        $this->city = $profile?->city;
        $this->state = $profile?->state;
        $this->currentImage = $profile?->image ? \Storage::url($profile->image) : null;

        $this->mere = $profile?->mere;
        $this->instagram = $profile?->instagram;
        $this->x = $profile?->x;
        $this->facebook = $profile?->facebook;
        $this->youtube = $profile?->youtube;
        $this->role = $profile?->role ?? 'user';
        $this->plan = $profile?->plan ?? 'free';

        $this->isEditMode = true;
        $this->showModal = true;
    }

    public function store()
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => \Hash::make($this->password),
        ]);

        $profileData = [
            'phone' => $this->phone,
            'city' => $this->city,
            'state' => $this->state,
            'mere' => $this->mere,
            'instagram' => $this->instagram,
            'x' => $this->x,
            'facebook' => $this->facebook,
            'youtube' => $this->youtube,
            'status' => 'active', // Default
            'plan' => 'free', // Default
        ];

        if (auth()->user()->isSuperAdmin()) {
            $profileData['role'] = $this->role;
            $profileData['plan'] = $this->plan;
        }

        if ($this->image) {
            $profileData['image'] = $this->image->store('profile-photos', 'public');
        }

        $user->profile()->create($profileData);

        $this->showModal = false;
        $this->dispatch('toast', [
            'type' => 'success',
            'message' => 'O usuário "'.$this->name.'" foi cadastrado com sucesso!',
            'title' => 'Novo usuário criado',
        ]);
    }

    public function update()
    {
        $this->validate();

        $user = User::findOrFail($this->userId);

        $user->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        if ($this->password) {
            $user->update(['password' => \Hash::make($this->password)]);
        }

        $profileData = [
            'phone' => $this->phone,
            'city' => $this->city,
            'state' => $this->state,
            'mere' => $this->mere,
            'instagram' => $this->instagram,
            'x' => $this->x,
            'facebook' => $this->facebook,
            'youtube' => $this->youtube,
        ];

        if (auth()->user()->isSuperAdmin()) {
            $profileData['role'] = $this->role;
            $profileData['plan'] = $this->plan;
        }

        if ($this->image) {
            $profileData['image'] = $this->image->store('profile-photos', 'public');
        }

        if ($user->profile) {
            $user->profile->update($profileData);
        } else {
            $user->profile()->create($profileData);
        }

        $this->showModal = false;
        $this->dispatch('toast', [
            'type' => 'info',
            'message' => 'Os dados do usuário "'.$this->name.'" foram atualizados!',
            'title' => 'Usuário atualizado',
        ]);
    }

    public $confirmingDeletion = false;

    public $userToDelete;

    public function confirmDelete($id)
    {
        $this->userToDelete = User::findOrFail($id);
        $this->confirmingDeletion = true;
    }

    public function delete()
    {
        if ($this->userToDelete) {
            $userName = $this->userToDelete->name;
            $this->userToDelete->delete();

            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'O usuário "'.$userName.'" foi removido do sistema!',
                'title' => 'Usuário excluído',
            ]);
        }

        $this->confirmingDeletion = false;
        $this->userToDelete = null;
    }

    public function toggleStatus($id)
    {
        $user = User::with('profile')->findOrFail($id);
        if ($user->profile) {
            $newStatus = $user->profile->status === 'active' ? 'inactive' : 'active';
            $user->profile->status = $newStatus;
            $user->profile->save();

            $statusText = $newStatus === 'active' ? 'ativado' : 'desativado';
            $this->dispatch('toast', [
                'type' => 'info',
                'message' => 'O usuário "'.$user->name.'" foi '.$statusText.' com sucesso!',
                'title' => 'Status atualizado',
            ]);
        } else {
            $user->profile()->create(['status' => 'active']);
            $this->dispatch('toast', [
                'type' => 'success',
                'message' => 'Perfil criado e ativado para "'.$user->name.'"!',
                'title' => 'Perfil criado',
            ]);
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function render()
    {
        if ($this->perPage == -1) {
            // Show all without pagination
            $users = $this->getUsersQuery()->get();

            // Create a manual paginator for compatibility with the view
            $users = new LengthAwarePaginator(
                $users,
                $users->count(),
                $users->count(),
                1,
                ['path' => request()->url()]
            );
        } else {
            $users = $this->getUsersQuery()->paginate($this->perPage);
        }

        return view('livewire.users.index', [
            'users' => $users,
        ])->title('Users');
    }
}
