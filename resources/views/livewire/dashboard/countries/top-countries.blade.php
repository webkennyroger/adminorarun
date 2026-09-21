<x-ui.card>
    <div class="flex min-w-0 items-center justify-between mb-4">
        <h2 class="truncate font-medium tracking-wide" style="color: var(--text-primary);">
            Principais países
        </h2>
        <a href="##"
            class="border-b border-dotted border-current pb-0.5 text-xs-plus font-medium text-emerald-500 outline-hidden transition-colors duration-300 hover:opacity-70">Ver
            todos
        </a>
    </div>
    <div>
        <p><span class="text-2xl" style="color: var(--text-primary);">{{ count($countries) }}</span></p>
        <p class="text-xs-plus" style="color: var(--text-secondary);">Países</p>
    </div>
    <div class="mt-5 max-h-[350px] overflow-y-auto custom-scrollbar pr-2">
        @if(count($countries) > 0)
            <div class="space-y-4">
                @foreach($countries as $country)
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex min-w-0 items-center gap-2">
                            <img class="size-6 rounded-full object-cover" alt="{{ $country['name'] }}"
                                src="/assets/images/countries/{{ $country['flag'] }}">
                            <a href="##" class="truncate transition-opacity hover:opacity-80 text-sm font-medium"
                                style="color: var(--text-secondary);">
                                {{ $country['name'] }}
                            </a>
                        </div>
                        <div class="flex items-center gap-2">
                            <p class="text-sm-plus" style="color: var(--text-primary);">
                                {{ number_format($country['users'], 0, ',', '.') }}
                            </p>

                            @if($country['trend'] === 'up')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon"
                                    class="size-4 text-emerald-500">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18">
                                    </path>
                                </svg>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon"
                                    class="size-4 text-red-500">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3">
                                    </path>
                                </svg>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <x-ui.empty-state title="Nenhum país encontrado"
                description="Ainda não há dados suficientes para exibir o ranking de países." />
        @endif
    </div>
</x-ui.card>
