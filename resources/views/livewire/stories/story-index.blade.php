<div>
<x-common.page-breadcrumb title="Stories" />

@php
    $searchIconPath = 'M11 19a8 8 0 1 0 0-16 8 8 0 0 0 0 16zM21 21l-4.3-4.3';

    // Réplica manual das classes computadas por <x-ui.button> (variant/size).
    // Não usamos o componente aqui por um bug de compilação pré-existente em
    // resources/views/components/ui/button.blade.php: o comentário da prop
    // "startIcon" contém o texto literal "<x-ui.icon>", que o compilador do
    // Blade casa como uma tag de componente real e corrompe a view inteira
    // (ErrorException: Undefined variable $component). Esse arquivo está
    // fora do escopo autorizado para este refactor, então replicamos aqui o
    // visual exato do botão em vez de usar o componente quebrado.
    $btnBase = 'inline-flex items-center justify-center font-semibold rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';
    $btnSecondary = $btnBase.' px-4 py-2.5 text-sm gap-2 hover:opacity-80 focus:ring-emerald-500/30';
    $btnSecondaryStyle = 'background-color: var(--bg-elevated); color: var(--text-primary); border: 1px solid var(--border-color);';
    $btnDanger = $btnBase.' px-4 py-2.5 text-sm gap-2 hover:opacity-80 focus:ring-red-500/50';
    $btnDangerStyle = 'background-color: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3);';
@endphp

<x-ui.card>
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
                    <option value="-1">Todos</option>
                </x-ui.select>
            </div>
            <span class="text-sm" style="color: var(--text-secondary);">entradas</span>

            <div class="w-36">
                <x-ui.select wire:model.live="statusFilter" :placeholder="false">
                    <option value="active">Ativas</option>
                    <option value="expired">Expiradas</option>
                    <option value="all">Todas</option>
                </x-ui.select>
            </div>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="w-full sm:w-95">
                <x-ui.input wire:model.live.debounce.300ms="search" type="text" placeholder="Pesquisar por autor..."
                    :icon="$searchIconPath" />
            </div>

            @if (is_array($selected) && count($selected) > 0)
                <button type="button" wire:click="deleteSelected" class="{{ $btnDanger }}" style="{{ $btnDangerStyle }}">
                    Deletar Selecionados ({{ count($selected) }})
                </button>
            @endif
        </div>
    </div>
</x-ui.card>

<div class="mt-4">
    <x-ui.data-table>
        <x-slot:header>
            <th scope="col" class="px-4 py-3">
                <x-ui.checkbox wire:click="toggleSelectAll" :checked="$selectAll" />
            </th>
            <th scope="col" class="px-4 py-3 font-medium">Imagem</th>
            <th scope="col" class="px-4 py-3 font-medium">Autor</th>
            <th scope="col" class="px-4 py-3 font-medium">Status</th>
            <th scope="col" class="px-4 py-3 font-medium text-right">Ações</th>
        </x-slot:header>

        @forelse($stories as $story)
            <tr wire:key="{{ $story->id }}" @class(['relative' => is_array($selected) && in_array($story->id, $selected)])
                @style(['background-color: rgba(251, 101, 20, 0.15)' => is_array($selected) && in_array($story->id, $selected)])>
                <td class="px-4 py-4 whitespace-nowrap" @if(is_array($selected) && in_array($story->id, $selected))
                style="border-left: 3px solid #fb6514;" @endif>
                    <x-ui.checkbox wire:model.live="selected" value="{{ $story->id }}" />
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        @if($story->image_url)
                            <img src="{{ $story->image_url }}" alt="Story de {{ $story->user?->name }}"
                                class="h-12 w-12 rounded object-cover shrink-0">
                        @else
                            <div class="h-12 w-12 rounded-lg flex items-center justify-center shrink-0"
                                style="background-color: var(--bg-elevated);">
                                <span class="text-xs" style="color: var(--text-muted);">Sem imagem</span>
                            </div>
                        @endif
                    </div>
                </td>
                <td class="px-4 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-3">
                        <x-ui.avatar :name="$story->user?->name" size="sm" />
                        <div class="text-sm" style="color: var(--text-primary);">
                            {{ $story->user?->name ?? 'Usuário removido' }}
                        </div>
                    </div>
                </td>
                <td class="px-4 py-4 whitespace-nowrap">
                    @if($story->expires_at && $story->expires_at->isFuture())
                        <x-ui.badge variant="success">
                            Expira em {{ $story->expires_at->diffForHumans(['parts' => 1]) }}
                        </x-ui.badge>
                    @elseif($story->expires_at)
                        <x-ui.badge variant="default">
                            Expirada há {{ $story->expires_at->diffForHumans(['parts' => 1, 'suffix' => false]) }}
                        </x-ui.badge>
                    @else
                        <span class="text-xs" style="color: var(--text-muted);">N/A</span>
                    @endif
                </td>
                <td class="px-4 py-4 text-sm font-medium whitespace-nowrap">
                    <div class="flex justify-end gap-2">
                        <button type="button" wire:click="confirmDelete({{ $story->id }})" title="Excluir"
                            class="inline-flex items-center justify-center rounded-full p-2 text-red-500 bg-red-500/10 hover:bg-red-500/20 transition-colors duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="w-5 h-5">
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
                <td colspan="5" class="p-0">
                    <x-ui.empty-state title="Nenhuma story encontrada"
                        description="Ajuste os filtros de busca para encontrar stories." />
                </td>
            </tr>
        @endforelse

        <x-slot:footer>
            <x-ui.pagination :paginator="$stories" />
        </x-slot:footer>
    </x-ui.data-table>
</div>

{{-- Delete Confirmation Modal --}}
@if($confirmingDeletion)
    <div class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm bg-black/50 dark:bg-black/70"
        wire:click="cancelDelete">
        <div class="w-full max-w-md mx-4 rounded-2xl shadow-2xl"
            style="background-color: var(--bg-surface); border: 1px solid var(--border-color);" wire:click.stop>
            <div class="flex items-center gap-4 px-6 py-5">
                <div class="shrink-0 w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold" style="color: var(--text-primary);">Confirmar Exclusão</h3>
                    <p class="text-sm mt-1" style="color: var(--text-secondary);">
                        Tem certeza que deseja excluir esta story? Esta ação não pode ser desfeita.
                    </p>
                </div>
            </div>

            <div class="flex justify-end gap-3 px-6 py-4" style="border-top: 1px solid var(--border-color);">
                <button type="button" wire:click="cancelDelete" class="{{ $btnSecondary }}" style="{{ $btnSecondaryStyle }}">
                    Cancelar
                </button>
                <button type="button" wire:click="delete" class="{{ $btnDanger }}" style="{{ $btnDangerStyle }}">
                    Sim, Excluir
                </button>
            </div>
        </div>
    </div>
@endif
</div>
