<?php

namespace App\Livewire\Schedule;

use App\Models\Schedule;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class EventModal extends Component
{
    use WithFileUploads;

    public $showModal = false;

    public $editMode = false;

    public $eventId = null;

    // Event properties
    public $title = '';

    public $description = '';

    public $event_date = '';

    public $event_time = '';

    public $color = '#3b82f6';

    public $photo;

    public $existingPhoto = null;

    protected function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'required|date',
            'event_time' => 'required',
            'color' => 'required|string',
            'photo' => 'nullable|image|max:2048',
        ];
    }

    #[On('openCreateModal')]
    public function openCreateModal($date, $time = '09:00')
    {
        $this->resetForm();
        $this->event_date = $date;
        $this->event_time = $time;
        $this->editMode = false;
        $this->showModal = true;
    }

    #[On('openEditModal')]
    public function openEditModal($eventId)
    {
        $event = Schedule::where('user_id', auth()->id())->findOrFail($eventId);

        $this->eventId = $event->id;
        $this->title = $event->title;
        $this->description = $event->description;
        $this->event_date = $event->event_date->format('Y-m-d');
        $this->event_time = $event->event_time;
        $this->color = $event->color;
        $this->existingPhoto = $event->photo;

        $this->editMode = true;
        $this->showModal = true;
    }

    public function saveEvent()
    {
        $this->validate();

        $data = [
            'user_id' => auth()->id(),
            'title' => $this->title,
            'description' => $this->description,
            'event_date' => $this->event_date,
            'event_time' => $this->event_time,
            'color' => $this->color,
        ];

        if ($this->photo) {
            $data['photo'] = $this->photo->store('events', 'public');
        }

        if ($this->editMode) {
            $event = Schedule::where('user_id', auth()->id())->findOrFail($this->eventId);
            $event->update($data);
            $this->dispatch('toast', [
                'type' => 'info',
                'message' => 'Evento atualizado com sucesso!',
                'title' => 'Evento Atualizado',
            ]);
        } else {
            Schedule::create($data);
            $this->dispatch('toast', [
                'type' => 'success',
                'message' => 'Evento criado com sucesso!',
                'title' => 'Evento Criado',
            ]);
        }

        $this->closeModal();
        $this->dispatch('eventSaved');
    }

    public $confirmingDeletion = false;

    public function confirmDelete()
    {
        $this->confirmingDeletion = true;
    }

    public function cancelDelete()
    {
        $this->confirmingDeletion = false;
    }

    public function deleteEvent()
    {
        if ($this->eventId) {
            $event = Schedule::where('user_id', auth()->id())->findOrFail($this->eventId);
            $event->delete();

            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'O evento foi removido do sistema!',
                'title' => 'Evento Excluído',
            ]);

            $this->closeModal();
            $this->dispatch('eventSaved');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset([
            'eventId',
            'title',
            'description',
            'event_date',
            'event_time',
            'color',
            'photo',
            'existingPhoto',
            'editMode',
        ]);
        $this->color = '#3b82f6';
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.schedule.event-modal');
    }
}
