<div>
    <x-common.page-breadcrumb title="Chat" />

    <div class="rounded-2xl border border-zinc-200 bg-white pt-4 dark:border-zinc-800 dark:bg-white/[0.03]">
        <!-- Header -->
        <div class="flex flex-col gap-2 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <span class="text-zinc-500 dark:text-zinc-400">Mostrar</span>
                <div class="relative z-20 bg-transparent">
                    <div class="relative z-20 w-24">
                        <x-form.multiple-select wire:model.live="perPage" :multiple="false" :options="[
        ['value' => 5, 'label' => '5'],
        ['value' => 10, 'label' => '10'],
        ['value' => 25, 'label' => '25'],
        ['value' => 50, 'label' => '50'],
        ['value' => 100, 'label' => '100'],
        ['value' => -1, 'label' => 'Todos']
    ]" />
                    </div>
                </div>
                <span class="text-zinc-500 dark:text-zinc-400">entradas</span>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <form>
                    <div class="relative">
                        <span class="absolute -translate-y-1/2 pointer-events-none left-4 top-1/2">
                            <!-- Search Icon -->
                            <svg class="fill-zinc-500 dark:fill-zinc-400" width="20" height="20" viewBox="0 0 20 20"
                                fill="none">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M3.04175 9.37363C3.04175 5.87693 5.87711 3.04199 9.37508 3.04199C12.8731 3.04199 15.7084 5.87693 15.7084 9.37363C15.7084 12.8703 12.8731 15.7053 9.37508 15.7053C5.87711 15.7053 3.04175 12.8703 3.04175 9.37363ZM9.37508 1.54199C5.04902 1.54199 1.54175 5.04817 1.54175 9.37363C1.54175 13.6991 5.04902 17.2053 9.37508 17.2053C11.2674 17.2053 13.003 16.5344 14.357 15.4176L17.177 18.238C17.4699 18.5309 17.9448 18.5309 18.2377 18.238C18.5306 17.9451 18.5306 17.4703 18.2377 17.1774L15.418 14.3573C16.5365 13.0033 17.2084 11.2669 17.2084 9.37363C17.2084 5.04817 13.7011 1.54199 9.37508 1.54199Z"
                                    fill="" />
                            </svg>
                        </span>
                        <input wire:model.live.debounce.300ms="search" type="text"
                            placeholder="Pesquisar por participante ou grupo..."
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-zinc-200 bg-transparent py-2.5 pl-12 pr-4 text-sm text-zinc-800 shadow-theme-xs placeholder:text-zinc-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-zinc-800 dark:bg-white/3 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800 xl:w-[430px]" />
                    </div>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="max-w-full px-5 overflow-x-auto">
            <table class="min-w-full">
                <thead class="border-t border-zinc-100 border-y bg-zinc-50 dark:bg-zinc-900">
                    <tr class="border-zinc-200 border-y dark:border-zinc-700">
                        <th scope="col"
                            class="px-4 py-3 font-normal text-zinc-500 text-start text-theme-sm dark:text-zinc-400">
                            Tipo
                        </th>
                        <th scope="col"
                            class="px-4 py-3 font-normal text-zinc-500 text-start text-theme-sm dark:text-zinc-400">
                            Conversa
                        </th>
                        <th scope="col"
                            class="px-4 py-3 font-normal text-zinc-500 text-start text-theme-sm dark:text-zinc-400">
                            Última mensagem
                        </th>
                        <th scope="col"
                            class="px-4 py-3 font-normal text-zinc-500 text-start text-theme-sm dark:text-zinc-400">
                            Quando
                        </th>
                        <th scope="col"
                            class="px-4 py-3 font-normal text-zinc-500 text-start text-theme-sm dark:text-zinc-400">
                            Ações
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($conversations as $conversation)
                        <tr wire:key="{{ $conversation['key'] }}"
                            class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors duration-200">
                            <td class="px-4 py-4 whitespace-nowrap">
                                @if ($conversation['type'] === 'group')
                                    <span
                                        class="inline-flex items-center rounded-full bg-brand-100 px-2.5 py-0.5 text-xs font-medium text-brand-700 dark:bg-brand-900/30 dark:text-brand-400">
                                        Grupo
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                        Direto
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="text-sm text-zinc-700 dark:text-zinc-300">
                                    {{ $conversation['title'] }}
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ Str::limit($conversation['last_message'] ?? '', 60) }}
                                </div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ \Illuminate\Support\Carbon::parse($conversation['last_at'])->format('d/m/Y H:i') }}
                                </div>
                            </td>
                            <td class="px-4 py-4 text-sm font-medium whitespace-nowrap">
                                @if ($conversation['type'] === 'direct')
                                    <button
                                        wire:click="openThread('direct', {{ $conversation['user_a_id'] }}, {{ $conversation['user_b_id'] }})"
                                        class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium transition-all disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg:not([class*='size-'])]:size-4 shrink-0 [&amp;_svg]:shrink-0 outline-none active:scale-[.95] cursor-pointer hover:bg-accent hover:text-accent-foreground dark:hover:bg-accent/50 size-9 rounded-[50%] text-blue-500 bg-primary/10">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye w-5 h-5">
                                            <path
                                                d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0">
                                            </path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>
                                @else
                                    <button wire:click="openThread('group', null, null, {{ $conversation['group_id'] }})"
                                        class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium transition-all disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg:not([class*='size-'])]:size-4 shrink-0 [&amp;_svg]:shrink-0 outline-none active:scale-[.95] cursor-pointer hover:bg-accent hover:text-accent-foreground dark:hover:bg-accent/50 size-9 rounded-[50%] text-blue-500 bg-primary/10">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye w-5 h-5">
                                            <path
                                                d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0">
                                            </path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-zinc-500">
                                Nenhuma conversa encontrada
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <div
            class="flex items-center flex-col sm:flex-row justify-between border-t border-zinc-200 px-5 py-4 dark:border-zinc-800">
            {{ $conversations->links('components.pagination.custom') }}
        </div>
    </div>

    {{-- Thread Modal (read-only) --}}
    @if ($showThreadModal)
        <div class="fixed inset-0 bg-black/50 dark:bg-black/70 flex items-center justify-center z-50"
            wire:click="closeThreadModal">
            <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto flex flex-col"
                wire:click.stop>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $threadTitle }}</h3>
                    <button wire:click="closeThreadModal"
                        class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-4">
                    Modo somente leitura &mdash; exibindo as últimas {{ count($threadMessages) }} mensagens. O admin
                    pode excluir uma mensagem em caso de abuso.
                </p>

                <div class="space-y-3 overflow-y-auto flex-1 pr-1">
                    @forelse ($threadMessages as $message)
                        <div wire:key="thread-message-{{ $message['id'] }}"
                            class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-medium text-zinc-800 dark:text-zinc-200">
                                        {{ $message['author'] }}
                                    </p>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1 whitespace-pre-wrap">
                                        {{ $message['content'] }}
                                    </p>
                                    <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">
                                        {{ \Illuminate\Support\Carbon::parse($message['created_at'])->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                                <button wire:click="confirmMessageDeletion({{ $message['id'] }})"
                                    title="Excluir mensagem (abuso)"
                                    class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium transition-all disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg:not([class*='size-'])]:size-4 shrink-0 [&amp;_svg]:shrink-0 outline-none active:scale-[.95] cursor-pointer hover:bg-accent hover:text-accent-foreground dark:hover:bg-accent/50 size-8 rounded-[50%] text-red-500 bg-red-500/10 shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="lucide lucide-trash2 lucide-trash-2 w-4 h-4">
                                        <path d="M3 6h18"></path>
                                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                                        <line x1="10" x2="10" y1="11" y2="17"></line>
                                        <line x1="14" x2="14" y1="11" y2="17"></line>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-zinc-500 dark:text-zinc-400 text-center py-6">
                            Nenhuma mensagem nesta conversa.
                        </p>
                    @endforelse
                </div>

                <div class="flex justify-end pt-4">
                    <button type="button" wire:click="closeThreadModal"
                        class="px-4 py-2 bg-zinc-200 text-zinc-800 rounded-lg hover:bg-zinc-300 dark:bg-zinc-700 dark:text-zinc-200">
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Message Confirmation Modal --}}
    @if ($confirmingMessageDeletion)
        <div class="fixed inset-0 bg-black/50 dark:bg-black/70 flex items-center justify-center z-[60]"
            wire:click="cancelMessageDeletion">
            <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 max-w-md w-full mx-4" wire:click.stop>
                <div class="flex items-center gap-4 mb-4">
                    <div
                        class="flex-shrink-0 w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Confirmar Exclusão</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                            Tem certeza que deseja excluir esta mensagem? Esta ação é permanente e deve ser usada
                            apenas em casos de abuso.
                        </p>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-5">
                    <button type="button" wire:click="cancelMessageDeletion"
                        class="px-4 py-2 bg-zinc-200 text-zinc-800 rounded-lg hover:bg-zinc-300 dark:bg-zinc-700 dark:text-zinc-200">
                        Cancelar
                    </button>
                    <button wire:click="deleteMessage" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Sim, Excluir
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
