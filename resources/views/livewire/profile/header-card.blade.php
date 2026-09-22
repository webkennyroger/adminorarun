<div>
    <section class="w-full">

        <div class="mb-6 rounded-2xl border border-zinc-200 p-5 lg:p-6 dark:border-zinc-800">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
                <div class="flex w-full flex-col items-center gap-6 xl:flex-row">
                    <div class="h-20 w-20 rounded-full overflow-hidden bg-zinc-100">
                        @if ($image)
                            <img src="{{ $image->temporaryUrl() }}" class="h-full w-full object-cover" alt="Preview">
                        @else
                            <img src="{{ $user->image_url }}" class="h-full w-full object-cover" alt="Avatar">
                        @endif
                    </div>
                    <div class="order-3 xl:order-2">
                        <h4
                            class="mb-2 text-center text-lg font-semibold text-zinc-800 xl:text-left dark:text-white/90">
                            {{ $name }}
                        </h4>
                        <div class="flex flex-col items-center gap-1 text-center xl:flex-row xl:gap-3 xl:text-left">
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $phone }}
                            </p>
                            <div class="hidden h-3.5 w-px bg-zinc-300 xl:block dark:bg-zinc-700"></div>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $email }}
                            </p>
                        </div>
                    </div>
                    <div class="order-2 flex grow items-center gap-4 xl:order-3 xl:justify-end">
                        <div class="flex items-center gap-2">
                            @if($user->profile?->facebook)
                                <a href="{{ str_starts_with($user->profile->facebook, 'http') ? $user->profile->facebook : 'https://www.facebook.com/' . $user->profile->facebook }}"
                                    target="_blank"
                                    class="flex h-10 w-10 items-center justify-center rounded-full border border-zinc-200 text-zinc-500 transition-colors hover:bg-zinc-50 hover:text-zinc-800 dark:border-zinc-700 dark:text-zinc-400 dark:hover:bg-white/5 dark:hover:text-zinc-200">
                                    <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" />
                                    </svg>
                                </a>
                            @endif
                            @if($user->profile?->x)
                                <a href="{{ str_starts_with($user->profile->x, 'http') ? $user->profile->x : 'https://x.com/' . $user->profile->x }}"
                                    target="_blank"
                                    class="flex h-10 w-10 items-center justify-center rounded-full border border-zinc-200 text-zinc-500 transition-colors hover:bg-zinc-50 hover:text-zinc-800 dark:border-zinc-700 dark:text-zinc-400 dark:hover:bg-white/5 dark:hover:text-zinc-200">
                                    <svg class="h-4 w-4 fill-current" viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                                    </svg>
                                </a>
                            @endif
                            @if($user->profile?->instagram)
                                <a href="{{ str_starts_with($user->profile->instagram, 'http') ? $user->profile->instagram : 'https://www.instagram.com/' . $user->profile->instagram }}"
                                    target="_blank"
                                    class="flex h-10 w-10 items-center justify-center rounded-full border border-zinc-200 text-zinc-500 transition-colors hover:bg-zinc-50 hover:text-zinc-800 dark:border-zinc-700 dark:text-zinc-400 dark:hover:bg-white/5 dark:hover:text-zinc-200">
                                    <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <rect x="2" y="2" width="20" height="20" rx="5" ry="5" stroke="currentColor"
                                            stroke-width="2" fill="none" />
                                        <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" stroke="currentColor"
                                            stroke-width="2" fill="none" />
                                        <circle cx="17.5" cy="6.5" r="1.5" fill="currentColor" />
                                    </svg>
                                </a>
                            @endif
                            @if($user->profile?->tiktok)
                                <a href="{{ str_starts_with($user->profile->tiktok, 'http') ? $user->profile->tiktok : 'https://www.tiktok.com/@' . ltrim($user->profile->tiktok, '@') }}"
                                    target="_blank"
                                    class="flex h-10 w-10 items-center justify-center rounded-full border border-zinc-200 text-zinc-500 transition-colors hover:bg-zinc-50 hover:text-zinc-800 dark:border-zinc-700 dark:text-zinc-400 dark:hover:bg-white/5 dark:hover:text-zinc-200">
                                    <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.65-1.58-1.02v4.95c.06 4.91-4.02 8.94-8.91 8.99-4.31.05-8.15-3.07-8.95-7.28C-2.02 8.16 2.05 2.76 8.35 2.53v4.03h-.16c-1.13.08-2.19.61-2.95 1.5-.78.96-1.07 2.25-.8 3.46.26 1.18 1.01 2.22 2.06 2.8 1.11.61 2.47.64 3.7.13 1.12-.49 1.95-1.51 2.24-2.7.27-1.1.09-2.27-.08-3.35-.02-.85-.02-1.71-.02-2.56V.03h.19z" />
                                    </svg>
                                </a>
                            @endif
                            @if($user->profile?->mere)
                                <a href="{{ $user->profile->mere }}" target="_blank"
                                    class="flex h-10 w-10 items-center justify-center rounded-full border border-zinc-200 text-zinc-500 transition-colors hover:bg-zinc-50 hover:text-zinc-800 dark:border-zinc-700 dark:text-zinc-400 dark:hover:bg-white/5 dark:hover:text-zinc-200">
                                    <svg version="1.1" id="Camada_1" xmlns="http://www.w3.org/2000/svg"
                                        xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 32 32"
                                        class="h-5 w-5" xml:space="preserve">
                                        <g>
                                            <polygon fill="#FFFFFF" points="25.9,20.8 21.4,25.1 23.2,24.9 27.7,20.6" />
                                            <path fill="#465FFF"
                                                d="M14,22.4l3.4-1.5l6.8-14.7h-5.9l-4.6,3.7l-0.1,1.5l-1.3-5.2h-8l0.9,2.6H8L4.8,9.5L0.5,18l5.3-1.2L9,10.4
                                                        l0.1-1.2l1.6,6.2l8.5-6.8l-2,2.1l-5.4,11.6h0c-0.4,1-0.5,1.8-0.4,2.5c0.2,0.8,0.6,1.1,1.4,1.1l8-0.8l4.5-4.1L14,22.4z" />
                                            <polygon fill="#FFFFFF" points="28.5,20.6 24,24.8 25.2,24.7 29.8,20.4" />
                                            <polygon fill="#FFFFFF" points="26.1,24.6 26.9,24.6 31.5,20.3 30.7,20.3" />
                                        </g>
                                    </svg>
                                </a>
                            @endif
                        </div>

                        <button @click="$dispatch('open-modal', 'profile-info')"
                            class="flex items-center gap-2 rounded-full border border-zinc-300 bg-white px-5 py-2.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-white/5">
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
            </div>
        </div>
    </section>
</div>