<div>
    <x-common.page-breadcrumb title="Denúncias" />

    <x-ui.card :padding="false">
        <!-- Header / Filtros -->
        <div class="flex flex-col gap-2 px-4 py-4 sm:flex-row sm:items-center sm:justify-between"
            style="border-bottom: 1px solid var(--border-color);">
            <div class="flex items-center gap-3">
                <span class="text-sm" style="color: var(--text-secondary);">Mostrar</span>
                <div class="relative z-20 bg-transparent">
                    <div class="relative z-20 w-24">
                        <x-form.multiple-select wire:model.live="perPage" :multiple="false" :options="[
                            ['value' => 5, 'label' => '5'],
                            ['value' => 10, 'label' => '10'],
                            ['value' => 25, 'label' => '25'],
                            ['value' => 50, 'label' => '50'],
                            ['value' => -1, 'label' => 'Todos'],
                        ]" />
                    </div>
                </div>
                <span class="text-sm" style="color: var(--text-secondary);">entradas</span>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="relative z-20 w-full sm:w-48">
                    <x-form.multiple-select wire:model.live="statusFilter" :multiple="false" placeholder="Todas"
                        :options="[
                            ['value' => '', 'label' => 'Todas'],
                            ['value' => 'pending', 'label' => 'Pendentes'],
                            ['value' => 'reviewed', 'label' => 'Revisadas'],
                            ['value' => 'resolved', 'label' => 'Resolvidas'],
                        ]" />
                </div>

                <form>
                    <div class="relative">
                        <span class="absolute -translate-y-1/2 pointer-events-none left-4 top-1/2">
                            <x-ui.icon name="search" class="w-5 h-5" style="color: var(--text-muted);" />
                        </span>
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Pesquisar..."
                            class="h-11 w-full rounded-lg py-2.5 pl-12 pr-4 text-sm focus:outline-hidden focus:ring-3 focus:ring-emerald-500/10 xl:w-[300px]"
                            style="background-color: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-primary);" />
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabela -->
        <div class="p-4">
            <x-ui.data-table striped hoverable>
                <x-slot:header>
                    <th scope="col" class="px-4 py-3 font-normal text-start">Denunciante</th>
                    <th scope="col" class="px-4 py-3 font-normal text-start">Denunciado</th>
                    <th scope="col" class="px-4 py-3 font-normal text-start">Motivo</th>
                    <th scope="col" class="px-4 py-3 font-normal text-start">Status</th>
                    <th scope="col" class="px-4 py-3 font-normal text-start">Data</th>
                    <th scope="col" class="px-4 py-3 font-normal text-start">Ações</th>
                </x-slot:header>

                @forelse ($reports as $report)
                    <tr wire:key="{{ $report->id }}">
                        <td class="px-4 py-4 whitespace-nowrap">
                            <x-ui.text size="sm" variant="strong">
                                {{ $report->reporter?->name ?? 'Usuário removido' }}
                            </x-ui.text>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <x-ui.text size="sm">
                                {{ $this->reportedContentLabel($report) }}
                            </x-ui.text>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <x-ui.text size="sm">
                                {{ $report->reason }}
                            </x-ui.text>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            @if ($report->status === 'pending')
                                <x-ui.badge variant="warning" size="sm">Pendente</x-ui.badge>
                            @elseif ($report->status === 'reviewed')
                                <x-ui.badge variant="info" size="sm">Revisada</x-ui.badge>
                            @else
                                <x-ui.badge variant="success" size="sm">Resolvida</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <x-ui.text size="sm" variant="muted">
                                {{ $report->created_at->format('d/m/Y H:i') }}
                            </x-ui.text>
                        </td>
                        <td class="px-4 py-4 text-sm font-medium whitespace-nowrap">
                            <button type="button" wire:click="view({{ $report->id }})" title="Ver detalhes"
                                class="inline-flex items-center justify-center rounded-lg p-2 transition-colors hover:bg-[var(--bg-hover)]"
                                style="color: var(--text-secondary);">
                                <x-ui.icon name="eye" class="w-5 h-5" />
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-0">
                            <x-ui.empty-state title="Nenhuma denúncia encontrada"
                                description="Não há denúncias para os filtros selecionados." />
                        </td>
                    </tr>
                @endforelse
            </x-ui.data-table>
        </div>

        <!-- Paginação -->
        <div class="px-5 py-4" style="border-top: 1px solid var(--border-color);">
            <x-ui.pagination :paginator="$reports" />
        </div>
    </x-ui.card>

    {{-- View / Moderation Modal --}}
    @if ($showViewModal && $selectedReport)
        <div class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm"
            style="background-color: rgba(0, 0, 0, 0.5);" wire:click="closeViewModal">
            <div class="w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto rounded-2xl shadow-2xl"
                style="background-color: var(--bg-surface); border: 1px solid var(--border-color);" wire:click.stop>
                <div class="flex items-center justify-between px-6 py-4"
                    style="border-bottom: 1px solid var(--border-color);">
                    <x-ui.heading size="md">Detalhes da Denúncia</x-ui.heading>
                    <button type="button" wire:click="closeViewModal" title="Fechar"
                        class="inline-flex items-center justify-center rounded-lg p-2 transition-colors hover:bg-[var(--bg-hover)]"
                        style="color: var(--text-secondary);">
                        <x-ui.icon name="x-mark" class="w-5 h-5" />
                    </button>
                </div>

                <div class="px-6 py-5 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-ui.text size="sm" variant="muted">Denunciante</x-ui.text>
                            <div class="flex items-center gap-2 mt-1">
                                <x-ui.avatar size="sm" :name="$selectedReport->reporter?->name" />
                                <x-ui.text variant="strong">
                                    {{ $selectedReport->reporter?->name ?? 'Usuário removido' }}
                                </x-ui.text>
                            </div>
                        </div>
                        <div>
                            <x-ui.text size="sm" variant="muted">Status</x-ui.text>
                            <div class="mt-1">
                                @if ($selectedReport->status === 'pending')
                                    <x-ui.badge variant="warning" size="sm">Pendente</x-ui.badge>
                                @elseif ($selectedReport->status === 'reviewed')
                                    <x-ui.badge variant="info" size="sm">Revisada</x-ui.badge>
                                @else
                                    <x-ui.badge variant="success" size="sm">Resolvida</x-ui.badge>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div>
                        <x-ui.text size="sm" variant="muted">Denunciado</x-ui.text>
                        <x-ui.text variant="strong">
                            {{ $this->reportedContentLabel($selectedReport) }}
                        </x-ui.text>
                    </div>

                    <div>
                        <x-ui.text size="sm" variant="muted">Motivo</x-ui.text>
                        <x-ui.text variant="strong">{{ $selectedReport->reason }}</x-ui.text>
                    </div>

                    @if ($selectedReport->details)
                        <div>
                            <x-ui.text size="sm" variant="muted">Detalhes</x-ui.text>
                            <x-ui.text class="whitespace-pre-line">{{ $selectedReport->details }}</x-ui.text>
                        </div>
                    @endif

                    <div>
                        <x-ui.text size="sm" variant="muted">Data</x-ui.text>
                        <x-ui.text variant="strong">
                            {{ $selectedReport->created_at->format('d/m/Y H:i') }}
                        </x-ui.text>
                    </div>

                    {{-- Conteúdo denunciado --}}
                    <x-ui.alert variant="warning" title="Conteúdo denunciado">
                        @if ($selectedReport->reportable_type && $selectedReport->reportable)
                            @php $reportableType = class_basename($selectedReport->reportable_type); @endphp
                            @if ($reportableType === 'Post')
                                <p class="font-medium" style="color: var(--text-primary);">
                                    {{ $selectedReport->reportable->title }}
                                </p>
                                <p class="mt-1 whitespace-pre-line">
                                    {{ strip_tags((string) $selectedReport->reportable->content) }}
                                </p>
                            @elseif ($reportableType === 'Comment')
                                <p class="whitespace-pre-line">
                                    {{ $selectedReport->reportable->body }}
                                </p>
                            @else
                                <p>{{ $reportableType }}</p>
                            @endif
                        @elseif ($selectedReport->reportable_type)
                            <p class="italic">Este conteúdo já foi excluído.</p>
                        @elseif ($selectedReport->reportedMessage)
                            <p class="whitespace-pre-line">{{ $selectedReport->reportedMessage->content }}</p>
                        @elseif ($selectedReport->reportedUser)
                            <p class="font-medium" style="color: var(--text-primary);">
                                {{ $selectedReport->reportedUser->name }}
                            </p>
                            <p class="mt-1">{{ $selectedReport->reportedUser->email }}</p>
                        @else
                            <p class="italic">Nenhum conteúdo associado.</p>
                        @endif
                    </x-ui.alert>
                </div>

                <div class="flex flex-wrap justify-end gap-3 px-6 py-4"
                    style="border-top: 1px solid var(--border-color);">
                    @if ($selectedReport->reportedUser)
                        <button type="button" wire:click="banReportedUser({{ $selectedReport->id }})"
                            wire:confirm="Tem certeza que deseja banir este usuário?"
                            class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold transition-all hover:opacity-80 focus:outline-none focus:ring-2 focus:ring-red-500/50"
                            style="background-color: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3);">
                            Banir usuário
                        </button>
                    @endif

                    @if ($selectedReport->reportable)
                        <button type="button" wire:click="deleteReportable({{ $selectedReport->id }})"
                            wire:confirm="Tem certeza que deseja excluir o conteúdo denunciado?"
                            class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold transition-all hover:opacity-80 focus:outline-none focus:ring-2 focus:ring-red-500/50"
                            style="background-color: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3);">
                            Excluir conteúdo
                        </button>
                    @endif

                    @if ($selectedReport->status !== 'resolved')
                        <button type="button" wire:click="markResolved({{ $selectedReport->id }})"
                            class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-500/20 transition-all hover:from-emerald-400 hover:to-teal-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50">
                            Marcar como resolvida
                        </button>
                    @endif

                    <button type="button" wire:click="closeViewModal"
                        class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold transition-all hover:opacity-80 focus:outline-none focus:ring-2 focus:ring-emerald-500/30"
                        style="background-color: var(--bg-elevated); color: var(--text-primary); border: 1px solid var(--border-color);">
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
