<div>
    <x-common.page-breadcrumb title="Usuários" />

    @php
        $searchIconPath = 'M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z';

        $viewIcon = <<<'SVG'
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path><circle cx="12" cy="12" r="3"></circle></svg>
            SVG;

        $editIcon = <<<'SVG'
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.375 2.625a1 1 0 0 1 3 3l-9.013 9.014a2 2 0 0 1-.853.505l-2.873.84a.5.5 0 0 1-.62-.62l.84-2.873a2 2 0 0 1 .506-.852z"></path></svg>
            SVG;

        $deleteIcon = <<<'SVG'
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"></path><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path><line x1="10" x2="10" y1="11" y2="17"></line><line x1="14" x2="14" y1="11" y2="17"></line></svg>
            SVG;

        // NOTE: <x-ui.button> is currently broken app-wide — components/ui/button.blade.php
        // has a stray "<x-ui.icon>" mention inside a PHP comment on its own @props line,
        // which trips Blade's component-tag scanner and throws "Undefined variable $component"
        // for every usage. That file is outside this screen's scope, so buttons below replicate
        // its variant styles (colors/tokens) with plain <button>/<a> tags instead.
        $btnBase = 'inline-flex items-center justify-center font-semibold transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';
        $btnPrimary = $btnBase.' bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-white focus:ring-emerald-500/50 shadow-lg shadow-emerald-500/20';
        $btnSecondary = $btnBase.' hover:opacity-80 focus:ring-emerald-500/30';
        $btnDanger = $btnBase.' hover:opacity-80 focus:ring-red-500/50';
        $btnGhost = $btnBase.' hover:bg-[var(--bg-hover)] focus:ring-emerald-500/30';
        $btnSecondaryStyle = 'background-color: var(--bg-elevated); color: var(--text-primary); border: 1px solid var(--border-color);';
        $btnDangerStyle = 'background-color: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3);';
        $btnGhostStyle = 'color: var(--text-secondary);';
    @endphp

    <div class="flex flex-col gap-4">
        <!-- Toolbar -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
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
                <div class="w-full sm:w-[300px]">
                    <x-ui.input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar..."
                        :icon="$searchIconPath" />
                </div>

                @if (is_array($selected) && count($selected) > 0)
                    <button type="button" wire:click="deleteSelected"
                        class="{{ $btnDanger }} w-full rounded-xl px-3 py-1.5 text-xs gap-1.5 sm:w-auto"
                        style="{{ $btnDangerStyle }}">
                        Deletar Selecionados ({{ count($selected) }})
                    </button>
                @endif

                <button type="button" wire:click="create"
                    class="{{ $btnPrimary }} w-full rounded-xl px-3 py-1.5 text-xs gap-1.5 sm:w-auto">
                    Nova Usuário
                </button>
            </div>
        </div>

        <!-- Table -->
        @if ($users->count() > 0)
            <x-ui.data-table striped hoverable>
                <x-slot:header>
                    <th scope="col" class="w-12 px-4 py-3">
                        <div wire:click="toggleSelectAll" class="inline-flex cursor-pointer">
                            <div class="flex h-5 w-5 items-center justify-center rounded-md border-[1.25px] transition-all duration-200"
                                :class="@js($selectAll) ? 'border-orange-500 bg-orange-500 text-white' : 'bg-transparent border-zinc-300 dark:border-zinc-700 text-transparent'">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                        </div>
                    </th>
                    <th scope="col" class="px-4 py-3 text-start font-medium">Nome</th>
                    <th scope="col" class="px-4 py-3 text-start font-medium">Plano</th>
                    <th scope="col" class="px-4 py-3 text-start font-medium">Email</th>
                    <th scope="col" class="px-4 py-3 text-start font-medium">Telefone</th>
                    <th scope="col" class="px-4 py-3 text-start font-medium">Cidade</th>
                    <th scope="col" class="px-4 py-3 text-start font-medium">Status</th>
                    <th scope="col" class="px-4 py-3 text-start font-medium">Ação</th>
                </x-slot:header>

                @foreach ($users as $user)
                    <tr wire:key="{{ $user->id }}"
                        @class(['relative' => is_array($selected) && in_array($user->id, $selected)])
                        @style(['background-color: rgba(251, 101, 20, 0.15)' => is_array($selected) && in_array($user->id, $selected)])>
                        <td class="px-4 py-4 whitespace-nowrap" @if(is_array($selected) && in_array($user->id, $selected)) style="border-left: 3px solid #fb6514;" @endif>
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" wire:model.live="selected" value="{{ $user->id }}" class="peer sr-only" />
                                <div class="flex h-5 w-5 items-center justify-center rounded-md border-[1.25px] transition-all duration-200 bg-transparent border-zinc-300 dark:border-zinc-700 peer-checked:border-blue-500 peer-checked:bg-blue-500 peer-checked:text-white text-transparent">
                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            </label>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <x-ui.avatar :src="$user->image_url" :name="$user->name" size="md" />
                                <div class="text-sm font-medium" style="color: var(--text-primary);">
                                    {{ $user->name }}
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <x-ui.badge :variant="($user->profile?->plan ?? 'free') === 'free' ? 'default' : 'success'">
                                {{ ($user->profile?->plan ?? 'free') === 'free' ? 'Gratuito' : 'Assinante' }}
                            </x-ui.badge>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm" style="color: var(--text-secondary);">
                            {{ $user->email }}
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm" style="color: var(--text-secondary);">
                            {{ $user->profile?->phone ?? '-' }}
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm" style="color: var(--text-secondary);">
                            @if($user->profile?->city)
                                {{ $user->profile->city }}{{ $user->profile->state ? ' / '.$user->profile->state : '' }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <x-ui.switch wire:click="toggleStatus({{ $user->id }})"
                                :checked="($user->profile?->status ?? 'active') === 'active'" />
                        </td>
                        <td class="px-4 py-4 text-sm font-medium whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('users.show', $user->id) }}"
                                    class="{{ $btnGhost }} rounded-full p-2" style="{{ $btnGhostStyle }}">
                                    {!! $viewIcon !!}
                                </a>
                                <button type="button" wire:click="edit({{ $user->id }})"
                                    class="{{ $btnSecondary }} rounded-full p-2" style="{{ $btnSecondaryStyle }}">
                                    {!! $editIcon !!}
                                </button>
                                <button type="button" wire:click="confirmDelete({{ $user->id }})"
                                    class="{{ $btnDanger }} rounded-full p-2" style="{{ $btnDangerStyle }}">
                                    {!! $deleteIcon !!}
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach

                <x-slot:footer>
                    <x-ui.pagination :paginator="$users" />
                </x-slot:footer>
            </x-ui.data-table>
        @else
            <x-ui.empty-state title="Nenhum usuário encontrado"
                description="Ajuste sua busca ou cadastre um novo usuário." />
        @endif
    </div>

    {{-- Create / Edit Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm"
            style="background-color: rgba(0, 0, 0, 0.5);" wire:click="closeModal" x-transition>
            <div class="mx-4 max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl shadow-2xl"
                style="background-color: var(--bg-surface); border: 1px solid var(--border-color);" wire:click.stop>
                <div class="flex items-center justify-between px-6 py-4" style="border-bottom: 1px solid var(--border-color);">
                    <h3 class="text-xl font-semibold" style="color: var(--text-primary);">
                        {{ $isEditMode ? 'Editar Usuário' : 'Novo Usuário' }}
                    </h3>
                    <button wire:click="closeModal" class="rounded-lg p-1 transition-colors" style="color: var(--text-secondary);">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>

                <form wire:submit="{{ $isEditMode ? 'update' : 'store' }}" class="space-y-6 px-6 py-5">

                    <div class="space-y-4">
                        <h4 class="pb-2 text-lg font-medium" style="color: var(--text-primary); border-bottom: 1px solid var(--border-color);">
                            Informações Pessoais
                        </h4>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <x-ui.input wire:model="name" type="text" label="Nome *" :error="$errors->first('name')" />

                            <x-ui.input wire:model="email" type="email" label="Email *" :error="$errors->first('email')" />

                            <x-ui.input wire:model="password" type="password"
                                :label="$isEditMode ? 'Senha (deixe em branco para manter)' : 'Senha *'"
                                :error="$errors->first('password')" />

                            <x-ui.input wire:model="phone" type="text" label="Telefone" :error="$errors->first('phone')" />

                            <x-ui.input wire:model="city" type="text" label="Cidade" :error="$errors->first('city')" />

                            <x-ui.input wire:model="state" type="text" label="Estado" :error="$errors->first('state')" />

                            @if(auth()->user()->isSuperAdmin())
                                <x-ui.select wire:model="role" label="Função (Role)" :error="$errors->first('role')">
                                    <option value="user">Usuário</option>
                                    <option value="manager">Gerenciador</option>
                                    <option value="admin">Administrador</option>
                                </x-ui.select>

                                <x-ui.select wire:model="plan" label="Plano (Assinatura)" :error="$errors->first('plan')">
                                    <option value="free">Gratuito</option>
                                    <option value="monthly">Mensal</option>
                                    <option value="annual">Anual</option>
                                </x-ui.select>
                            @endif
                        </div>

                        <!-- Image -->
                        <div>
                            <label class="mb-2 block text-sm font-medium" style="color: var(--text-secondary);">
                                Foto de Perfil
                            </label>
                            @if ($image)
                                <img src="{{ $image->temporaryUrl() }}" class="mb-2 h-20 w-20 rounded-full object-cover">
                            @elseif ($currentImage)
                                <img src="{{ $currentImage }}" class="mb-2 h-20 w-20 rounded-full object-cover">
                            @endif
                            <x-form.file-input wire:model="image" id="user_profile_image" accept="image/*" />
                            @error('image') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <h4 class="pb-2 pt-4 text-lg font-medium" style="color: var(--text-primary); border-bottom: 1px solid var(--border-color);">
                            Redes Sociais
                        </h4>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <x-ui.input wire:model="instagram" type="text" label="Instagram"
                                placeholder="https://instagram.com/..." :error="$errors->first('instagram')" />

                            <x-ui.input wire:model="facebook" type="text" label="Facebook"
                                placeholder="https://facebook.com/..." :error="$errors->first('facebook')" />

                            <x-ui.input wire:model="x" type="text" label="X (Twitter)"
                                placeholder="https://x.com/..." :error="$errors->first('x')" />

                            <x-ui.input wire:model="youtube" type="text" label="Youtube"
                                placeholder="https://youtube.com/..." :error="$errors->first('youtube')" />

                            <x-ui.input wire:model="mere" type="text" label="Mere (Link Extra)"
                                :error="$errors->first('mere')" />
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4" style="border-top: 1px solid var(--border-color);">
                        <button type="button" wire:click="closeModal"
                            class="{{ $btnSecondary }} rounded-xl px-4 py-2.5 text-sm gap-2" style="{{ $btnSecondaryStyle }}">
                            Cancelar
                        </button>
                        <button type="submit" class="{{ $btnPrimary }} rounded-xl px-4 py-2.5 text-sm gap-2">
                            {{ $isEditMode ? 'Salvar Alterações' : 'Criar Usuário' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal de Confirmação de Exclusão --}}
    @if($confirmingDeletion)
        <div class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm"
            style="background-color: rgba(0, 0, 0, 0.5);" wire:click="$set('confirmingDeletion', false)">
            <div class="mx-4 w-full max-w-md rounded-2xl shadow-2xl"
                style="background-color: var(--bg-surface); border: 1px solid var(--border-color);" wire:click.stop>
                <div class="p-6">
                    <div class="mb-4 flex items-center gap-4">
                        <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full"
                            style="background-color: rgba(239, 68, 68, 0.1);">
                            <svg class="h-6 w-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold" style="color: var(--text-primary);">Confirmar Exclusão</h3>
                            <p class="mt-1 text-sm" style="color: var(--text-secondary);">
                                Tem certeza que deseja excluir o usuário "<strong>{{ $userToDelete?->name }}</strong>"? Esta
                                ação não pode ser desfeita.
                            </p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-5">
                        <button type="button" wire:click="$set('confirmingDeletion', false)"
                            class="{{ $btnSecondary }} rounded-xl px-4 py-2.5 text-sm gap-2" style="{{ $btnSecondaryStyle }}">
                            Cancelar
                        </button>
                        <button type="button" wire:click="delete"
                            class="{{ $btnDanger }} rounded-xl px-4 py-2.5 text-sm gap-2" style="{{ $btnDangerStyle }}">
                            Sim, Excluir
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
