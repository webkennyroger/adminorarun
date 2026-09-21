<div>
    <x-common.page-breadcrumb title="Chat" />

    <x-ui.card :padding="false">
        <!-- Header -->
        <div class="flex flex-col gap-2 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <span class="text-sm" style="color: var(--text-secondary);">Mostrar</span>
                <div class="w-24">
                    @php $perPageOptions = [5, 10, 25, 50, 100, -1]; @endphp
                    <x-ui.select wire:model.live="perPage" :placeholder="null">
                        @foreach ($perPageOptions as $option)
                            <option value="{{ $option }}" @selected($perPage == $option)>{{ $option === -1 ? 'Todos' : $option }}</option>
                        @endforeach
                    </x-ui.select>
                </div>
                <span class="text-sm" style="color: var(--text-secondary);">entradas</span>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="xl:w-[430px]">
                    <x-ui.input type="text" wire:model.live.debounce.300ms="search"
                        placeholder="Pesquisar por participante ou grupo..." icon="search" />
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="px-5">
            @if ($conversations->count() > 0)
                <x-ui.data-table striped hoverable>
                    <x-slot:header>
                        <th class="px-4 py-3 font-normal text-start text-sm" style="color: var(--text-secondary);">Tipo</th>
                        <th class="px-4 py-3 font-normal text-start text-sm" style="color: var(--text-secondary);">Conversa</th>
                        <th class="px-4 py-3 font-normal text-start text-sm" style="color: var(--text-secondary);">Última mensagem</th>
                        <th class="px-4 py-3 font-normal text-start text-sm" style="color: var(--text-secondary);">Quando</th>
                        <th class="px-4 py-3 font-normal text-start text-sm" style="color: var(--text-secondary);">Ações</th>
                    </x-slot:header>

                    @foreach ($conversations as $conversation)
                        <tr wire:key="{{ $conversation['key'] }}">
                            <td class="px-4 py-4 whitespace-nowrap">
                                @if ($conversation['type'] === 'group')
                                    <x-ui.badge variant="info">Grupo</x-ui.badge>
                                @else
                                    <x-ui.badge variant="default">Direto</x-ui.badge>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <x-ui.avatar
                                        :name="$conversation['type'] === 'group' ? Str::after($conversation['title'], 'Grupo: ') : Str::before($conversation['title'], ' & ')"
                                        size="sm" />
                                    <span class="text-sm" style="color: var(--text-primary);">
                                        {{ $conversation['title'] }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="text-sm" style="color: var(--text-secondary);">
                                    {{ Str::limit($conversation['last_message'] ?? '', 60) }}
                                </div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="text-sm" style="color: var(--text-secondary);">
                                    {{ \Illuminate\Support\Carbon::parse($conversation['last_at'])->format('d/m/Y H:i') }}
                                </div>
                            </td>
                            <td class="px-4 py-4 text-sm font-medium whitespace-nowrap">
                                @if ($conversation['type'] === 'direct')
                                    <x-ui.button variant="ghost" size="sm" icon="eye" title="Ver conversa"
                                        wire:click="openThread('direct', {{ $conversation['user_a_id'] }}, {{ $conversation['user_b_id'] }})" />
                                @else
                                    <x-ui.button variant="ghost" size="sm" icon="eye" title="Ver conversa"
                                        wire:click="openThread('group', null, null, {{ $conversation['group_id'] }})" />
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </x-ui.data-table>
            @else
                <x-ui.empty-state title="Nenhuma conversa encontrada"
                    description="Ajuste os filtros de busca para tentar novamente." />
            @endif
        </div>
        <!-- Pagination -->
        <div class="px-5 py-4">
            <x-ui.pagination :paginator="$conversations" />
        </div>
    </x-ui.card>

    {{-- Thread Modal (read-only) --}}
    @if ($showThreadModal)
        <div class="fixed inset-0 bg-black/50 dark:bg-black/70 flex items-center justify-center z-50"
            wire:click="closeThreadModal">
            <div class="max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto" wire:click.stop>
                <x-ui.card>
                    <x-slot:header>
                        <div class="flex justify-between items-center">
                            <h3 class="text-xl font-semibold" style="color: var(--text-primary);">
                                {{ $threadTitle }}
                            </h3>
                            <x-ui.button variant="ghost" size="sm" icon="x-mark" title="Fechar"
                                wire:click="closeThreadModal" />
                        </div>
                    </x-slot:header>

                    <p class="text-xs mb-4" style="color: var(--text-muted);">
                        Modo somente leitura &mdash; exibindo as últimas {{ count($threadMessages) }} mensagens. O
                        admin pode excluir uma mensagem em caso de abuso.
                    </p>

                    <div class="space-y-3 max-h-[55vh] overflow-y-auto pr-1">
                        @forelse ($threadMessages as $message)
                            <div wire:key="thread-message-{{ $message['id'] }}" class="rounded-lg p-3"
                                style="border: 1px solid var(--border-color);">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-start gap-3">
                                        <x-ui.avatar :name="$message['author']" size="sm" />
                                        <div>
                                            <p class="text-sm font-medium" style="color: var(--text-primary);">
                                                {{ $message['author'] }}
                                            </p>
                                            <p class="text-sm mt-1 whitespace-pre-wrap" style="color: var(--text-secondary);">
                                                {{ $message['content'] }}
                                            </p>
                                            <p class="text-xs mt-1" style="color: var(--text-muted);">
                                                {{ \Illuminate\Support\Carbon::parse($message['created_at'])->format('d/m/Y H:i') }}
                                            </p>
                                        </div>
                                    </div>
                                    <x-ui.button variant="danger" size="xs" icon="trash"
                                        title="Excluir mensagem (abuso)"
                                        wire:click="confirmMessageDeletion({{ $message['id'] }})" />
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-center py-6" style="color: var(--text-secondary);">
                                Nenhuma mensagem nesta conversa.
                            </p>
                        @endforelse
                    </div>

                    <x-slot:footer>
                        <x-ui.button variant="secondary" wire:click="closeThreadModal">Fechar</x-ui.button>
                    </x-slot:footer>
                </x-ui.card>
            </div>
        </div>
    @endif

    {{-- Delete Message Confirmation Modal --}}
    @if ($confirmingMessageDeletion)
        <div class="fixed inset-0 bg-black/50 dark:bg-black/70 flex items-center justify-center z-[60]"
            wire:click="cancelMessageDeletion">
            <div class="max-w-md w-full mx-4" wire:click.stop>
                <x-ui.card>
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center"
                            style="background-color: rgba(239, 68, 68, 0.1);">
                            <x-ui.icon name="exclamation-triangle" class="w-6 h-6" style="color: #f87171;" />
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold" style="color: var(--text-primary);">
                                Confirmar Exclusão
                            </h3>
                            <p class="text-sm mt-1" style="color: var(--text-secondary);">
                                Tem certeza que deseja excluir esta mensagem? Esta ação é permanente e deve ser
                                usada apenas em casos de abuso.
                            </p>
                        </div>
                    </div>

                    <x-slot:footer>
                        <x-ui.button variant="secondary" wire:click="cancelMessageDeletion">Cancelar</x-ui.button>
                        <x-ui.button variant="danger" wire:click="deleteMessage">Sim, Excluir</x-ui.button>
                    </x-slot:footer>
                </x-ui.card>
            </div>
        </div>
    @endif
</div>
