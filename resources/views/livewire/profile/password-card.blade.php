

<section class="w-full">
    <div class="p-5 my-6 border border-zinc-200 rounded-2xl dark:border-zinc-800 lg:p-6">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h4 class="text-lg font-semibold text-zinc-800 dark:text-white/90 lg:mb-6">
                    Segurança
                </h4>

                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-7 2xl:gap-x-32">
                    <div>
                        <p class="mb-2 text-xs leading-normal text-zinc-500 dark:text-zinc-400">Senha</p>
                        <p class="text-sm font-medium text-zinc-800 dark:text-white/90">********</p>
                    </div>

                    <div>
                        <p class="mb-2 text-xs leading-normal text-zinc-500 dark:text-zinc-400">Autenticação de Dois
                            Fatores</p>
                        @if($twoFactorEnabled)
                            <span
                                class="inline-flex items-center px-2 py-1 text-xs font-medium text-green-700 bg-green-100 rounded-full dark:bg-green-900 dark:text-green-300">
                                Ativado
                            </span>
                        @else
                            <span
                                class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-700 bg-red-100 rounded-full dark:bg-red-900 dark:text-red-300">
                                Desativado
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex gap-2">
                <button class="edit-button" @click="$dispatch('open-modal', 'password')">
                    <svg class="fill-current" width="18" height="18" viewBox="0 0 18 18" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M15.0911 2.78206C14.2125 1.90338 12.7878 1.90338 11.9092 2.78206L4.57524 10.116C4.26682 10.4244 4.0547 10.8158 3.96468 11.2426L3.31231 14.3352C3.25997 14.5833 3.33653 14.841 3.51583 15.0203C3.69512 15.1996 3.95286 15.2761 4.20096 15.2238L7.29355 14.5714C7.72031 14.4814 8.11172 14.2693 8.42013 13.9609L15.7541 6.62695C16.6327 5.74827 16.6327 4.32365 15.7541 3.44497L15.0911 2.78206ZM12.9698 3.84272C13.2627 3.54982 13.7376 3.54982 14.0305 3.84272L14.6934 4.50563C14.9863 4.79852 14.9863 5.2734 14.6934 5.56629L14.044 6.21573L12.3204 4.49215L12.9698 3.84272ZM11.2597 5.55281L5.6359 11.1766C5.53309 11.2794 5.46238 11.4099 5.43238 11.5522L5.01758 13.5185L6.98394 13.1037C7.1262 13.0737 7.25666 13.003 7.35947 12.9002L12.9833 7.27639L11.2597 5.55281Z"
                            fill="" />
                    </svg>
                    Editar Senha
                </button>

                <button class="edit-button" @click="$dispatch('open-modal', 'twofactor')">
                    <svg class="fill-current" width="18" height="18" viewBox="0 0 18 18" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M9 1.5C9 1.5 4.5 3 4.5 6V9.75C4.5 12.9 6.75 15.75 9 16.5C11.25 15.75 13.5 12.9 13.5 9.75V6C13.5 3 9 1.5 9 1.5ZM9 9C9.825 9 10.5 8.325 10.5 7.5C10.5 6.675 9.825 6 9 6C8.175 6 7.5 6.675 7.5 7.5C7.5 8.325 8.175 9 9 9ZM6.75 11.25C6.75 10.125 8.625 9.75 9 9.75C9.375 9.75 11.25 10.125 11.25 11.25V12H6.75V11.25Z"
                            fill="" />
                    </svg>
                    2FA
                </button>
            </div>
        </div>
    </div>

    <x-ui.modal name="password" maxWidth="700px">
        <div>
            <h4 class="mb-2 text-2xl font-semibold text-zinc-800 dark:text-white/90">
                Update Password
            </h4>
            <p class="mb-6 text-sm text-zinc-500 dark:text-zinc-400 lg:mb-7">
                Ensure your account is using a long, random password to stay secure.
            </p>
            <form wire:submit="updatePassword" class="flex flex-col space-y-6">
                <div class="px-2 space-y-4">
                    <div>
                        <label for="current_password"
                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Current password') }}</label>
                        <input wire:model="current_password" id="current_password" type="password" required
                            autocomplete="current-password"
                            class="mt-1 block w-full rounded-md border-zinc-300 dark:border-zinc-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-900 dark:text-zinc-100 sm:text-sm">
                        @error('current_password') <span
                        class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="password"
                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('New password') }}</label>
                        <input wire:model="password" id="password" type="password" required autocomplete="new-password"
                            class="mt-1 block w-full rounded-md border-zinc-300 dark:border-zinc-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-900 dark:text-zinc-100 sm:text-sm">
                        @error('password') <span
                            class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation"
                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Confirm Password') }}</label>
                        <input wire:model="password_confirmation" id="password_confirmation" type="password" required
                            autocomplete="new-password"
                            class="mt-1 block w-full rounded-md border-zinc-300 dark:border-zinc-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-900 dark:text-zinc-100 sm:text-sm">
                        @error('password_confirmation') <span
                        class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex items-center gap-3 px-2 mt-6 lg:justify-end">
                    <button @click="open = false" type="button"
                        class="flex w-full justify-center rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-white/[0.03] sm:w-auto">
                        Close
                    </button>
                    <button type="submit"
                        class="flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </x-ui.modal>

    <!-- Two-Factor Authentication Modal -->
    <x-ui.modal name="twofactor" maxWidth="700px">
        <h4 class="mb-2 text-2xl font-semibold text-zinc-800 dark:text-white/90">
            Autenticação de Dois Fatores
        </h4>
        <p class="mb-6 text-sm text-zinc-500 dark:text-zinc-400 lg:mb-7">
            Gerencie suas configurações de autenticação de dois fatores
        </p>
        <livewire:profile.two-factor-card />
    </x-ui.modal>
</section>