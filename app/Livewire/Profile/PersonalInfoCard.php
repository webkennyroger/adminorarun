<?php

namespace App\Livewire\Profile;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class PersonalInfoCard extends Component
{
    use WithFileUploads;

    public $user;

    public string $name = '';

    public string $last_name = '';

    public string $nickname = '';

    public string $email = '';

    public string $phone = '';

    public $image;

    public function mount(): void
    {
        $this->user = Auth::user();
        $this->name = $this->user->name;
        $this->email = $this->user->email;
        $this->last_name = $this->user->profile?->last_name ?? '';
        $this->nickname = $this->user->profile?->nickname ?? '';
        $this->phone = $this->user->profile?->phone ?? '';
    }

    public function updateProfileInformation(): void
    {
        // Sanitize nickname: remove @, special chars, and convert to lowercase
        if ($this->nickname) {
            $this->nickname = strtolower(preg_replace('/[^a-zA-Z0-9_.]/', '', str_replace('@', '', $this->nickname)));
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($this->user->id)],
            'last_name' => ['nullable', 'string', 'max:255'],
            'nickname' => [
                'nullable',
                'string',
                'max:30',
                'min:3',
                'regex:/^[a-zA-Z0-9_.]+$/',
                Rule::unique('profiles', 'nickname')->ignore($this->user->profile?->id),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'image' => ['nullable', 'image', 'max:1024'], // 1MB Max
        ], [
            'nickname.unique' => 'Este nome de usuário já está em uso.',
            'nickname.regex' => 'O nome de usuário deve conter apenas letras, números, pontos e sublinhados.',
            'nickname.min' => 'O nome de usuário deve ter pelo menos 3 caracteres.',
            'nickname.max' => 'O nome de usuário não pode ter mais de 30 caracteres.',
        ]);

        $this->user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($this->user->isDirty('email')) {
            $this->user->email_verified_at = null;
        }

        $this->user->save();

        if ($this->image) {
            $path = $this->image->store('profile-photos', 'public');
            $this->user->profile()->updateOrCreate(
                ['user_id' => $this->user->id],
                ['image' => $path]
            );
        }

        $this->user->profile()->updateOrCreate(
            ['user_id' => $this->user->id],
            [
                'last_name' => $validated['last_name'],
                'nickname' => $validated['nickname'],
                'phone' => $validated['phone'],
            ]
        );

        $this->user->refresh();

        $this->dispatch('profile-updated');
        $this->dispatch('close-modal', 'profile-info');
    }

    public function render()
    {
        return view('livewire.profile.personal-info-card');
    }
}
