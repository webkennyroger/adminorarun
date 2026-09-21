@php
    // Nota: réplica local das classes do <x-ui.button> — o componente
    // compartilhado tem um bug de compilação pré-existente (comentário com
    // "<x-ui.icon>" dentro do @props é interpretado como tag real pelo
    // compilador do Blade), então evitamos usá-lo aqui.
    $btnBase = 'inline-flex items-center justify-center font-semibold rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';
    $btnPrimary = $btnBase.' px-4 py-2.5 text-sm gap-2 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-white focus:ring-emerald-500/50 shadow-lg shadow-emerald-500/20';
    $btnSecondary = $btnBase.' px-4 py-2.5 text-sm gap-2 hover:opacity-80 focus:ring-emerald-500/30';
    $btnSecondaryStyle = 'background-color: var(--bg-elevated); color: var(--text-primary); border: 1px solid var(--border-color);';
    $btnDanger = $btnBase.' px-4 py-2.5 text-sm gap-2 hover:opacity-80 focus:ring-red-500/50';
    $btnDangerStyle = 'background-color: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3);';
    $btnIcon = $btnBase.' px-2.5 py-1 text-xs gap-1 rounded-full!';
@endphp
<div>
<x-common.page-breadcrumb title="Desafios" />

<x-ui.card :padding="false" class="pt-4">
    <!-- Header -->
    <div class="flex flex-col gap-2 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <span style="color: var(--text-secondary);">Mostrar</span>
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
            <span style="color: var(--text-secondary);">entradas</span>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="w-full sm:w-107.5">
                <x-ui.input wire:model.live.debounce.300ms="search" type="text" placeholder="Pesquisar..."
                    icon="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
            </div>

            @if (is_array($selected) && count($selected) > 0)
                <button wire:click="deleteSelected" class="{{ $btnDanger }} w-full sm:w-auto" style="{{ $btnDangerStyle }}">
                    Deletar Selecionados ({{ count($selected) }})
                </button>
            @endif

            <button wire:click="create" class="{{ $btnPrimary }} w-full sm:w-auto">
                Novo Desafio
            </button>
        </div>
    </div>

    <div class="px-4 pb-4 space-y-3">
        {{-- Success Message --}}
        @if (session()->has('message'))
            <x-ui.alert variant="success" dismissible>
                {{ session('message') }}
            </x-ui.alert>
        @endif
        {{-- Error Message --}}
        @if (session()->has('error'))
            <x-ui.alert variant="danger" dismissible>
                {{ session('error') }}
            </x-ui.alert>
        @endif
    </div>

    <!-- Table -->
    <div class="max-w-full px-5 overflow-x-auto">
        <x-ui.data-table hoverable>
            <x-slot:header>
                <th scope="col" class="px-4 py-3 text-start">
                    <div wire:click="toggleSelectAll" class="cursor-pointer inline-flex">
                        <div class="flex h-5 w-5 items-center justify-center rounded-md border-[1.25px] transition-all duration-200"
                            :class="@js($selectAll) ? 'border-orange-500 bg-orange-500 text-white' : 'bg-transparent border-zinc-300 dark:border-zinc-700 text-transparent'">
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                    </div>
                </th>
                <th scope="col" class="px-4 py-3 text-start">
                    Imagem
                </th>
                <th scope="col" class="px-4 py-3 text-start">
                    Título
                </th>
                <th scope="col" class="px-4 py-3 text-start">
                    Descrição
                </th>
                <th scope="col" class="px-4 py-3 text-start">
                    inicio
                </th>
                <th scope="col" class="px-4 py-3 text-start">
                    fim
                </th>
                <th scope="col" class="px-4 py-3 text-start">
                    Meta
                </th>
                <th scope="col" class="px-4 py-3 text-start">
                    Categorias
                </th>
                <th scope="col" class="px-4 py-3 text-start">
                    Ações
                </th>
            </x-slot:header>

            @forelse($challenges as $challenge)
                <tr wire:key="{{ $challenge->id }}" @class(['transition-colors duration-200', 'relative' => is_array($selected) && in_array($challenge->id, $selected)])
                    @style(['background-color: rgba(251, 101, 20, 0.15)' => is_array($selected) && in_array($challenge->id, $selected)])>
                    <td class="px-4 py-4 whitespace-nowrap" @if(is_array($selected) && in_array($challenge->id, $selected))
                    style="border-left: 3px solid #fb6514;" @endif>
                        <x-ui.checkbox wire:model.live="selected" value="{{ $challenge->id }}" />
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="shrink-0 h-10 w-10">
                                @if($challenge->image)
                                    <img src="{{ Storage::url($challenge->image) }}" alt="{{ $challenge->title }}"
                                        class="h-12 w-12 rounded-lg object-cover">
                                @else
                                    <div class="h-12 w-12 rounded-lg flex items-center justify-center" style="background-color: var(--bg-elevated);">
                                        <span class="text-xs" style="color: var(--text-muted);">Sem imagem</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap">
                        <div class="text-sm" style="color: var(--text-secondary);">
                            {{ $challenge->title }}
                        </div>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap">
                        <div class="text-sm" style="color: var(--text-secondary);">
                            {!! Str::limit(strip_tags($challenge->description), 40) !!}
                        </div>

                    </td>
                    <td class="px-4 py-4 whitespace-nowrap">
                        <div class="text-sm" style="color: var(--text-secondary);">
                            {{ $challenge->start_date->format('d/m/Y') }}
                        </div>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap">
                        <div class="text-sm" style="color: var(--text-secondary);">
                            {{ $challenge->end_date->format('d/m/Y') }}
                        </div>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap">
                        <div class="text-sm" style="color: var(--text-secondary);">
                            {{ number_format($challenge->goal_km, 2, ',', '.') }} km
                        </div>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap">
                        @if ($challenge->category)
                            <x-category-badge :color="$challenge->category->color">
                                {{ $challenge->category->name }}
                            </x-category-badge>
                        @else
                            <span class="text-xs" style="color: var(--text-muted);">N/A</span>
                        @endif
                    </td>
                    <td class="px-4 py-4 text-sm font-medium text-right whitespace-nowrap">
                        <div class="flex justify-center gap-2">
                            <button wire:click="view({{ $challenge->id }})" class="{{ $btnIcon }} text-blue-500" style="background-color: rgba(59, 130, 246, 0.1);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-eye w-5 h-5">
                                    <path
                                        d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0">
                                    </path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                            <button wire:click="edit({{ $challenge->id }})" class="{{ $btnIcon }} text-green-600" style="background-color: rgba(22, 163, 74, 0.1);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-square-pen w-5 h-5">
                                    <path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path
                                        d="M18.375 2.625a1 1 0 0 1 3 3l-9.013 9.014a2 2 0 0 1-.853.505l-2.873.84a.5.5 0 0 1-.62-.62l.84-2.873a2 2 0 0 1 .506-.852z">
                                    </path>
                                </svg>
                            </button>
                            <button wire:click="confirmDelete({{ $challenge->id }})" class="{{ $btnIcon }}" style="{{ $btnDangerStyle }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-trash2 lucide-trash-2 w-5 h-5">
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
                    <td colspan="9" class="p-0">
                        <x-ui.empty-state title="Nenhum desafio encontrado"
                            description="Tente ajustar sua busca ou crie um novo desafio." />
                    </td>
                </tr>
            @endforelse
        </x-ui.data-table>
    </div>
    <!-- Pagination -->
    <div class="flex items-center flex-col sm:flex-row justify-between px-5 py-4" style="border-top: 1px solid var(--border-color);">
        <x-ui.pagination :paginator="$challenges" />
    </div>
</x-ui.card>

{{-- Create Modal --}}
@if($showCreateModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm" style="background-color: rgba(0, 0, 0, 0.5);"
        wire:click="closeCreateModal">
        <div class="w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto rounded-2xl shadow-2xl p-6"
            style="background-color: var(--bg-surface); border: 1px solid var(--border-color);" wire:click.stop>
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold" style="color: var(--text-primary);">Criar Novo Desafio</h3>
                <button wire:click="closeCreateModal" class="p-1 rounded-lg transition-colors" style="color: var(--text-secondary);">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <form wire:submit="save" class="space-y-4">
                <div class="flex gap-4">
                    <div class="flex-1">
                        <x-ui.input label="Título *" wire:model="title" type="text" :error="$errors->first('title')" />
                    </div>
                    <div class="flex items-center pt-8">
                        <x-ui.checkbox wire:model="is_featured" label="Destaque do Mês" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--text-secondary);">Descrição *</label>
                    <x-form.text-area wire:model="description" height="h-32" />
                    @error('description') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-form.date-picker label="Data de Início *" name="start_date" id="create_challenge_start_date"
                            placeholder="Selecione a data" :default-date="$start_date"
                            x-on:date-change="$wire.set('start_date', $event.detail.dateStr)" />
                        <div class="mt-1">
                            @error('start_date') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div>
                        <x-form.date-picker label="Data de Fim *" name="end_date" id="create_challenge_end_date"
                            placeholder="Selecione a data" :default-date="$end_date"
                            x-on:date-change="$wire.set('end_date', $event.detail.dateStr)" />
                        <div class="mt-1">
                            @error('end_date') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-ui.input label="Meta (KM) *" wire:model="goal_km" type="number" step="0.01" :error="$errors->first('goal_km')" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2" style="color: var(--text-secondary);">Categoria
                            *</label>
                        <x-form.multiple-select wire:model="category_id" :multiple="false"
                            placeholder="Selecione uma categoria" :options="$categories->map(fn($category) => ['value' => $category->id, 'label' => $category->name])->toArray()" />
                        @error('category_id') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--text-secondary);">Imagem de Capa * (max
                        2MB)</label>
                    <x-form.file-input wire:model="image" id="create_challenge_image" accept="image/*" />
                    @error('image') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                </div>



                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" wire:click="closeCreateModal" class="{{ $btnSecondary }}" style="{{ $btnSecondaryStyle }}">
                        Cancelar
                    </button>
                    <button type="submit" class="{{ $btnPrimary }}">
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif

{{-- View Modal --}}
@if($showViewModal && $selectedChallenge)
    <div class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm" style="background-color: rgba(0, 0, 0, 0.5);"
        wire:click="closeViewModal">
        <div class="w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto rounded-2xl shadow-2xl p-6"
            style="background-color: var(--bg-surface); border: 1px solid var(--border-color);" wire:click.stop>
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold" style="color: var(--text-primary);">Detalhes do Desafio</h3>
                <button wire:click="closeViewModal" class="p-1 rounded-lg transition-colors" style="color: var(--text-secondary);">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            @if($selectedChallenge->image)
                <img src="{{ Storage::url($selectedChallenge->image) }}" alt="{{ $selectedChallenge->title }}"
                    class="w-full h-48 object-cover rounded-lg mb-4">
            @endif

            <div class="space-y-4">
                <div>
                    <label class="text-sm font-medium" style="color: var(--text-muted);">Título</label>
                    <p style="color: var(--text-primary);">{{ $selectedChallenge->title }}</p>
                </div>

                <div>
                    <label class="text-sm font-medium" style="color: var(--text-muted);">Descrição</label>
                    <div class="prose dark:prose-invert max-w-none" style="color: var(--text-primary);">
                        {!! $selectedChallenge->description !!}
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium" style="color: var(--text-muted);">Data de Início</label>
                        <p style="color: var(--text-primary);">
                            {{ $selectedChallenge->start_date->format('d/m/Y') }}
                        </p>
                    </div>
                    <div>
                        <label class="text-sm font-medium" style="color: var(--text-muted);">Data de Fim</label>
                        <p style="color: var(--text-primary);">{{ $selectedChallenge->end_date->format('d/m/Y') }}
                        </p>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-medium" style="color: var(--text-muted);">Meta (KM)</label>
                    <p style="color: var(--text-primary);">
                        {{ number_format($selectedChallenge->goal_km, 2, ',', '.') }} km
                    </p>
                </div>

                <div>
                    <label class="text-sm font-medium" style="color: var(--text-muted);">Categoria</label>
                    <div class="mt-1">
                        @if($selectedChallenge->category)
                            <x-category-badge :color="$selectedChallenge->category->color">
                                {{ $selectedChallenge->category->name }}
                            </x-category-badge>
                        @else
                            <span style="color: var(--text-muted);">N/A</span>
                        @endif
                    </div>
                </div>


            </div>
        </div>
    </div>
@endif

{{-- Edit Modal --}}
@if($showEditModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm" style="background-color: rgba(0, 0, 0, 0.5);"
        wire:click="closeEditModal">
        <div class="w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto rounded-2xl shadow-2xl p-6"
            style="background-color: var(--bg-surface); border: 1px solid var(--border-color);" wire:click.stop>
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold" style="color: var(--text-primary);">Editar Desafio</h3>
                <button wire:click="closeEditModal" class="p-1 rounded-lg transition-colors" style="color: var(--text-secondary);">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <form wire:submit="update" class="space-y-4">
                <div class="flex gap-4">
                    <div class="flex-1">
                        <x-ui.input label="Título *" wire:model="title" type="text" :error="$errors->first('title')" />
                    </div>
                    <div class="flex items-center pt-8">
                        <x-ui.checkbox wire:model="is_featured" label="Destaque do Mês" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--text-secondary);">Descrição *</label>
                    <x-form.text-area wire:model="description" height="h-32" />
                    @error('description') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-form.date-picker label="Data de Início *" name="start_date" id="edit_challenge_start_date"
                            placeholder="Selecione a data" :default-date="$start_date"
                            x-on:date-change="$wire.set('start_date', $event.detail.dateStr)" />
                        <div class="mt-1">
                            @error('start_date') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div>
                        <x-form.date-picker label="Data de Fim *" name="end_date" id="edit_challenge_end_date"
                            placeholder="Selecione a data" :default-date="$end_date"
                            x-on:date-change="$wire.set('end_date', $event.detail.dateStr)" />
                        <div class="mt-1">
                            @error('end_date') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-ui.input label="Meta (KM) *" wire:model="goal_km" type="number" step="0.01" :error="$errors->first('goal_km')" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2" style="color: var(--text-secondary);">Categoria
                            *</label>
                        <x-form.multiple-select wire:model="category_id" :multiple="false"
                            placeholder="Selecione uma categoria" :options="$categories->map(fn($category) => ['value' => $category->id, 'label' => $category->name])->toArray()" />
                        @error('category_id') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--text-secondary);">Imagem</label>
                    @if($image)
                        <img src="{{ $image->temporaryUrl() }}" class="h-32 rounded-lg mb-2">
                    @elseif($existing_image)
                        <img src="{{ Storage::url($existing_image) }}" class="h-32 rounded-lg mb-2">
                    @endif
                    <x-form.file-input wire:model="image" id="edit_challenge_image" accept="image/*" />
                    @error('image') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                </div>



                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" wire:click="closeEditModal" class="{{ $btnSecondary }}" style="{{ $btnSecondaryStyle }}">
                        Cancelar
                    </button>
                    <button type="submit" class="{{ $btnPrimary }}">
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif

{{-- Delete Confirmation Modal --}}
@if($confirmingDeletion)
    <div class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm" style="background-color: rgba(0, 0, 0, 0.5);" wire:click="cancelDelete">
        <div class="w-full max-w-md mx-4 rounded-2xl shadow-2xl p-6" style="background-color: var(--bg-surface); border: 1px solid var(--border-color);" wire:click.stop>
            <div class="flex items-center gap-4 mb-4">
                <div class="shrink-0 w-12 h-12 rounded-full flex items-center justify-center bg-red-500/10">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold" style="color: var(--text-primary);">Confirmar Exclusão</h3>
                    <p class="text-sm mt-1" style="color: var(--text-secondary);">
                        Tem certeza que deseja excluir este desafio? Esta ação não pode ser desfeita.
                    </p>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-5">
                <button type="button" wire:click="cancelDelete" class="{{ $btnSecondary }}" style="{{ $btnSecondaryStyle }}">
                    Cancelar
                </button>
                <button wire:click="delete" class="{{ $btnDanger }}" style="{{ $btnDangerStyle }}">
                    Sim, Excluir
                </button>
            </div>
        </div>
    </div>
@endif
</div>
