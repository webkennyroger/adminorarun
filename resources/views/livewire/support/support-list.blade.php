<div>
    <x-common.page-breadcrumb title="Tickets de Suporte" />

    <x-ui.card>
        <div class="flex flex-col gap-4">
            <!-- First Row: Status Filters -->
            <div class="flex items-center gap-2">
                <x-ui.button size="sm" :variant="$status === 'all' ? 'primary' : 'secondary'"
                    wire:click="setFilter('all')">
                    Todos
                </x-ui.button>
                <x-ui.button size="sm" :variant="$status === 'solved' ? 'primary' : 'secondary'"
                    wire:click="setFilter('solved')">
                    Resolvidos
                </x-ui.button>
                <x-ui.button size="sm" :variant="$status === 'pending' ? 'primary' : 'secondary'"
                    wire:click="setFilter('pending')">
                    Pendentes
                </x-ui.button>
            </div>

            <!-- Second Row: Controls -->
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-sm" style="color: var(--text-secondary);">Mostrar</span>
                    <div class="w-24">
                        <x-ui.select wire:model.live="perPage" :placeholder="false">
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </x-ui.select>
                    </div>
                    <span class="text-sm" style="color: var(--text-secondary);">entradas</span>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="w-full sm:w-[300px]">
                        <x-ui.input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar..."
                            icon="M3.04199 9.37363C3.04199 5.87693 5.87735 3.04199 9.37533 3.04199C12.8733 3.04199 15.7087 5.87693 15.7087 9.37363C15.7087 12.8703 12.8733 15.7053 9.37533 15.7053C5.87735 15.7053 3.04199 12.8703 3.04199 9.37363ZM9.37533 1.54199C5.04926 1.54199 1.54199 5.04817 1.54199 9.37363C1.54199 13.6991 5.04926 17.2053 9.37533 17.2053C11.2676 17.2053 13.0032 16.5344 14.3572 15.4176L17.1773 18.238C17.4702 18.5309 17.945 18.5309 18.2379 18.238C18.5308 17.9451 18.5309 17.4703 18.238 17.1773L15.4182 14.3573C16.5367 13.0033 17.2087 11.2669 17.2087 9.37363C17.2087 5.04817 13.7014 1.54199 9.37533 1.54199Z" />
                    </div>

                    @if (count($selected) > 0)
                        <x-ui.button variant="danger" wire:click="deleteSelected">
                            Deletar Selecionados ({{ count($selected) }})
                        </x-ui.button>
                    @endif

                    <x-ui.button variant="primary" :href="route('support.index')">
                        Novo Ticket
                    </x-ui.button>
                </div>
            </div>
        </div>
    </x-ui.card>

    <div class="mt-4">
        <x-ui.data-table>
            <x-slot:header>
                <th scope="col" class="px-4 py-3 w-12">
                    <div class="flex items-center justify-center">
                        <x-ui.checkbox wire:model.live="selectAll" />
                    </div>
                </th>
                <th scope="col" class="px-4 py-3 font-medium w-32">Ticket ID</th>
                <th scope="col" class="px-4 py-3 font-medium">Assunto</th>
                <th scope="col" class="px-4 py-3 font-medium w-48">Solicitado Por</th>
                <th scope="col" class="px-4 py-3 font-medium w-32">Status</th>
                <th scope="col" class="px-4 py-3 font-medium w-32">Prioridade</th>
                <th scope="col" class="px-4 py-3 font-medium w-40">Criado em</th>
                <th scope="col" class="px-4 py-3 font-medium text-center w-24">Ação</th>
            </x-slot:header>

            @forelse ($tickets as $ticket)
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
                    $priorityVariant = match ($ticket->priority) {
                        'high' => 'danger',
                        'medium' => 'warning',
                        default => 'success',
                    };
                    $priorityLabel = match ($ticket->priority) {
                        'high' => 'Alta',
                        'medium' => 'Média',
                        'low' => 'Baixa',
                        default => ucfirst($ticket->priority),
                    };
                @endphp
                <tr wire:key="{{ $ticket->id }}">
                    <td class="px-4 py-4 whitespace-nowrap">
                        <div class="flex items-center justify-center">
                            <x-ui.checkbox wire:model.live="selected" value="{{ $ticket->id }}" />
                        </div>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap">
                        <a href="{{ route('support.show', $ticket) }}" class="text-sm font-medium"
                            style="color: var(--text-primary);">
                            {{ $ticket->ticket_id }}
                        </a>
                    </td>
                    <td class="px-4 py-4">
                        <div class="text-sm" style="color: var(--text-primary);">
                            {{ Str::limit($ticket->subject, 40) }}
                        </div>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap">
                        <div class="text-sm">
                            <div class="font-medium" style="color: var(--text-primary);">
                                {{ optional($ticket->user)->name ?? 'Desconhecido' }}
                            </div>
                            <div class="text-xs" style="color: var(--text-muted);">
                                {{ optional($ticket->user)->email ?? 'Sem Email' }}
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap">
                        <x-ui.badge :variant="$statusVariant" size="sm">
                            {{ $statusLabel }}
                        </x-ui.badge>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap">
                        <x-ui.badge :variant="$priorityVariant" size="sm">
                            {{ $priorityLabel }}
                        </x-ui.badge>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap">
                        <div class="text-sm" style="color: var(--text-secondary);">
                            {{ $ticket->created_at->format('d/m/Y H:i') }}
                        </div>
                    </td>
                    <td class="px-4 py-4 text-sm font-medium whitespace-nowrap">
                        <div class="flex justify-center gap-2">
                            <x-ui.button variant="ghost" size="xs" :href="route('support.show', $ticket)"
                                title="Ver ticket" class="!rounded-full !p-2 text-blue-500 bg-blue-500/10 hover:bg-blue-500/20">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="w-5 h-5">
                                    <path
                                        d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0">
                                    </path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </x-ui.button>
                            <x-ui.button variant="ghost" size="xs" wire:click="confirmDelete({{ $ticket->id }})"
                                title="Excluir ticket" class="!rounded-full !p-2 text-red-500 bg-red-500/10 hover:bg-red-500/20">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="w-5 h-5">
                                    <path d="M3 6h18"></path>
                                    <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                    <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                                    <line x1="10" x2="10" y1="11" y2="17"></line>
                                    <line x1="14" x2="14" y1="11" y2="17"></line>
                                </svg>
                            </x-ui.button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="p-0">
                        <x-ui.empty-state title="Nenhum ticket encontrado"
                            description="Ajuste os filtros de busca ou aguarde novas solicitações." />
                    </td>
                </tr>
            @endforelse

            <x-slot:footer>
                <x-ui.pagination :paginator="$tickets" />
            </x-slot:footer>
        </x-ui.data-table>
    </div>

    {{-- Delete Confirmation Modal --}}
    @if($confirmingDeletion)
        <div class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm bg-black/50 dark:bg-black/70 px-4"
            wire:click="$set('confirmingDeletion', false)">
            <div class="w-full max-w-md mx-4 rounded-2xl shadow-2xl"
                style="background-color: var(--bg-surface); border: 1px solid var(--border-color);" wire:click.stop>
                <div class="flex items-center gap-4 px-6 py-5">
                    <div class="flex-shrink-0 w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold" style="color: var(--text-primary);">Confirmar Exclusão</h3>
                        <p class="text-sm mt-1" style="color: var(--text-secondary);">
                            Tem certeza que deseja excluir o ticket "<strong>{{ $ticketToDelete?->subject }}</strong>"?
                            Esta ação não pode ser desfeita.
                        </p>
                    </div>
                </div>

                <div class="flex justify-end gap-3 px-6 py-4" style="border-top: 1px solid var(--border-color);">
                    <x-ui.button type="button" variant="secondary" wire:click="$set('confirmingDeletion', false)">
                        Cancelar
                    </x-ui.button>
                    <x-ui.button variant="danger" wire:click="delete">
                        Sim, Excluir
                    </x-ui.button>
                </div>
            </div>
        </div>
    @endif
</div>
