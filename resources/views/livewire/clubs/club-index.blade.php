<div>
<x-common.page-breadcrumb title="Clubes" />

@php
    $searchIconPath = 'M11 19a8 8 0 1 0 0-16 8 8 0 0 0 0 16zM21 21l-4.3-4.3';

    // Réplica manual das classes computadas por <x-ui.button> (variant/size).
    // Não usamos o componente aqui por um bug de compilação pré-existente em
    // resources/views/components/ui/button.blade.php: o comentário da prop
    // "startIcon" contém o texto literal "<x-ui.icon>", que o compilador do
    // Blade casa como uma tag de componente real e corrompe a view inteira
    // (ErrorException: Undefined variable $component). Esse arquivo está
    // fora do escopo autorizado para este refactor, então replicamos aqui o
    // visual exato do botão em vez de usar o componente quebrado.
    $btnBase = 'inline-flex items-center justify-center font-semibold rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';
    $btnPrimary = $btnBase.' px-4 py-2.5 text-sm gap-2 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-white focus:ring-emerald-500/50 shadow-lg shadow-emerald-500/20';
    $btnSecondary = $btnBase.' px-4 py-2.5 text-sm gap-2 hover:opacity-80 focus:ring-emerald-500/30';
    $btnSecondaryStyle = 'background-color: var(--bg-elevated); color: var(--text-primary); border: 1px solid var(--border-color);';
    $btnDanger = $btnBase.' px-4 py-2.5 text-sm gap-2 hover:opacity-80 focus:ring-red-500/50';
    $btnDangerStyle = 'background-color: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3);';
    $btnLinkStyle = 'color: #34d399;';
@endphp

<x-ui.card>
    <div class="flex flex-col gap-3">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <span class="text-sm" style="color: var(--text-secondary);">Mostrar</span>
                <div class="w-24">
                    <x-ui.select wire:model.live="perPage" :placeholder="false">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="-1">Todos</option>
                    </x-ui.select>
                </div>
                <span class="text-sm" style="color: var(--text-secondary);">entradas</span>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="w-full sm:w-75">
                    <x-ui.input wire:model.live.debounce.300ms="search" type="text"
                        placeholder="Pesquisar por nome ou cidade..." :icon="$searchIconPath" />
                </div>

                @if (is_array($selected) && count($selected) > 0)
                    <button type="button" wire:click="deleteSelected" class="{{ $btnDanger }}" style="{{ $btnDangerStyle }}">
                        Deletar Selecionados ({{ count($selected) }})
                    </button>
                @endif

                <button type="button" wire:click="create" class="{{ $btnPrimary }}">
                    Novo Clube
                </button>
            </div>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="w-full sm:w-56">
                <x-ui.select wire:model.live="filterCategory" placeholder="Todas as categorias">
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                    @endforeach
                </x-ui.select>
            </div>
            <div class="w-full sm:w-56">
                <x-ui.select wire:model.live="filterPublic" placeholder="Todos (público/privado)">
                    <option value="1">Somente públicos</option>
                    <option value="0">Somente privados</option>
                </x-ui.select>
            </div>
        </div>
    </div>
</x-ui.card>

<div class="mt-4">
    <x-ui.data-table>
        <x-slot:header>
            <th scope="col" class="px-4 py-3">
                <x-ui.checkbox wire:click="toggleSelectAll" :checked="$selectAll" />
            </th>
            <th scope="col" class="px-4 py-3 font-medium">Clube</th>
            <th scope="col" class="px-4 py-3 font-medium">Local</th>
            <th scope="col" class="px-4 py-3 font-medium">Categoria</th>
            <th scope="col" class="px-4 py-3 font-medium">Visibilidade</th>
            <th scope="col" class="px-4 py-3 font-medium">Membros</th>
            <th scope="col" class="px-4 py-3 font-medium">Criador</th>
            <th scope="col" class="px-4 py-3 font-medium text-right">Ações</th>
        </x-slot:header>

        @forelse($clubs as $club)
            <tr wire:key="{{ $club->id }}">
                <td class="px-4 py-4 whitespace-nowrap">
                    <x-ui.checkbox wire:model.live="selected" value="{{ $club->id }}" />
                </td>
                <td class="px-4 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-3">
                        <x-ui.avatar :src="$club->avatar ? Storage::url($club->avatar) : null" :name="$club->name" size="md" />
                        <div class="text-sm font-medium" style="color: var(--text-primary);">
                            {{ $club->name }}
                        </div>
                    </div>
                </td>
                <td class="px-4 py-4 whitespace-nowrap">
                    <div class="text-sm" style="color: var(--text-secondary);">
                        {{ $club->city }}/{{ $club->state }}
                    </div>
                </td>
                <td class="px-4 py-4 whitespace-nowrap">
                    <x-ui.badge variant="info">
                        {{ $club->category }}
                    </x-ui.badge>
                </td>
                <td class="px-4 py-4 whitespace-nowrap">
                    @if($club->is_public)
                        <x-ui.badge variant="success">Público</x-ui.badge>
                    @else
                        <x-ui.badge variant="default">Privado</x-ui.badge>
                    @endif
                </td>
                <td class="px-4 py-4 whitespace-nowrap">
                    <div class="text-sm" style="color: var(--text-secondary);">
                        {{ $club->members_count }}
                    </div>
                </td>
                <td class="px-4 py-4 whitespace-nowrap">
                    <div class="text-sm" style="color: var(--text-secondary);">
                        {{ $club->creator_name }}
                    </div>
                </td>
                <td class="px-4 py-4 text-sm font-medium whitespace-nowrap">
                    <div class="flex justify-end gap-2">
                        <button type="button" wire:click="manageMembers({{ $club->id }})" title="Gerenciar membros"
                            class="inline-flex items-center justify-center rounded-full p-2 text-purple-600 bg-purple-600/10 hover:bg-purple-600/20 transition-colors duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="w-5 h-5">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </button>
                        <button type="button" wire:click="edit({{ $club->id }})" title="Editar"
                            class="inline-flex items-center justify-center rounded-full p-2 text-emerald-600 bg-emerald-600/10 hover:bg-emerald-600/20 transition-colors duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="w-5 h-5">
                                <path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path
                                    d="M18.375 2.625a1 1 0 0 1 3 3l-9.013 9.014a2 2 0 0 1-.853.505l-2.873.84a.5.5 0 0 1-.62-.62l.84-2.873a2 2 0 0 1 .506-.852z">
                                </path>
                            </svg>
                        </button>
                        <button type="button" wire:click="confirmDelete({{ $club->id }})" title="Excluir"
                            class="inline-flex items-center justify-center rounded-full p-2 text-red-500 bg-red-500/10 hover:bg-red-500/20 transition-colors duration-200">
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
                <td colspan="8" class="p-0">
                    <x-ui.empty-state title="Nenhum clube encontrado"
                        description="Ajuste os filtros de busca ou crie um novo clube." />
                </td>
            </tr>
        @endforelse

        <x-slot:footer>
            <x-ui.pagination :paginator="$clubs" />
        </x-slot:footer>
    </x-ui.data-table>
</div>

{{-- Create Modal --}}
@if($showCreateModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm bg-black/50 dark:bg-black/70"
        wire:click="closeCreateModal">
        <div class="w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto rounded-2xl shadow-2xl"
            style="background-color: var(--bg-surface); border: 1px solid var(--border-color);" wire:click.stop>
            <div class="flex items-center justify-between px-6 py-4" style="border-bottom: 1px solid var(--border-color);">
                <h3 class="text-lg font-semibold" style="color: var(--text-primary);">Criar Novo Clube</h3>
                <button wire:click="closeCreateModal" class="p-1 rounded-lg" style="color: var(--text-secondary);">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <form wire:submit="save" class="px-6 py-5 space-y-4">
                <div class="flex gap-4">
                    <div class="flex-1">
                        <x-ui.field label="Nome *" :error="$errors->first('name')">
                            <x-ui.input wire:model="name" type="text" />
                        </x-ui.field>
                    </div>
                    <div class="flex items-center pt-6">
                        <x-ui.switch wire:model="is_public" label="Público" />
                    </div>
                </div>

                <x-ui.field label="Descrição" :error="$errors->first('description')">
                    <x-ui.textarea wire:model="description" rows="4" />
                </x-ui.field>

                <div class="grid grid-cols-3 gap-4">
                    <x-ui.field label="Cidade *" :error="$errors->first('city')">
                        <x-ui.input wire:model="city" type="text" />
                    </x-ui.field>
                    <x-ui.field label="Estado (UF) *" :error="$errors->first('state')">
                        <x-ui.input wire:model="state" type="text" maxlength="2" />
                    </x-ui.field>
                    <x-ui.field label="Categoria *" :error="$errors->first('category')">
                        <x-ui.input wire:model="category" type="text" placeholder="ex: corrida, ciclismo..." />
                    </x-ui.field>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <x-ui.field label="Imagem de Capa (max 2MB)" :error="$errors->first('image')">
                        <x-form.file-input wire:model="image" id="create_club_image" accept="image/*" />
                        @if($image)
                            <img src="{{ $image->temporaryUrl() }}" class="h-24 rounded-lg mt-2">
                        @endif
                    </x-ui.field>
                    <x-ui.field label="Avatar (max 2MB)" :error="$errors->first('avatar')">
                        <x-form.file-input wire:model="avatar" id="create_club_avatar" accept="image/*" />
                        @if($avatar)
                            <img src="{{ $avatar->temporaryUrl() }}" class="h-24 w-24 rounded-full object-cover mt-2">
                        @endif
                    </x-ui.field>
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

{{-- Edit Modal --}}
@if($showEditModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm bg-black/50 dark:bg-black/70"
        wire:click="closeEditModal">
        <div class="w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto rounded-2xl shadow-2xl"
            style="background-color: var(--bg-surface); border: 1px solid var(--border-color);" wire:click.stop>
            <div class="flex items-center justify-between px-6 py-4" style="border-bottom: 1px solid var(--border-color);">
                <h3 class="text-lg font-semibold" style="color: var(--text-primary);">Editar Clube</h3>
                <button wire:click="closeEditModal" class="p-1 rounded-lg" style="color: var(--text-secondary);">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <form wire:submit="update" class="px-6 py-5 space-y-4">
                <div class="flex gap-4">
                    <div class="flex-1">
                        <x-ui.field label="Nome *" :error="$errors->first('name')">
                            <x-ui.input wire:model="name" type="text" />
                        </x-ui.field>
                    </div>
                    <div class="flex items-center pt-6">
                        <x-ui.switch wire:model="is_public" label="Público" />
                    </div>
                </div>

                <x-ui.field label="Descrição" :error="$errors->first('description')">
                    <x-ui.textarea wire:model="description" rows="4" />
                </x-ui.field>

                <div class="grid grid-cols-3 gap-4">
                    <x-ui.field label="Cidade *" :error="$errors->first('city')">
                        <x-ui.input wire:model="city" type="text" />
                    </x-ui.field>
                    <x-ui.field label="Estado (UF) *" :error="$errors->first('state')">
                        <x-ui.input wire:model="state" type="text" maxlength="2" />
                    </x-ui.field>
                    <x-ui.field label="Categoria *" :error="$errors->first('category')">
                        <x-ui.input wire:model="category" type="text" placeholder="ex: corrida, ciclismo..." />
                    </x-ui.field>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <x-ui.field label="Imagem de Capa" :error="$errors->first('image')">
                        @if($image)
                            <img src="{{ $image->temporaryUrl() }}" class="h-24 rounded-lg mb-2">
                        @elseif($existing_image)
                            <img src="{{ Storage::url($existing_image) }}" class="h-24 rounded-lg mb-2">
                        @endif
                        <x-form.file-input wire:model="image" id="edit_club_image" accept="image/*" />
                    </x-ui.field>
                    <x-ui.field label="Avatar" :error="$errors->first('avatar')">
                        @if($avatar)
                            <img src="{{ $avatar->temporaryUrl() }}" class="h-24 w-24 rounded-full object-cover mb-2">
                        @elseif($existing_avatar)
                            <img src="{{ Storage::url($existing_avatar) }}" class="h-24 w-24 rounded-full object-cover mb-2">
                        @endif
                        <x-form.file-input wire:model="avatar" id="edit_club_avatar" accept="image/*" />
                    </x-ui.field>
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

{{-- Members Modal --}}
@if($showMembersModal && $selectedClub)
    <div class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm bg-black/50 dark:bg-black/70"
        wire:click="closeMembersModal">
        <div class="w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto rounded-2xl shadow-2xl"
            style="background-color: var(--bg-surface); border: 1px solid var(--border-color);" wire:click.stop>
            <div class="flex items-center justify-between px-6 py-4" style="border-bottom: 1px solid var(--border-color);">
                <h3 class="text-lg font-semibold" style="color: var(--text-primary);">
                    Membros de {{ $selectedClub->name }}
                </h3>
                <button wire:click="closeMembersModal" class="p-1 rounded-lg" style="color: var(--text-secondary);">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <div class="px-6 py-5 space-y-2">
                @forelse($selectedClub->members as $member)
                    <div wire:key="member-{{ $member->id }}"
                        class="flex items-center justify-between gap-3 rounded-lg px-3 py-2"
                        style="border: 1px solid var(--border-color);">
                        <div class="flex items-center gap-3">
                            <x-ui.avatar :name="$member->name" size="sm" />
                            <div class="text-sm">
                                <p class="font-medium" style="color: var(--text-primary);">{{ $member->name }}</p>
                                <p style="color: var(--text-muted);">{{ $member->email }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            @if($member->pivot->role === 'creator')
                                <x-ui.badge variant="primary">Criador</x-ui.badge>
                            @elseif($member->pivot->role === 'admin')
                                <x-ui.badge variant="info">Admin</x-ui.badge>
                                <button type="button" wire:click="demoteMember({{ $member->id }})"
                                    class="text-xs font-medium hover:underline" style="{{ $btnLinkStyle }}">
                                    Rebaixar
                                </button>
                                <button type="button" wire:click="removeMember({{ $member->id }})"
                                    class="text-xs font-medium text-red-500 hover:text-red-400 hover:underline">
                                    Remover
                                </button>
                            @else
                                <x-ui.badge variant="default">Membro</x-ui.badge>
                                <button type="button" wire:click="promoteMember({{ $member->id }})"
                                    class="text-xs font-medium hover:underline" style="{{ $btnLinkStyle }}">
                                    Promover a admin
                                </button>
                                <button type="button" wire:click="removeMember({{ $member->id }})"
                                    class="text-xs font-medium text-red-500 hover:text-red-400 hover:underline">
                                    Remover
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-center py-4" style="color: var(--text-muted);">Este clube ainda não tem membros.</p>
                @endforelse
            </div>

            <div class="flex justify-end px-6 py-4 gap-3" style="border-top: 1px solid var(--border-color);">
                <button type="button" wire:click="closeMembersModal" class="{{ $btnSecondary }}" style="{{ $btnSecondaryStyle }}">
                    Fechar
                </button>
            </div>
        </div>
    </div>
@endif

{{-- Delete Confirmation Modal --}}
@if($confirmingDeletion)
    <div class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm bg-black/50 dark:bg-black/70"
        wire:click="cancelDelete">
        <div class="w-full max-w-md mx-4 rounded-2xl shadow-2xl"
            style="background-color: var(--bg-surface); border: 1px solid var(--border-color);" wire:click.stop>
            <div class="flex items-center gap-4 px-6 py-5">
                <div class="shrink-0 w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold" style="color: var(--text-primary);">Confirmar Exclusão</h3>
                    <p class="text-sm mt-1" style="color: var(--text-secondary);">
                        Tem certeza que deseja excluir este clube? Esta ação não pode ser desfeita.
                    </p>
                </div>
            </div>

            <div class="flex justify-end gap-3 px-6 py-4" style="border-top: 1px solid var(--border-color);">
                <button type="button" wire:click="cancelDelete" class="{{ $btnSecondary }}" style="{{ $btnSecondaryStyle }}">
                    Cancelar
                </button>
                <button type="button" wire:click="delete" class="{{ $btnDanger }}" style="{{ $btnDangerStyle }}">
                    Sim, Excluir
                </button>
            </div>
        </div>
    </div>
@endif
</div>
