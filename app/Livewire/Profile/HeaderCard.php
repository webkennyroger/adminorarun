<?php

namespace App\Livewire\Profile;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class HeaderCard extends Component
{
    public $user;

    public string $name = '';

    public string $phone = '';

    public string $email = '';

    public $image;

    public function mount(): void
    {
        $this->user = Auth::user();
        $this->name = $this->user->name;
        $this->email = $this->user->email;
        $this->phone = $this->user->profile?->phone ?? '';
    }

    #[On('profile-updated')]
    public function refreshUser()
    {
        $this->user->refresh();
        $this->name = $this->user->name;
        $this->email = $this->user->email;
        $this->phone = $this->user->profile?->phone ?? '';
    }

    public function render()
    {
        return view('livewire.profile.header-card');
    }
}
