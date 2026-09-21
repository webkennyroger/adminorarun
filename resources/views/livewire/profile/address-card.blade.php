<div>
    <div class="p-5 border border-zinc-200 rounded-2xl dark:border-zinc-800 lg:p-6">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">

            <div>
                <p class="mb-2 text-xs leading-normal text-zinc-500 dark:text-zinc-400">Endereço</p>
                <p class="text-sm font-medium text-zinc-800 dark:text-white/90">{{ $user->profile?->address ?? '-' }}
                </p>
            </div>

            <div>
                <p class="mb-2 text-xs leading-normal text-zinc-500 dark:text-zinc-400">Cidade</p>
                <p class="text-sm font-medium text-zinc-800 dark:text-white/90">{{ $user->profile?->city ?? '-' }}</p>
            </div>

            <div>
                <p class="mb-2 text-xs leading-normal text-zinc-500 dark:text-zinc-400">Estado</p>
                <p class="text-sm font-medium text-zinc-800 dark:text-white/90">{{ $user->profile?->state ?? '-' }}</p>
            </div>

            <div>
                <p class="mb-2 text-xs leading-normal text-zinc-500 dark:text-zinc-400">Código postal</p>
                <p class="text-sm font-medium text-zinc-800 dark:text-white/90">{{ $user->profile?->zip_code ?? '-' }}
                </p>
            </div>




            <button @click="$dispatch('open-modal', 'address')"
                class="flex w-full items-center justify-center gap-2 rounded-full border border-zinc-300 bg-white px-4 py-3 text-sm font-medium text-zinc-700 shadow-theme-xs hover:bg-zinc-50 hover:text-zinc-800 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-white/[0.03] dark:hover:text-zinc-200 lg:inline-flex lg:w-auto">
                <svg class="fill-current" width="18" height="18" viewBox="0 0 18 18" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M15.0911 2.78206C14.2125 1.90338 12.7878 1.90338 11.9092 2.78206L4.57524 10.116C4.26682 10.4244 4.0547 10.8158 3.96468 11.2426L3.31231 14.3352C3.25997 14.5833 3.33653 14.841 3.51583 15.0203C3.69512 15.1996 3.95286 15.2761 4.20096 15.2238L7.29355 14.5714C7.72031 14.4814 8.11172 14.2693 8.42013 13.9609L15.7541 6.62695C16.6327 5.74827 16.6327 4.32365 15.7541 3.44497L15.0911 2.78206ZM12.9698 3.84272C13.2627 3.54982 13.7376 3.54982 14.0305 3.84272L14.6934 4.50563C14.9863 4.79852 14.9863 5.2734 14.6934 5.56629L14.044 6.21573L12.3204 4.49215L12.9698 3.84272ZM11.2597 5.55281L5.6359 11.1766C5.53309 11.2794 5.46238 11.4099 5.43238 11.5522L5.01758 13.5185L6.98394 13.1037C7.1262 13.0737 7.25666 13.003 7.35947 12.9002L12.9833 7.27639L11.2597 5.55281Z"
                        fill="" />
                </svg>
                Editar
            </button>
        </div>
    </div>
    <x-ui.modal name="address" maxWidth="700px">
        <div>
            <h4 class="mb-2 text-2xl font-semibold text-zinc-800 dark:text-white/90">
                Editar Endereço
            </h4>
            <p class="mb-6 text-sm text-zinc-500 dark:text-zinc-400 lg:mb-7">
                Atualize os detalhes do seu endereço.
            </p>
            <form wire:submit="saveAddress" class="flex flex-col">
                <div class="px-2 overflow-y-auto custom-scrollbar">
                    <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-400">
                                Endereço
                            </label>
                            <input type="text" wire:model="address"
                                class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-zinc-300 bg-transparent bg-none px-4 py-2.5 text-sm text-zinc-800 shadow-theme-xs placeholder:text-zinc-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-400">
                                Cidade
                            </label>
                            <input type="text" wire:model="city"
                                class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-zinc-300 bg-transparent bg-none px-4 py-2.5 text-sm text-zinc-800 shadow-theme-xs placeholder:text-zinc-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-400">
                                Estado
                            </label>
                            <input type="text" wire:model="state"
                                class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-zinc-300 bg-transparent bg-none px-4 py-2.5 text-sm text-zinc-800 shadow-theme-xs placeholder:text-zinc-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-400">
                                Código Postal
                            </label>
                            <input type="text" wire:model.blur="zip_code"
                                class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-zinc-300 bg-transparent bg-none px-4 py-2.5 text-sm text-zinc-800 shadow-theme-xs placeholder:text-zinc-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3 mt-6 lg:justify-end">
                    <button @click="open = false" type="button"
                        class="flex w-full justify-center rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-white/[0.03] sm:w-auto">
                        Fechar
                    </button>
                    <button type="submit"
                        class="flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto">
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </x-ui.modal>
</div>