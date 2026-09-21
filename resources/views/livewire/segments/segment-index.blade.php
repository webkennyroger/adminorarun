<div>
<x-common.page-breadcrumb title="Segmentos" />

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

            <div class="w-40">
                <x-ui.select wire:model.live="sportType" placeholder="Todos os esportes">
                    @foreach ($sportTypes as $type)
                        <option value="{{ $type }}" @selected($sportType === $type)>{{ ucfirst($type) }}</option>
                    @endforeach
                </x-ui.select>
            </div>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="xl:w-[430px]">
                <x-ui.input type="text" wire:model.live.debounce.300ms="search" placeholder="Pesquisar por nome..."
                    icon="search" />
            </div>

            @if (is_array($selected) && count($selected) > 0)
                <x-ui.button variant="danger" wire:click="deleteSelected">
                    Deletar Selecionados ({{ count($selected) }})
                </x-ui.button>
            @endif
        </div>
    </div>

    <!-- Table -->
    <div class="px-5">
        @if ($segments->count() > 0)
            <x-ui.data-table striped hoverable>
                <x-slot:header>
                    <th class="px-4 py-3 text-start sm:px-4 text-sm">
                        <x-ui.checkbox wire:click="toggleSelectAll" :checked="$selectAll" />
                    </th>
                    <th class="px-4 py-3 font-normal text-start text-sm" style="color: var(--text-secondary);">Nome</th>
                    <th class="px-4 py-3 font-normal text-start text-sm" style="color: var(--text-secondary);">Esporte</th>
                    <th class="px-4 py-3 font-normal text-start text-sm" style="color: var(--text-secondary);">Criado por</th>
                    <th class="px-4 py-3 font-normal text-start text-sm" style="color: var(--text-secondary);">Distância aprox.</th>
                    <th class="px-4 py-3 font-normal text-start text-sm" style="color: var(--text-secondary);">Tentativas</th>
                    <th class="px-4 py-3 font-normal text-start text-sm" style="color: var(--text-secondary);">Ações</th>
                </x-slot:header>

                @foreach ($segments as $segment)
                    <tr wire:key="{{ $segment->id }}"
                        @style(['background-color: rgba(251, 101, 20, 0.15)' => is_array($selected) && in_array($segment->id, $selected)])>
                        <td class="px-4 py-4 whitespace-nowrap"
                            @if (is_array($selected) && in_array($segment->id, $selected)) style="border-left: 3px solid #fb6514;" @endif>
                            <x-ui.checkbox wire:model.live="selected" value="{{ $segment->id }}" />
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium" style="color: var(--text-primary);">
                                {{ $segment->name }}
                            </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <x-ui.badge variant="success">
                                {{ ucfirst($segment->sport_type) }}
                            </x-ui.badge>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="text-sm" style="color: var(--text-secondary);">
                                {{ $segment->creator?->name ?? 'N/A' }}
                            </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="text-sm" style="color: var(--text-secondary);">
                                {{ $segment->radius_m }} m raio
                            </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="text-sm" style="color: var(--text-secondary);">
                                {{ $segment->efforts_count }}
                            </div>
                        </td>
                        <td class="px-4 py-4 text-sm font-medium text-right whitespace-nowrap">
                            <div class="flex justify-center gap-2">
                                <x-ui.button variant="ghost" size="sm" icon="chart-bar" title="Ver leaderboard"
                                    wire:click="viewLeaderboard({{ $segment->id }})" />
                                <x-ui.button variant="danger" size="sm" icon="trash" title="Excluir segmento"
                                    wire:click="confirmDelete({{ $segment->id }})" />
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-ui.data-table>
        @else
            <x-ui.empty-state title="Nenhum segmento encontrado"
                description="Ajuste os filtros de busca para tentar novamente." />
        @endif
    </div>
    <!-- Pagination -->
    <div class="px-5 py-4">
        <x-ui.pagination :paginator="$segments" />
    </div>
</x-ui.card>

{{-- Leaderboard Modal --}}
@if ($showLeaderboardModal && $selectedSegment)
    <div class="fixed inset-0 bg-black/50 dark:bg-black/70 flex items-center justify-center z-50"
        wire:click="closeLeaderboardModal">
        <div class="max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto" wire:click.stop>
            <x-ui.card>
                <x-slot:header>
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-xl font-semibold" style="color: var(--text-primary);">
                                Leaderboard &mdash; {{ $selectedSegment->name }}
                            </h3>
                            <p class="text-sm" style="color: var(--text-secondary);">
                                {{ ucfirst($selectedSegment->sport_type) }} &middot; {{ $selectedSegment->efforts_count }} tentativas
                                registradas
                            </p>
                        </div>
                        <x-ui.button variant="ghost" size="sm" icon="x-mark" title="Fechar"
                            wire:click="closeLeaderboardModal" />
                    </div>
                </x-slot:header>

                @if ($leaderboard->isEmpty())
                    <x-ui.empty-state title="Nenhuma tentativa registrada"
                        description="Nenhuma tentativa registrada para este segmento ainda." />
                @else
                    <x-ui.data-table compact>
                        <x-slot:header>
                            <th class="px-3 py-2 font-normal text-start text-sm" style="color: var(--text-secondary);">#</th>
                            <th class="px-3 py-2 font-normal text-start text-sm" style="color: var(--text-secondary);">Atleta</th>
                            <th class="px-3 py-2 font-normal text-start text-sm" style="color: var(--text-secondary);">Tempo</th>
                            <th class="px-3 py-2 font-normal text-start text-sm" style="color: var(--text-secondary);">Data</th>
                        </x-slot:header>

                        @foreach ($leaderboard as $index => $effort)
                            <tr wire:key="effort-{{ $effort->id }}">
                                <td class="px-3 py-3 whitespace-nowrap">
                                    <span class="text-sm font-semibold" style="color: var(--text-primary);">
                                        {{ $index + 1 }}º
                                    </span>
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <x-ui.avatar :name="$effort->user?->name ?? 'Usuário removido'" size="xs" />
                                        <span class="text-sm" style="color: var(--text-primary);">
                                            {{ $effort->user?->name ?? 'Usuário removido' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap">
                                    <span class="text-sm" style="color: var(--text-secondary);">
                                        {{ $effort->duration_seconds < 3600 ? gmdate('i:s', $effort->duration_seconds) : gmdate('H:i:s', $effort->duration_seconds) }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap">
                                    <span class="text-sm" style="color: var(--text-secondary);">
                                        {{ $effort->achieved_at->format('d/m/Y H:i') }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </x-ui.data-table>
                @endif
            </x-ui.card>
        </div>
    </div>
@endif

{{-- Delete Confirmation Modal --}}
@if ($confirmingDeletion)
    <div class="fixed inset-0 bg-black/50 dark:bg-black/70 flex items-center justify-center z-50"
        wire:click="cancelDelete">
        <div class="max-w-md w-full mx-4" wire:click.stop>
            <x-ui.card>
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center"
                        style="background-color: rgba(239, 68, 68, 0.1);">
                        <x-ui.icon name="exclamation-triangle" class="w-6 h-6" style="color: #f87171;" />
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold" style="color: var(--text-primary);">Confirmar Exclusão</h3>
                        <p class="text-sm mt-1" style="color: var(--text-secondary);">
                            Tem certeza que deseja excluir este segmento? Todas as tentativas registradas para ele
                            também serão removidas. Esta ação não pode ser desfeita.
                        </p>
                    </div>
                </div>

                <x-slot:footer>
                    <x-ui.button variant="secondary" wire:click="cancelDelete">Cancelar</x-ui.button>
                    <x-ui.button variant="danger" wire:click="delete">Sim, Excluir</x-ui.button>
                </x-slot:footer>
            </x-ui.card>
        </div>
    </div>
@endif
</div>
