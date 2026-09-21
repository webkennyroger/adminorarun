

<div class="p-5 mb-6 border border-zinc-200 rounded-2xl dark:border-zinc-800 lg:p-6">
    <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h4 class="text-lg font-semibold text-zinc-800 dark:text-white/90 lg:mb-6">
                Redes Sociais
            </h4>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-7 2xl:gap-x-32">
                <div>
                    <p class="mb-2 text-xs leading-normal text-zinc-500 dark:text-zinc-400">Instagram</p>
                    @if ($user->profile?->instagram)
                        <a href="{{ str_starts_with($user->profile->instagram, 'http') ? $user->profile->instagram : 'https://www.instagram.com/' . $user->profile->instagram }}"
                            target="_blank" rel="noopener noreferrer"
                            class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline truncate block">
                            {{ $user->profile->instagram }}
                        </a>
                    @else
                        <p class="text-sm font-medium text-zinc-800 dark:text-white/90">-</p>
                    @endif
                </div>

                <div>
                    <p class="mb-2 text-xs leading-normal text-zinc-500 dark:text-zinc-400">Facebook</p>
                    @if ($user->profile?->facebook)
                        <a href="{{ str_starts_with($user->profile->facebook, 'http') ? $user->profile->facebook : 'https://www.facebook.com/' . $user->profile->facebook }}"
                            target="_blank" rel="noopener noreferrer"
                            class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline truncate block">
                            {{ $user->profile->facebook }}
                        </a>
                    @else
                        <p class="text-sm font-medium text-zinc-800 dark:text-white/90">-</p>
                    @endif
                </div>

                <div>
                    <p class="mb-2 text-xs leading-normal text-zinc-500 dark:text-zinc-400">X (Twitter)</p>
                    @if ($user->profile?->x)
                        <a href="{{ str_starts_with($user->profile->x, 'http') ? $user->profile->x : 'https://x.com/' . $user->profile->x }}"
                            target="_blank" rel="noopener noreferrer"
                            class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline truncate block">
                            {{ $user->profile->x }}
                        </a>
                    @else
                        <p class="text-sm font-medium text-zinc-800 dark:text-white/90">-</p>
                    @endif
                </div>

                <div>
                    <p class="mb-2 text-xs leading-normal text-zinc-500 dark:text-zinc-400">Youtube</p>
                    @if ($user->profile?->youtube)
                        <a href="{{ str_starts_with($user->profile->youtube, 'http') ? $user->profile->youtube : 'https://www.youtube.com/' . $user->profile->youtube }}"
                            target="_blank" rel="noopener noreferrer"
                            class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline truncate block">
                            {{ $user->profile->youtube }}
                        </a>
                    @else
                        <p class="text-sm font-medium text-zinc-800 dark:text-white/90">-</p>
                    @endif
                </div>

                <div>
                    <p class="mb-2 text-xs leading-normal text-zinc-500 dark:text-zinc-400">TikTok</p>
                    @if ($user->profile?->tiktok)
                        <a href="{{ str_starts_with($user->profile->tiktok, 'http') ? $user->profile->tiktok : 'https://www.tiktok.com/@' . ltrim($user->profile->tiktok, '@') }}"
                            target="_blank" rel="noopener noreferrer"
                            class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline truncate block">
                            {{ $user->profile->tiktok }}
                        </a>
                    @else
                        <p class="text-sm font-medium text-zinc-800 dark:text-white/90">-</p>
                    @endif
                </div>

                <div>
                    <p class="mb-2 text-xs leading-normal text-zinc-500 dark:text-zinc-400">Mere</p>
                    @if ($user->profile?->mere)
                        <a href="{{ $user->profile->mere }}" target="_blank" rel="noopener noreferrer"
                            class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline truncate block">
                            {{ $user->profile->mere }}
                        </a>
                    @else
                        <p class="text-sm font-medium text-zinc-800 dark:text-white/90">-</p>
                    @endif
                </div>
            </div>
        </div>

        <button class="edit-button" @click="$dispatch('open-modal', 'social-media')">
            <svg class="fill-current" width="18" height="18" viewBox="0 0 18 18" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M15.0911 2.78206C14.2125 1.90338 12.7878 1.90338 11.9092 2.78206L4.57524 10.116C4.26682 10.4244 4.0547 10.8158 3.96468 11.2426L3.31231 14.3352C3.25997 14.5833 3.33653 14.841 3.51583 15.0203C3.69512 15.1996 3.95286 15.2761 4.20096 15.2238L7.29355 14.5714C7.72031 14.4814 8.11172 14.2693 8.42013 13.9609L15.7541 6.62695C16.6327 5.74827 16.6327 4.32365 15.7541 3.44497L15.0911 2.78206ZM12.9698 3.84272C13.2627 3.54982 13.7376 3.54982 14.0305 3.84272L14.6934 4.50563C14.9863 4.79852 14.9863 5.2734 14.6934 5.56629L14.044 6.21573L12.3204 4.49215L12.9698 3.84272ZM11.2597 5.55281L5.6359 11.1766C5.53309 11.2794 5.46238 11.4099 5.43238 11.5522L5.01758 13.5185L6.98394 13.1037C7.1262 13.0737 7.25666 13.003 7.35947 12.9002L12.9833 7.27639L11.2597 5.55281Z"
                    fill="" />
            </svg>
            Editar
        </button>
    </div>

    <!-- Social Media Modal -->
    <x-ui.modal name="social-media" maxWidth="700px">
        <div>
            <h4 class="mb-2 text-2xl font-semibold text-zinc-800 dark:text-white/90">
                Editar Redes Sociais
            </h4>
            <p class="mb-6 text-sm text-zinc-500 dark:text-zinc-400 lg:mb-7">
                Atualize seus links de redes sociais
            </p>
            <form wire:submit="updateSocialMedia" class="flex flex-col">
                <div class="custom-scrollbar overflow-y-auto p-2">
                    <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                        <div class="col-span-2 lg:col-span-1">
                            <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-400">
                                Instagram
                            </label>
                            <input type="text" wire:model="instagram" placeholder="Ex: seu.usuario"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-zinc-300 bg-transparent bg-none px-4 py-2.5 text-sm text-zinc-800 shadow-theme-xs placeholder:text-zinc-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            @error('instagram') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-span-2 lg:col-span-1">
                            <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-400">
                                Facebook
                            </label>
                            <input type="text" wire:model="facebook" placeholder="Ex: seu.usuario"
                                class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-zinc-300 bg-transparent bg-none px-4 py-2.5 text-sm text-zinc-800 shadow-theme-xs placeholder:text-zinc-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            @error('facebook') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-span-2 lg:col-span-1">
                            <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-400">
                                X (Twitter)
                            </label>
                            <input type="text" wire:model="x" placeholder="Ex: seu_usuario"
                                class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-zinc-300 bg-transparent bg-none px-4 py-2.5 text-sm text-zinc-800 shadow-theme-xs placeholder:text-zinc-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            @error('x') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-span-2 lg:col-span-1">
                            <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-400">
                                Youtube
                            </label>
                            <input type="text" wire:model="youtube" placeholder="Ex: seu_canal"
                                class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-zinc-300 bg-transparent bg-none px-4 py-2.5 text-sm text-zinc-800 shadow-theme-xs placeholder:text-zinc-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            @error('youtube') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-span-2 lg:col-span-1">
                            <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-400">
                                TikTok
                            </label>
                            <input type="text" wire:model="tiktok" placeholder="Ex: @seu_usuario"
                                class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-zinc-300 bg-transparent bg-none px-4 py-2.5 text-sm text-zinc-800 shadow-theme-xs placeholder:text-zinc-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            @error('tiktok') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-span-2 lg:col-span-1">
                            <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-400">
                                Mere
                            </label>
                            <input type="text" wire:model="mere" placeholder="URL ou usuário"
                                class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-zinc-300 bg-transparent bg-none px-4 py-2.5 text-sm text-zinc-800 shadow-theme-xs placeholder:text-zinc-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            @error('mere') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3 px-2 mt-6 lg:justify-end">
                    <button @click="open = false" type="button"
                        class="flex w-full justify-center rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-white/[0.03] sm:w-auto">
                        Fechar
                    </button>
                    <button type="submit"
                        class="flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto">
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </x-ui.modal>
</div>