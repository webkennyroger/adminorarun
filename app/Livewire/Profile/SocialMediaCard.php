<?php

namespace App\Livewire\Profile;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SocialMediaCard extends Component
{
    public $user;

    public string $instagram = '';

    public string $facebook = '';

    public string $x = '';

    public string $youtube = '';

    public string $tiktok = '';

    public string $mere = '';

    public function mount(): void
    {
        $this->user = Auth::user();
        $this->instagram = $this->user->profile?->instagram ?? '';
        $this->facebook = $this->user->profile?->facebook ?? '';
        $this->x = $this->user->profile?->x ?? '';
        $this->youtube = $this->user->profile?->youtube ?? '';
        $this->tiktok = $this->user->profile?->tiktok ?? '';
        $this->mere = $this->user->profile?->mere ?? '';
    }

    public function updateSocialMedia(): void
    {
        $validated = $this->validate([
            'instagram' => ['nullable', 'string', 'max:255'],
            'facebook' => ['nullable', 'string', 'max:255'],
            'x' => ['nullable', 'string', 'max:255'],
            'youtube' => ['nullable', 'string', 'max:255'],
            'tiktok' => ['nullable', 'string', 'max:255'],
            'mere' => ['nullable', 'string', 'max:255'],
        ]);

        $this->user->profile()->updateOrCreate(
            ['user_id' => $this->user->id],
            [
                'instagram' => $validated['instagram'],
                'facebook' => $validated['facebook'],
                'x' => $validated['x'],
                'youtube' => $validated['youtube'],
                'tiktok' => $validated['tiktok'],
                'mere' => $validated['mere'],
            ]
        );

        $this->dispatch('close-modal', 'social-media');
        $this->dispatch('profile-updated');
    }

    public function render()
    {
        return view('livewire.profile.social-media-card');
    }
}
