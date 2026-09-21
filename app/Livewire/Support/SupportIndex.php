<?php

namespace App\Livewire\Support;

use App\Models\Support;
use App\Models\User;
use App\Notifications\TicketCreated;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Suporte e Ajuda'])]
class SupportIndex extends Component
{
    public $subject;

    public $priority = 'low';

    public $message;

    protected $rules = [
        'subject' => 'required|min:5|max:100',
        'priority' => 'required|in:low,medium,high',
        'message' => 'required|min:10',
    ];

    public function submitSupportForm()
    {
        Log::info('Support form submission started', [
            'user' => auth()->id(),
            'data' => [
                'subject' => $this->subject,
                'priority' => $this->priority,
                'message' => $this->message,
            ],
        ]);

        $this->validate();

        $ticket = Support::create([
            'user_id' => auth()->id(),
            'subject' => $this->subject,
            'priority' => $this->priority,
            'message' => $this->message,
            'status' => 'pending',
        ]);

        // Notify Admin
        $admin = User::where('email', 'webkennyroger@gmail.com')->first();
        if ($admin) {
            $admin->notify(new TicketCreated($ticket));
        }

        $this->reset();

        // Redirecionar para a lista após criar para confirmar visualização
        return redirect()->route('support.list')->with('status', 'Ticket criado com sucesso!');
    }

    public function render()
    {
        return view('livewire.support.support-index', [
            'tickets' => Support::where('user_id', auth()->id())
                ->latest()
                ->get(),
        ]);
    }
}
