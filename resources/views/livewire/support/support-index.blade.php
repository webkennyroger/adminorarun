<div>
    <x-common.page-breadcrumb pageTitle="Suporte e Ajuda" />

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <!-- Create Ticket Form -->
        <x-ui.card>
            <h3 class="mb-6 text-lg font-medium" style="color: var(--text-primary);">
                Abrir Novo Ticket
            </h3>

            <form action="{{ route('support.store') }}" method="POST" class="space-y-6">
                @csrf

                @if(session('status'))
                    <x-ui.alert variant="success">
                        {{ session('status') }}
                    </x-ui.alert>
                @endif

                <x-ui.field label="Assunto" :error="$errors->first('subject')">
                    <x-ui.input type="text" name="subject" value="{{ old('subject') }}"
                        placeholder="Ex: Problema com acesso ao sistema" required />
                </x-ui.field>

                <x-ui.field label="Prioridade" :error="$errors->first('priority')">
                    <x-ui.select name="priority" :placeholder="false">
                        <option value="low" @selected(old('priority', 'low') === 'low')>Baixa</option>
                        <option value="medium" @selected(old('priority', 'low') === 'medium')>Média</option>
                        <option value="high" @selected(old('priority', 'low') === 'high')>Alta</option>
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Mensagem" :error="$errors->first('message')">
                    <x-ui.textarea name="message" rows="5"
                        placeholder="Descreva seu problema detalhadamente...">{{ old('message') }}</x-ui.textarea>
                </x-ui.field>

                <div class="pt-2">
                    <x-ui.button type="submit" class="w-full !justify-center">
                        Enviar Solicitação
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>

        <!-- My Tickets List -->
        <x-ui.card :padding="false">
            <x-slot:header>
                <h3 class="text-lg font-medium" style="color: var(--text-primary);">
                    Meus Tickets
                </h3>
            </x-slot:header>

            <div class="p-6">
                @if($tickets->isEmpty())
                    <x-ui.empty-state title="Nenhum ticket encontrado"
                        description="Você ainda não criou nenhuma solicitação de suporte." />
                @else
                    <div class="space-y-4 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
                        @foreach($tickets as $ticket)
                            @php
                                $statusVariant = match ($ticket->status) {
                                    'resolved', 'solved' => 'success',
                                    'closed' => 'danger',
                                    'pending' => 'warning',
                                    default => 'info',
                                };
                                $statusLabel = match ($ticket->status) {
                                    'open' => 'Aberto',
                                    'pending' => 'Pendente',
                                    'resolved', 'solved' => 'Resolvido',
                                    'closed' => 'Fechado',
                                    default => ucfirst($ticket->status),
                                };
                            @endphp
                            <a href="{{ route('support.show', $ticket) }}" wire:navigate
                                class="group block rounded-xl p-4 transition-colors hover:bg-[var(--bg-hover)]"
                                style="border: 1px solid var(--border-color);">
                                <div class="mb-2 flex items-center justify-between">
                                    <span class="text-xs font-medium" style="color: var(--text-muted);">
                                        {{ $ticket->ticket_id }}
                                    </span>
                                    <x-ui.badge :variant="$statusVariant" size="sm">
                                        {{ $statusLabel }}
                                    </x-ui.badge>
                                </div>
                                <h4 class="mb-1 text-sm font-medium" style="color: var(--text-primary);">
                                    {{ $ticket->subject }}
                                </h4>
                                <div class="flex items-center justify-between">
                                    <p class="line-clamp-1 text-xs" style="color: var(--text-muted);">
                                        {{ Str::limit(strip_tags($ticket->message), 60) }}
                                    </p>
                                    <span class="text-xs" style="color: var(--text-muted);">
                                        {{ $ticket->created_at->diffForHumans() }}
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </x-ui.card>
    </div>
</div>
