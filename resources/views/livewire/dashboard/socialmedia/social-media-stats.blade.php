<x-ui.card>
    <div class="flex flex-col lg:flex-row">
        <div class="flex min-w-0 flex-col lg:w-72 lg:shrink-0">
            <div class="flex items-center justify-between">
                <h3 class="truncate text-base font-medium tracking-wide lg:text-lg" style="color: var(--text-primary);">
                    Canais
                </h3>
                <button wire:click="refreshData" class="p-1 rounded-full transition-colors"
                    style="color: var(--text-secondary);" title="Atualizar dados">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                </button>
            </div>
            <p class="mt-3 grow text-sm" style="color: var(--text-secondary);">
                Análises de canais calculadas com base na sua atividade
            </p>
            <div class="mt-3 flex items-center space-x-2">
                <div class="avatar relative inline-flex shrink-0 h-7 w-7">
                    <div class="flex h-full w-full items-center justify-center rounded bg-emerald-500/10 text-emerald-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18">
                            </path>
                        </svg>
                    </div>
                </div>
                <p class="text-base font-medium" style="color: var(--text-primary);">
                    {{ number_format($overallGrowth, 1) }}%
                </p>
            </div>
        </div>

        <div class="hide-scrollbar flex-1 mt-5 flex space-x-4 overflow-x-auto pb-2 lg:mt-0">
            @forelse($stats as $stat)
                <div class="relative break-words flex w-36 shrink-0 flex-col items-center group">
                    <div class="z-10 flex h-12 w-12 items-center justify-center rounded-full shadow-sm ring-4 {{ $stat['color_bg'] }} {{ $stat['color_text'] }}"
                        style="--tw-ring-color: var(--bg-card);">
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="{{ $stat['path'] }}" />
                        </svg>
                    </div>
                    <div class="relative w-full -mt-6 flex flex-col rounded-2xl px-3 py-5 text-center shadow-sm pt-9 transition-transform hover:-translate-y-1"
                        style="background-color: var(--bg-elevated);">
                        <p class="mt-1 text-base font-medium" style="color: var(--text-primary);">{{ $stat['name'] }}</p>
                        <a href="#" class="mt-1 text-xs font-medium transition-colors hover:text-emerald-500"
                            style="color: var(--text-muted);">{{ $stat['handle'] }}</a>
                        <div class="mt-4 flex justify-center items-baseline gap-0.5" style="color: var(--text-primary);">
                            <p class="text-3xl font-semibold tracking-tight">+{{ $stat['growth'] }}</p>
                            <p class="text-sm font-medium" style="color: var(--text-secondary);">%</p>
                        </div>
                    </div>
                </div>
            @empty
                <x-ui.empty-state title="Nenhum canal conectado"
                    description="Conecte uma rede social para ver as estatísticas de crescimento aqui." />
            @endforelse
        </div>
    </div>
</x-ui.card>
