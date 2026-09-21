

<section class="mt-10 space-y-6">
    <div class="relative mb-5">
        <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">{{ __('Delete account') }}</h2>
        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
            {{ __('Delete your account and all of its resources') }}
        </p>
    </div>

    <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'delete-user')"
        class="inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-800 transition ease-in-out duration-150"
        data-test="delete-user-button">
        {{ __('Delete account') }}
    </button>

    <x-ui.modal name="delete-user" maxWidth="md" :open="$errors->isNotEmpty()">
        <form method="POST" wire:submit="deleteUser" class="p-6">
            <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <div class="mt-6">
                <label for="password" class="sr-only">{{ __('Password') }}</label>
                <input wire:model="password" id="password" type="password"
                    class="mt-1 block w-3/4 rounded-md border-zinc-300 dark:border-zinc-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-900 dark:text-zinc-100 sm:text-sm"
                    placeholder="{{ __('Password') }}" />
                @error('password') <span class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div class="mt-6 flex justify-end">
                <button type="button" x-on:click="open = false"
                    class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-500 rounded-md font-semibold text-xs text-zinc-700 dark:text-zinc-300 uppercase tracking-widest shadow-sm hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-800 disabled:opacity-25 transition ease-in-out duration-150">
                    {{ __('Cancel') }}
                </button>

                <button type="submit"
                    class="ms-3 inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-800 transition ease-in-out duration-150"
                    data-test="confirm-delete-user-button">
                    {{ __('Delete account') }}
                </button>
            </div>
        </form>
    </x-ui.modal>
</section>