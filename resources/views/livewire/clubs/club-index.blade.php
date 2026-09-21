<div>
<x-common.page-breadcrumb title="Clubes" />

<div class="rounded-2xl border border-zinc-200 bg-white pt-4 dark:border-zinc-800 dark:bg-white/[0.03]">
    <!-- Header -->
    <div class="flex flex-col gap-3 px-4 py-4">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <span class="text-zinc-500 dark:text-zinc-400">Mostrar</span>
                <div class="relative z-20 bg-transparent">
                    <div class="relative z-20 w-24">
                        <x-form.multiple-select wire:model.live="perPage" :multiple="false" :options="[
            ['value' => 5, 'label' => '5'],
            ['value' => 10, 'label' => '10'],
            ['value' => 25, 'label' => '25'],
            ['value' => 50, 'label' => '50'],
            ['value' => 100, 'label' => '100'],
            ['value' => -1, 'label' => 'Todos']
        ]" />
                    </div>
                </div>
                <span class="text-zinc-500 dark:text-zinc-400">entradas</span>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
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
                        <input wire:model.live.debounce.300ms="search" type="text"
                            placeholder="Pesquisar por nome ou cidade..."
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-zinc-200 bg-transparent py-2.5 pl-12 pr-4 text-sm text-zinc-800 shadow-theme-xs placeholder:text-zinc-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-zinc-800 dark:bg-white/3 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800 xl:w-[350px]" />
                    </div>
                </form>

                @if (is_array($selected) && count($selected) > 0)
                    <button wire:click="deleteSelected"
                        class="flex w-full items-center justify-center gap-2 rounded-lg border border-red-300 bg-red-200 px-4 py-[11px] text-sm font-medium text-red-700 shadow-theme-xs dark:border-red-700 dark:bg-red-800 dark:text-zinc-400 sm:w-auto">
                        Deletar Selecionados ({{ count($selected) }})
                    </button>
                @endif

                <button wire:click="create"
                    class="bg-brand-600 hover:bg-brand-700 text-white px-4 py-2 rounded-lg transition">
                    Novo Clube
                </button>
            </div>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="w-full sm:w-56">
                <select wire:model.live="filterCategory"
                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-zinc-200 bg-transparent px-3 text-sm text-zinc-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-zinc-800 dark:text-white/90">
                    <option value="">Todas as categorias</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full sm:w-56">
                <select wire:model.live="filterPublic"
                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-zinc-200 bg-transparent px-3 text-sm text-zinc-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-zinc-800 dark:text-white/90">
                    <option value="">Todos (público/privado)</option>
                    <option value="1">Somente públicos</option>
                    <option value="0">Somente privados</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="max-w-full px-5 overflow-x-auto">
        <table class="min-w-full">
            <thead class="border-t border-zinc-100 border-y bg-zinc-50 dark:bg-zinc-900">
                <tr class="border-zinc-200 border-y dark:border-zinc-700">
                    <th scope="col" class="px-4 py-3 text-start sm:px-4 text-theme-sm">
                        <div wire:click="toggleSelectAll" class="cursor-pointer inline-flex">
                            <div class="flex h-5 w-5 items-center justify-center rounded-md border-[1.25px] transition-all duration-200"
                                :class="@js($selectAll) ? 'border-brand-500 bg-brand-500 text-white' : 'bg-transparent border-zinc-300 dark:border-zinc-700 text-transparent'">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                        </div>
                    </th>
                    <th scope="col"
                        class="px-4 py-3 font-normal text-zinc-500 text-start text-theme-sm dark:text-zinc-400">
                        Clube
                    </th>
                    <th scope="col"
                        class="px-4 py-3 font-normal text-zinc-500 text-start text-theme-sm dark:text-zinc-400">
                        Local
                    </th>
                    <th scope="col"
                        class="px-4 py-3 font-normal text-zinc-500 text-start text-theme-sm dark:text-zinc-400">
                        Categoria
                    </th>
                    <th scope="col"
                        class="px-4 py-3 font-normal text-zinc-500 text-start text-theme-sm dark:text-zinc-400">
                        Visibilidade
                    </th>
                    <th scope="col"
                        class="px-4 py-3 font-normal text-zinc-500 text-start text-theme-sm dark:text-zinc-400">
                        Membros
                    </th>
                    <th scope="col"
                        class="px-4 py-3 font-normal text-zinc-500 text-start text-theme-sm dark:text-zinc-400">
                        Criador
                    </th>
                    <th scope="col"
                        class="px-4 py-3 font-normal text-zinc-500 text-start text-theme-sm dark:text-zinc-400">
                        Ações
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse($clubs as $club)
                    <tr wire:key="{{ $club->id }}" @class(['hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors duration-200'])>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" wire:model.live="selected" value="{{ $club->id }}" class="sr-only peer" />
                                <div class="flex h-5 w-5 items-center justify-center rounded-md border-[1.25px] transition-all duration-200 bg-transparent border-zinc-300 dark:border-zinc-700 peer-checked:border-brand-500 peer-checked:bg-brand-500 peer-checked:text-white text-transparent">
                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            </label>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="shrink-0 h-10 w-10">
                                    @if($club->avatar)
                                        <img src="{{ Storage::url($club->avatar) }}" alt="{{ $club->name }}"
                                            class="h-10 w-10 rounded-full object-cover">
                                    @else
                                        <div class="h-10 w-10 bg-zinc-200 dark:bg-zinc-700 rounded-full flex items-center justify-center">
                                            <span class="text-zinc-400 text-xs">N/A</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="text-sm text-zinc-700 dark:text-zinc-300 font-medium">
                                    {{ $club->name }}
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $club->city }}/{{ $club->state }}
                            </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <x-category-badge color="green">
                                {{ $club->category }}
                            </x-category-badge>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            @if($club->is_public)
                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-400">Público</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-400">Privado</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $club->members_count }}
                            </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $club->creator_name }}
                            </div>
                        </td>
                        <td class="px-4 py-4 text-sm font-medium text-right whitespace-nowrap">
                            <div class="flex justify-center gap-2">
                                <button wire:click="manageMembers({{ $club->id }})" title="Gerenciar membros"
                                    class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium transition-all disabled:pointer-events-none disabled:opacity-50 shrink-0 outline-none active:scale-[.95] cursor-pointer hover:bg-accent hover:text-accent-foreground dark:hover:bg-accent/50 size-9 rounded-[50%] text-purple-600 bg-purple-600/10">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="w-5 h-5">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="9" cy="7" r="4"></circle>
                                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                    </svg>
                                </button>
                                <button wire:click="edit({{ $club->id }})" title="Editar"
                                    class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium transition-all disabled:pointer-events-none disabled:opacity-50 shrink-0 outline-none active:scale-[.95] cursor-pointer hover:bg-accent hover:text-accent-foreground dark:hover:bg-accent/50 size-9 rounded-[50%] text-green-600 bg-green-600/10">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="w-5 h-5">
                                        <path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path
                                            d="M18.375 2.625a1 1 0 0 1 3 3l-9.013 9.014a2 2 0 0 1-.853.505l-2.873.84a.5.5 0 0 1-.62-.62l.84-2.873a2 2 0 0 1 .506-.852z">
                                        </path>
                                    </svg>
                                </button>
                                <button wire:click="confirmDelete({{ $club->id }})" title="Excluir"
                                    class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium transition-all disabled:pointer-events-none disabled:opacity-50 shrink-0 outline-none active:scale-[.95] cursor-pointer hover:bg-accent hover:text-accent-foreground dark:hover:bg-accent/50 size-9 rounded-[50%] text-red-500 bg-red-500/10">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="w-5 h-5">
                                        <path d="M3 6h18"></path>
                                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                                        <line x1="10" x2="10" y1="11" y2="17"></line>
                                        <line x1="14" x2="14" y1="11" y2="17"></line>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-zinc-500">
                            Nenhum clube encontrado
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <!-- Pagination -->
    <div
        class="flex items-center flex-col sm:flex-row justify-between border-t border-zinc-200 px-5 py-4 dark:border-zinc-800">
        {{ $clubs->links('components.pagination.custom') }}
    </div>
</div>

{{-- Create Modal --}}
@if($showCreateModal)
    <div class="fixed inset-0 bg-black/50 dark:bg-black/70 flex items-center justify-center z-50"
        wire:click="closeCreateModal">
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto"
            wire:click.stop>
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Criar Novo Clube</h3>
                <button wire:click="closeCreateModal" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <form wire:submit="save" class="space-y-4">
                <div class="flex gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Nome *</label>
                        <input wire:model="name" type="text"
                            class="w-full border rounded-lg px-3 py-2 dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100">
                        @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex items-center pt-8">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="is_public" class="sr-only peer">
                            <div class="relative w-11 h-6 bg-zinc-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-300 dark:peer-focus:ring-brand-800 rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-zinc-600 peer-checked:bg-brand-600"></div>
                            <span class="ms-3 text-sm font-medium text-zinc-700 dark:text-zinc-300">Público</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Descrição</label>
                    <x-form.text-area wire:model="description" height="h-32" />
                    @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Cidade *</label>
                        <input wire:model="city" type="text"
                            class="w-full border rounded-lg px-3 py-2 dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100">
                        @error('city') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Estado (UF)
                            *</label>
                        <input wire:model="state" type="text" maxlength="2"
                            class="w-full border rounded-lg px-3 py-2 dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100">
                        @error('state') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Categoria
                            *</label>
                        <input wire:model="category" type="text" placeholder="ex: corrida, ciclismo..."
                            class="w-full border rounded-lg px-3 py-2 dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100">
                        @error('category') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Imagem de Capa
                            (max 2MB)</label>
                        <x-form.file-input wire:model="image" id="create_club_image" accept="image/*" />
                        @if($image)
                            <img src="{{ $image->temporaryUrl() }}" class="h-24 rounded-lg mt-2">
                        @endif
                        @error('image') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Avatar (max
                            2MB)</label>
                        <x-form.file-input wire:model="avatar" id="create_club_avatar" accept="image/*" />
                        @if($avatar)
                            <img src="{{ $avatar->temporaryUrl() }}" class="h-24 w-24 rounded-full object-cover mt-2">
                        @endif
                        @error('avatar') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" wire:click="closeCreateModal"
                        class="px-4 py-2 bg-zinc-200 text-zinc-800 rounded-lg hover:bg-zinc-300 dark:bg-zinc-700 dark:text-zinc-200">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif

{{-- Edit Modal --}}
@if($showEditModal)
    <div class="fixed inset-0 bg-black/50 dark:bg-black/70 flex items-center justify-center z-50"
        wire:click="closeEditModal">
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto"
            wire:click.stop>
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Editar Clube</h3>
                <button wire:click="closeEditModal" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <form wire:submit="update" class="space-y-4">
                <div class="flex gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Nome *</label>
                        <input wire:model="name" type="text"
                            class="w-full border rounded-lg px-3 py-2 dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100">
                        @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex items-center pt-8">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="is_public" class="sr-only peer">
                            <div class="relative w-11 h-6 bg-zinc-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-300 dark:peer-focus:ring-brand-800 rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-zinc-600 peer-checked:bg-brand-600"></div>
                            <span class="ms-3 text-sm font-medium text-zinc-700 dark:text-zinc-300">Público</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Descrição</label>
                    <x-form.text-area wire:model="description" height="h-32" />
                    @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Cidade *</label>
                        <input wire:model="city" type="text"
                            class="w-full border rounded-lg px-3 py-2 dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100">
                        @error('city') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Estado (UF)
                            *</label>
                        <input wire:model="state" type="text" maxlength="2"
                            class="w-full border rounded-lg px-3 py-2 dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100">
                        @error('state') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Categoria
                            *</label>
                        <input wire:model="category" type="text" placeholder="ex: corrida, ciclismo..."
                            class="w-full border rounded-lg px-3 py-2 dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100">
                        @error('category') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Imagem de
                            Capa</label>
                        @if($image)
                            <img src="{{ $image->temporaryUrl() }}" class="h-24 rounded-lg mb-2">
                        @elseif($existing_image)
                            <img src="{{ Storage::url($existing_image) }}" class="h-24 rounded-lg mb-2">
                        @endif
                        <x-form.file-input wire:model="image" id="edit_club_image" accept="image/*" />
                        @error('image') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Avatar</label>
                        @if($avatar)
                            <img src="{{ $avatar->temporaryUrl() }}" class="h-24 w-24 rounded-full object-cover mb-2">
                        @elseif($existing_avatar)
                            <img src="{{ Storage::url($existing_avatar) }}" class="h-24 w-24 rounded-full object-cover mb-2">
                        @endif
                        <x-form.file-input wire:model="avatar" id="edit_club_avatar" accept="image/*" />
                        @error('avatar') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" wire:click="closeEditModal"
                        class="px-4 py-2 bg-zinc-200 text-zinc-800 rounded-lg hover:bg-zinc-300 dark:bg-zinc-700 dark:text-zinc-200">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif

{{-- Members Modal --}}
@if($showMembersModal && $selectedClub)
    <div class="fixed inset-0 bg-black/50 dark:bg-black/70 flex items-center justify-center z-50"
        wire:click="closeMembersModal">
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto"
            wire:click.stop>
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">
                    Membros de {{ $selectedClub->name }}
                </h3>
                <button wire:click="closeMembersModal" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <div class="space-y-2">
                @forelse($selectedClub->members as $member)
                    <div wire:key="member-{{ $member->id }}"
                        class="flex items-center justify-between gap-3 rounded-lg border border-zinc-200 px-3 py-2 dark:border-zinc-800">
                        <div class="flex items-center gap-3">
                            <div class="text-sm">
                                <p class="font-medium text-zinc-800 dark:text-zinc-200">{{ $member->name }}</p>
                                <p class="text-zinc-500 dark:text-zinc-400">{{ $member->email }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            @if($member->pivot->role === 'creator')
                                <span class="inline-flex items-center rounded-full bg-brand-100 px-2.5 py-0.5 text-xs font-medium text-brand-800 dark:bg-brand-900/30 dark:text-brand-400">Criador</span>
                            @elseif($member->pivot->role === 'admin')
                                <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">Admin</span>
                                <button wire:click="demoteMember({{ $member->id }})"
                                    class="text-xs font-medium text-zinc-600 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200">
                                    Rebaixar
                                </button>
                                <button wire:click="removeMember({{ $member->id }})"
                                    class="text-xs font-medium text-red-600 hover:text-red-800 dark:text-red-400">
                                    Remover
                                </button>
                            @else
                                <span class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-400">Membro</span>
                                <button wire:click="promoteMember({{ $member->id }})"
                                    class="text-xs font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                    Promover a admin
                                </button>
                                <button wire:click="removeMember({{ $member->id }})"
                                    class="text-xs font-medium text-red-600 hover:text-red-800 dark:text-red-400">
                                    Remover
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-center text-zinc-500 dark:text-zinc-400 py-4">Este clube ainda não tem membros.</p>
                @endforelse
            </div>

            <div class="flex justify-end pt-5">
                <button type="button" wire:click="closeMembersModal"
                    class="px-4 py-2 bg-zinc-200 text-zinc-800 rounded-lg hover:bg-zinc-300 dark:bg-zinc-700 dark:text-zinc-200">
                    Fechar
                </button>
            </div>
        </div>
    </div>
@endif

{{-- Delete Confirmation Modal --}}
@if($confirmingDeletion)
    <div class="fixed inset-0 bg-black/50 dark:bg-black/70 flex items-center justify-center z-50" wire:click="cancelDelete">
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 max-w-md w-full mx-4" wire:click.stop>
            <div class="flex items-center gap-4 mb-4">
                <div
                    class="flex-shrink-0 w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Confirmar Exclusão</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                        Tem certeza que deseja excluir este clube? Esta ação não pode ser desfeita.
                    </p>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-5">
                <button type="button" wire:click="cancelDelete"
                    class="px-4 py-2 bg-zinc-200 text-zinc-800 rounded-lg hover:bg-zinc-300 dark:bg-zinc-700 dark:text-zinc-200">
                    Cancelar
                </button>
                <button wire:click="delete" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Sim, Excluir
                </button>
            </div>
        </div>
    </div>
@endif
</div>
