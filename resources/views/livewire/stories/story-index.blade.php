<div>
<x-common.page-breadcrumb title="Stories" />

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

            <div class="relative z-20 w-36">
                <x-form.multiple-select wire:model.live="statusFilter" :multiple="false" :options="[
        ['value' => 'active', 'label' => 'Ativas'],
        ['value' => 'expired', 'label' => 'Expiradas'],
        ['value' => 'all', 'label' => 'Todas'],
    ]" />
            </div>
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
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Pesquisar por autor..."
                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-zinc-200 bg-transparent py-2.5 pl-12 pr-14 text-sm text-zinc-800 shadow-theme-xs placeholder:text-zinc-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-zinc-800 dark:bg-white/3 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800 xl:w-[430px]" />
                </div>
            </form>

            @if (is_array($selected) && count($selected) > 0)
                <button wire:click="deleteSelected"
                    class="flex w-full items-center justify-center gap-2 rounded-lg border border-red-300 bg-red-200 px-4 py-[11px] text-sm font-medium text-red-700 shadow-theme-xs dark:border-red-700 dark:bg-red-800 dark:text-zinc-400 sm:w-auto">
                    Deletar Selecionados ({{ count($selected) }})
                </button>
            @endif
        </div>
    </div>

    <!-- Table -->
    <div class="max-w-full px-5 overflow-x-auto">
        <table class="min-w-full">
            <thead class="border-t border-zinc-100 border-y bg-zinc-50 dark:bg-zinc-900">
                <tr class="border-zinc-200 border-y dark:border-zinc-700">
                    <th scope="col" class="px-4 py-3 text-start sm:px-4 text-theme-sm">
                        <div wire:click="toggleSelectAll" class="cursor-pointer inline-flex">
                            <div class="flex h-5 w-5 items-center justify-center rounded-md border-[1.25px] transition-all duration-200"
                                :class="@js($selectAll) ? 'border-orange-500 bg-orange-500 text-white' : 'bg-transparent border-zinc-300 dark:border-zinc-700 text-transparent'">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                        </div>
                    </th>
                    <th scope="col"
                        class="px-4 py-3 font-normal text-zinc-500 text-start text-theme-sm dark:text-zinc-400">
                        Imagem
                    </th>
                    <th scope="col"
                        class="px-4 py-3 font-normal text-zinc-500 text-start text-theme-sm dark:text-zinc-400">
                        Autor
                    </th>
                    <th scope="col"
                        class="px-4 py-3 font-normal text-zinc-500 text-start text-theme-sm dark:text-zinc-400">
                        Status
                    </th>
                    <th scope="col"
                        class="px-4 py-3 font-normal text-zinc-500 text-start text-theme-sm dark:text-zinc-400">
                        Ações
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse($stories as $story)
                    <tr wire:key="{{ $story->id }}" @class(['hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors duration-200', 'relative' => is_array($selected) && in_array($story->id, $selected)])
                        @style(['background-color: rgba(251, 101, 20, 0.15)' => is_array($selected) && in_array($story->id, $selected)])>
                        <td class="px-4 py-4 whitespace-nowrap" @if(is_array($selected) && in_array($story->id, $selected))
                        style="border-left: 3px solid #fb6514;" @endif>
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" wire:model.live="selected" value="{{ $story->id }}" class="sr-only peer" />
                                <div class="flex h-5 w-5 items-center justify-center rounded-md border-[1.25px] transition-all duration-200 bg-transparent border-zinc-300 dark:border-zinc-700 peer-checked:border-blue-500 peer-checked:bg-blue-500 peer-checked:text-white text-transparent">
                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            </label>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="shrink-0 h-12 w-12">
                                    @if($story->image_url)
                                        <img src="{{ $story->image_url }}" alt="Story de {{ $story->user?->name }}"
                                            class="h-12 w-12 rounded object-cover">
                                    @else
                                        <div
                                            class="h-12 w-12 bg-zinc-200 dark:bg-zinc-700 rounded-lg flex items-center justify-center">
                                            <span class="text-zinc-400 text-xs">Sem imagem</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="text-sm text-zinc-700 dark:text-zinc-300">
                                {{ $story->user?->name ?? 'Usuário removido' }}
                            </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            @if($story->expires_at && $story->expires_at->isFuture())
                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                    Expira em {{ $story->expires_at->diffForHumans(['parts' => 1]) }}
                                </span>
                            @elseif($story->expires_at)
                                <span class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                    Expirada há {{ $story->expires_at->diffForHumans(['parts' => 1, 'suffix' => false]) }}
                                </span>
                            @else
                                <span class="text-xs text-zinc-500">N/A</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-sm font-medium text-right whitespace-nowrap">
                            <div class="flex justify-center gap-2">
                                <button wire:click="confirmDelete({{ $story->id }})"
                                    class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium transition-all disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg:not([class*='size-'])]:size-4 shrink-0 [&amp;_svg]:shrink-0 outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive active:scale-[.95] cursor-pointer hover:bg-accent hover:text-accent-foreground dark:hover:bg-accent/50 size-9 rounded-[50%] text-red-500 bg-red-500/10">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="lucide lucide-trash2 lucide-trash-2 w-5 h-5">
                                        <path d="M3 6h18"></path>
                                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                                        <line x1="10" x2="10" y1="11" y2="17"></line>
                                        <line x1="14" x2="14" y1="11" y2="17"></line>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-zinc-500">
                            Nenhuma story encontrada
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <!-- Pagination -->
    <div
        class="flex items-center flex-col sm:flex-row justify-between border-t border-zinc-200 px-5 py-4 dark:border-zinc-800">
        {{ $stories->links('components.pagination.custom') }}
    </div>
</div>

{{-- Delete Confirmation Modal --}}
@if($confirmingDeletion)
    <div class="fixed inset-0 bg-black/50 dark:bg-black/70 flex items-center justify-center z-50" wire:click="cancelDelete">
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
                        Tem certeza que deseja excluir esta story? Esta ação não pode ser desfeita.
                    </p>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-5">
                <button type="button" wire:click="cancelDelete"
                    class="px-4 py-2 bg-zinc-200 text-zinc-800 rounded-lg hover:bg-zinc-300 dark:bg-zinc-700 dark:text-zinc-200">
                    Cancelar
                </button>
                <button wire:click="delete" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Sim, Excluir
                </button>
            </div>
        </div>
    </div>
@endif
</div>
