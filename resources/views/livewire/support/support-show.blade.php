<div>
    <x-common.page-breadcrumb pageTitle="Detalhes do Ticket" />

    @php
        $statusVariant = match ($status) {
            'resolved', 'solved' => 'success',
            'closed' => 'danger',
            'pending' => 'warning',
            default => 'info',
        };
        $statusLabel = match ($status) {
            'open' => 'Aberto',
            'pending' => 'Pendente',
            'resolved', 'solved' => 'Resolvido',
            'closed' => 'Fechado',
            default => ucfirst($status),
        };
        $priorityVariant = match ($support->priority) {
            'high' => 'danger',
            'medium' => 'warning',
            default => 'success',
        };
        $priorityLabel = match ($support->priority) {
            'high' => 'Alta',
            'medium' => 'Média',
            'low' => 'Baixa',
            default => ucfirst($support->priority),
        };
    @endphp

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <!-- Ticket Details & Conversation -->
        <div class="xl:col-span-2 space-y-6">
            <!-- Ticket Header -->
            <x-ui.card>
                <div class="mb-6 flex items-start justify-between">
                    <div>
                        <div class="mb-2 flex items-center gap-3">
                            <h2 class="text-xl font-semibold" style="color: var(--text-primary);">
                                {{ $support->subject }}
                            </h2>
                            <x-ui.badge :variant="$statusVariant" size="sm">
                                {{ $statusLabel }}
                            </x-ui.badge>
                        </div>
                        <div class="flex items-center gap-4 text-sm" style="color: var(--text-secondary);">
                            <span class="flex items-center gap-1.5">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                </svg>
                                {{ $support->ticket_id }}
                            </span>
                            <span class="flex items-center gap-1.5">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $support->created_at->format('d/m/Y H:i') }}
                            </span>
                        </div>
                    </div>
                    <x-ui.button variant="secondary" size="sm" :href="route('support.index')" wire:navigate>
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Voltar
                    </x-ui.button>
                </div>

                <div class="prose prose-sm max-w-none" style="color: var(--text-secondary);">
                    <p class="whitespace-pre-wrap">{!! $support->message !!}</p>
                </div>
            </x-ui.card>

            <!-- Conversation History -->
            <div class="space-y-6">
                <h3 class="text-lg font-medium" style="color: var(--text-primary);">Histórico da Conversa</h3>

                @foreach($support->replies as $reply)
                    @php $isOwnReply = $reply->user_id === auth()->id(); @endphp
                    <div class="flex gap-4 {{ $isOwnReply ? 'flex-row-reverse' : '' }}">
                        <div class="flex-shrink-0">
                            <x-ui.avatar :name="$reply->user->name" size="md" />
                        </div>
                        <div class="flex max-w-[80%] flex-col {{ $isOwnReply ? 'items-end' : 'items-start' }}">
                            <div class="rounded-2xl px-6 py-4 {{ $isOwnReply ? 'bg-emerald-500 text-white' : '' }}"
                                @unless($isOwnReply) style="background-color: var(--bg-card); border: 1px solid var(--border-color);" @endunless>
                                <p class="whitespace-pre-wrap text-sm {{ $isOwnReply ? 'text-white' : '' }}"
                                    @unless($isOwnReply) style="color: var(--text-secondary);" @endunless>
                                    {!! $reply->message !!}
                                </p>
                            </div>
                            <span class="mt-1.5 text-xs" style="color: var(--text-muted);">
                                {{ $reply->created_at->format('d/m/Y H:i') }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Reply Form -->
            <x-ui.card>
                <h3 class="mb-4 text-lg font-medium" style="color: var(--text-primary);">
                    Responder
                </h3>
                <form action="{{ route('support.reply', $support->id) }}" method="POST">
                    @csrf
                    <x-ui.field :error="$errors->first('message')">
                        <x-ui.textarea name="message" rows="5" placeholder="Digite sua resposta...">{{ old('message') }}</x-ui.textarea>
                    </x-ui.field>
                    <div class="flex justify-end mt-4">
                        <x-ui.button type="submit">
                            Enviar Resposta
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>

        <!-- Sidebar Info -->
        <div class="xl:col-span-1">
            <x-ui.card class="sticky top-6">
                <h3 class="mb-4 text-lg font-medium" style="color: var(--text-primary);">
                    Informações
                </h3>
                <div class="mb-4">
                    <x-ui.field label="Status">
                        @if(auth()->user()->is_admin)
                            <form action="{{ route('support.update-status', $support->id) }}" method="POST" class="space-y-2">
                                @csrf
                                @method('PATCH')
                                <x-ui.select name="status" :placeholder="false">
                                    <option value="open" @selected($support->status === 'open')>Aberto</option>
                                    <option value="pending" @selected($support->status === 'pending')>Pendente</option>
                                    <option value="resolved" @selected($support->status === 'resolved')>Resolvido</option>
                                    <option value="closed" @selected($support->status === 'closed')>Fechado</option>
                                </x-ui.select>
                                <x-ui.button type="submit" class="w-full !justify-center">
                                    Salvar Status
                                </x-ui.button>
                            </form>
                        @else
                            <x-ui.badge :variant="$statusVariant" size="sm">
                                {{ $statusLabel }}
                            </x-ui.badge>
                        @endif
                    </x-ui.field>
                </div>
                <div class="mb-4">
                    <p class="mb-1.5 block text-sm font-medium" style="color: var(--text-secondary);">
                        Prioridade
                    </p>
                    <x-ui.badge :variant="$priorityVariant" size="sm">
                        {{ $priorityLabel }}
                    </x-ui.badge>
                </div>
                <div>
                    <p class="mb-1.5 block text-sm font-medium" style="color: var(--text-secondary);">
                        Última Atualização
                    </p>
                    <span class="mt-1 text-sm font-medium" style="color: var(--text-primary);">
                        {{ $support->updated_at->diffForHumans() }}
                    </span>
                </div>
            </x-ui.card>
        </div>
    </div>
</div>
