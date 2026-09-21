<div>
    <x-common.page-breadcrumb title="Denúncias" />

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
                            ['value' => -1, 'label' => 'Todos'],
                        ]" />
                    </div>
                </div>
                <span class="text-zinc-500 dark:text-zinc-400">entradas</span>
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
                            <svg class="fill-zinc-500 dark:fill-zinc-400" width="20" height="20" viewBox="0 0 20 20"
                                fill="none">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M3.04175 9.37363C3.04175 5.87693 5.87711 3.04199 9.37508 3.04199C12.8731 3.04199 15.7084 5.87693 15.7084 9.37363C15.7084 12.8703 12.8731 15.7053 9.37508 15.7053C5.87711 15.7053 3.04175 12.8703 3.04175 9.37363ZM9.37508 1.54199C5.04902 1.54199 1.54175 5.04817 1.54175 9.37363C1.54175 13.6991 5.04902 17.2053 9.37508 17.2053C11.2674 17.2053 13.003 16.5344 14.357 15.4176L17.177 18.238C17.4699 18.5309 17.9448 18.5309 18.2377 18.238C18.5306 17.9451 18.5306 17.4703 18.2377 17.1774L15.418 14.3573C16.5365 13.0033 17.2084 11.2669 17.2084 9.37363C17.2084 5.04817 13.7011 1.54199 9.37508 1.54199Z"
                                    fill="" />
                            </svg>
                        </span>
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Pesquisar..."
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-zinc-200 bg-transparent py-2.5 pl-12 pr-4 text-sm text-zinc-800 shadow-theme-xs placeholder:text-zinc-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-zinc-800 dark:bg-white/3 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800 xl:w-[300px]" />
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
                            Denunciante
                        </th>
                        <th scope="col"
                            class="px-4 py-3 font-normal text-zinc-500 text-start text-theme-sm dark:text-zinc-400">
                            Denunciado
                        </th>
                        <th scope="col"
                            class="px-4 py-3 font-normal text-zinc-500 text-start text-theme-sm dark:text-zinc-400">
                            Motivo
                        </th>
                        <th scope="col"
                            class="px-4 py-3 font-normal text-zinc-500 text-start text-theme-sm dark:text-zinc-400">
                            Status
                        </th>
                        <th scope="col"
                            class="px-4 py-3 font-normal text-zinc-500 text-start text-theme-sm dark:text-zinc-400">
                            Data
                        </th>
                        <th scope="col"
                            class="px-4 py-3 font-normal text-zinc-500 text-start text-theme-sm dark:text-zinc-400">
                            Ações
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($reports as $report)
                        <tr wire:key="{{ $report->id }}"
                            class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors duration-200">
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="text-sm text-zinc-800 dark:text-zinc-100">
                                    {{ $report->reporter?->name ?? 'Usuário removido' }}
                                </div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $this->reportedContentLabel($report) }}
                                </div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $report->reason }}
                                </div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                @if ($report->status === 'pending')
                                    <span
                                        class="inline-flex rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                                        Pendente
                                    </span>
                                @elseif ($report->status === 'reviewed')
                                    <span
                                        class="inline-flex rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                        Revisada
                                    </span>
                                @else
                                    <span
                                        class="inline-flex rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                        Resolvida
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $report->created_at->format('d/m/Y H:i') }}
                                </div>
                            </td>
                            <td class="px-4 py-4 text-sm font-medium whitespace-nowrap">
                                <button wire:click="view({{ $report->id }})"
                                    class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium transition-all disabled:pointer-events-none disabled:opacity-50 shrink-0 outline-none cursor-pointer hover:bg-accent hover:text-accent-foreground dark:hover:bg-accent/50 size-9 rounded-[50%] text-blue-500 bg-primary/10">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="lucide lucide-eye w-5 h-5">
                                        <path
                                            d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0">
                                        </path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-zinc-500">
                                Nenhuma denúncia encontrada
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <div
            class="flex items-center flex-col sm:flex-row justify-between border-t border-zinc-200 px-5 py-4 dark:border-zinc-800">
            {{ $reports->links('components.pagination.custom') }}
        </div>
    </div>

    {{-- View / Moderation Modal --}}
    @if ($showViewModal && $selectedReport)
        <div class="fixed inset-0 bg-black/50 dark:bg-black/70 flex items-center justify-center z-50"
            wire:click="closeViewModal">
            <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto"
                wire:click.stop>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Detalhes da Denúncia</h3>
                    <button wire:click="closeViewModal"
                        class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Denunciante</label>
                            <p class="text-zinc-900 dark:text-zinc-100">
                                {{ $selectedReport->reporter?->name ?? 'Usuário removido' }}
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Status</label>
                            <p class="text-zinc-900 dark:text-zinc-100">
                                @if ($selectedReport->status === 'pending')
                                    <span
                                        class="inline-flex rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                                        Pendente
                                    </span>
                                @elseif ($selectedReport->status === 'reviewed')
                                    <span
                                        class="inline-flex rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                        Revisada
                                    </span>
                                @else
                                    <span
                                        class="inline-flex rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                        Resolvida
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Denunciado</label>
                        <p class="text-zinc-900 dark:text-zinc-100">
                            {{ $this->reportedContentLabel($selectedReport) }}
                        </p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Motivo</label>
                        <p class="text-zinc-900 dark:text-zinc-100">{{ $selectedReport->reason }}</p>
                    </div>

                    @if ($selectedReport->details)
                        <div>
                            <label class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Detalhes</label>
                            <p class="text-zinc-900 dark:text-zinc-100 whitespace-pre-line">{{ $selectedReport->details }}</p>
                        </div>
                    @endif

                    <div>
                        <label class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Data</label>
                        <p class="text-zinc-900 dark:text-zinc-100">
                            {{ $selectedReport->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>

                    {{-- Conteúdo denunciado --}}
                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-white/5">
                        <label class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Conteúdo denunciado</label>

                        @if ($selectedReport->reportable_type && $selectedReport->reportable)
                            @php $reportableType = class_basename($selectedReport->reportable_type); @endphp
                            @if ($reportableType === 'Post')
                                <p class="mt-1 font-medium text-zinc-900 dark:text-zinc-100">
                                    {{ $selectedReport->reportable->title }}
                                </p>
                                <p class="mt-1 text-zinc-700 dark:text-zinc-300 whitespace-pre-line">
                                    {{ strip_tags((string) $selectedReport->reportable->content) }}
                                </p>
                            @elseif ($reportableType === 'Comment')
                                <p class="mt-1 text-zinc-700 dark:text-zinc-300 whitespace-pre-line">
                                    {{ $selectedReport->reportable->body }}
                                </p>
                            @else
                                <p class="mt-1 text-zinc-700 dark:text-zinc-300">{{ $reportableType }}</p>
                            @endif
                        @elseif ($selectedReport->reportable_type)
                            <p class="mt-1 text-zinc-500 dark:text-zinc-400 italic">
                                Este conteúdo já foi excluído.
                            </p>
                        @elseif ($selectedReport->reportedMessage)
                            <p class="mt-1 text-zinc-700 dark:text-zinc-300 whitespace-pre-line">
                                {{ $selectedReport->reportedMessage->content }}
                            </p>
                        @elseif ($selectedReport->reportedUser)
                            <p class="mt-1 font-medium text-zinc-900 dark:text-zinc-100">
                                {{ $selectedReport->reportedUser->name }}
                            </p>
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $selectedReport->reportedUser->email }}
                            </p>
                        @else
                            <p class="mt-1 text-zinc-500 dark:text-zinc-400 italic">Nenhum conteúdo associado.</p>
                        @endif
                    </div>
                </div>

                <div class="flex flex-wrap justify-end gap-3 pt-6">
                    @if ($selectedReport->reportedUser)
                        <button wire:click="banReportedUser({{ $selectedReport->id }})"
                            wire:confirm="Tem certeza que deseja banir este usuário?"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                            Banir usuário
                        </button>
                    @endif

                    @if ($selectedReport->reportable)
                        <button wire:click="deleteReportable({{ $selectedReport->id }})"
                            wire:confirm="Tem certeza que deseja excluir o conteúdo denunciado?"
                            class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
                            Excluir conteúdo
                        </button>
                    @endif

                    @if ($selectedReport->status !== 'resolved')
                        <button wire:click="markResolved({{ $selectedReport->id }})"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            Marcar como resolvida
                        </button>
                    @endif

                    <button type="button" wire:click="closeViewModal"
                        class="px-4 py-2 bg-zinc-200 text-zinc-800 rounded-lg hover:bg-zinc-300 dark:bg-zinc-700 dark:text-zinc-200">
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
