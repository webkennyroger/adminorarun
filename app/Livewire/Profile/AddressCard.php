<?php

namespace App\Livewire\Profile;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

class AddressCard extends Component
{
    public $user;

    public string $address = '';

    public string $city = '';

    public string $state = '';

    public string $zip_code = '';

    public function mount(): void
    {
        $this->user = Auth::user();
        if ($this->user->profile) {
            $this->address = $this->user->profile->address ?? '';
            $this->city = $this->user->profile->city ?? '';
            $this->state = $this->user->profile->state ?? '';
            $this->zip_code = $this->user->profile->zip_code ?? '';
        }
    }

    public function updatedZipCode($value)
    {
        // Sanitize
        $cep = preg_replace('/[^0-9]/', '', $value);

        if (strlen($cep) === 8) {
            $response = Http::get("https://viacep.com.br/ws/{$cep}/json/");

            if ($response->successful() && ! isset($response['erro'])) {
                $data = $response->json();
                $this->address = $data['logradouro'] ?? $this->address;
                $this->city = $data['localidade'] ?? $this->city;
                $this->state = $data['uf'] ?? $this->state;
            }
        }
    }

    public function saveAddress(): void
    {
        $validated = $this->validate([
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'zip_code' => ['nullable', 'string', 'max:20'],
        ]);

        $this->user->profile()->updateOrCreate(
            ['user_id' => $this->user->id],
            [
                'address' => $validated['address'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'zip_code' => $validated['zip_code'],
            ]
        );

        $this->dispatch('close-modal', 'address');
        $this->dispatch('profile-updated'); // Optional: Update parent/header if needed
    }

    public function render()
    {
        return view('livewire.profile.address-card');
    }
}
