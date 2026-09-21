<div>
<x-common.page-breadcrumb title="Feed (Posts e Enquetes)" />

<x-ui.card :padding="false">
    <x-slot:header>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <span class="text-sm" style="color: var(--text-secondary);">Mostrar</span>
                <div class="w-24">
                    <x-ui.select wire:model.live="perPage" :placeholder="null">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="-1">Todos</option>
                    </x-ui.select>
                </div>
                <span class="text-sm" style="color: var(--text-secondary);">entradas</span>

                <div class="w-40">
                    <x-ui.select wire:model.live="typeFilter" :placeholder="null">
                        <option value="">Todos</option>
                        <option value="post">Posts</option>
                        <option value="poll">Enquetes</option>
                    </x-ui.select>
                </div>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="w-full xl:w-[380px]">
                    <x-ui.input wire:model.live.debounce.300ms="search" type="text" placeholder="Pesquisar..."
                        icon="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
                </div>

                @if (is_array($selected) && count($selected) > 0)
                    <button wire:click="deleteSelected"
                        class="inline-flex items-center justify-center gap-1.5 whitespace-nowrap rounded-xl px-3 py-1.5 text-xs font-semibold transition-all duration-200 hover:opacity-80 focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:ring-offset-2"
                        style="background-color: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Deletar Selecionados ({{ count($selected) }})
                    </button>
                @endif
            </div>
        </div>
    </x-slot:header>

    <x-ui.data-table>
        <x-slot:header>
            <th scope="col" class="px-4 py-3 w-10">
                <x-ui.checkbox wire:click="toggleSelectAll" :checked="$selectAll" />
            </th>
            <th scope="col" class="px-4 py-3">Autor</th>
            <th scope="col" class="px-4 py-3">Conteúdo</th>
            <th scope="col" class="px-4 py-3">Tipo</th>
            <th scope="col" class="px-4 py-3">Curtidas</th>
            <th scope="col" class="px-4 py-3">Comentários</th>
            <th scope="col" class="px-4 py-3">Criado em</th>
            <th scope="col" class="px-4 py-3 text-right">Ações</th>
        </x-slot:header>

        @forelse($posts as $post)
            <tr wire:key="{{ $post->id }}"
                @style(['background-color: rgba(16, 185, 129, 0.08)' => is_array($selected) && in_array($post->id, $selected)])>
                <td class="px-4 py-4">
                    <x-ui.checkbox wire:model.live="selected" value="{{ $post->id }}" />
                </td>
                <td class="px-4 py-4 font-medium" style="color: var(--text-primary);">
                    {{ optional($post->user)->name ?? 'Desconhecido' }}
                </td>
                <td class="px-4 py-4">
                    <div class="max-w-xs">
                        @if($post->title)
                            <span class="block font-medium" style="color: var(--text-primary);">{{ Str::limit($post->title, 30) }}</span>
                        @endif
                        <span style="color: var(--text-secondary);">{{ Str::limit(strip_tags($post->content), 60) }}</span>
                    </div>
                </td>
                <td class="px-4 py-4">
                    @if($post->type === 'poll')
                        <x-ui.badge variant="info" size="sm">Enquete</x-ui.badge>
                    @else
                        <x-ui.badge variant="primary" size="sm">Post</x-ui.badge>
                    @endif
                </td>
                <td class="px-4 py-4" style="color: var(--text-secondary);">
                    {{ $post->likes_count }}
                </td>
                <td class="px-4 py-4" style="color: var(--text-secondary);">
                    {{ $post->comments_count }}
                </td>
                <td class="px-4 py-4" style="color: var(--text-secondary);">
                    {{ $post->created_at->format('d/m/Y H:i') }}
                </td>
                <td class="px-4 py-4 text-right">
                    <div class="flex justify-end gap-2">
                        <button wire:click="view({{ $post->id }})" title="Ver detalhes"
                            class="inline-flex items-center justify-center rounded-xl px-2.5 py-1 transition-all duration-200 hover:bg-[var(--bg-hover)] focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:ring-offset-2"
                            style="color: var(--text-secondary);">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </button>
                        <button wire:click="confirmDelete({{ $post->id }})" title="Excluir"
                            class="inline-flex items-center justify-center rounded-xl px-2.5 py-1 transition-all duration-200 hover:opacity-80 focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:ring-offset-2"
                            style="background-color: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3);">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="px-4 py-4">
                    <x-ui.empty-state title="Nenhuma publicação encontrada"
                        description="Ajuste a busca ou o filtro de tipo para encontrar publicações." icon="document" />
                </td>
            </tr>
        @endforelse

        <x-slot:footer>
            <x-ui.pagination :paginator="$posts" />
        </x-slot:footer>
    </x-ui.data-table>
</x-ui.card>

{{-- View Modal --}}
@if($showViewModal && $selectedPost)
    <div class="fixed inset-0 bg-black/50 dark:bg-black/70 flex items-center justify-center z-50"
        wire:click="closeViewModal">
        <div class="rounded-2xl p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto"
            style="background-color: var(--bg-surface); border: 1px solid var(--border-color);"
            wire:click.stop>
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold" style="color: var(--text-primary);">
                    Detalhes da {{ $selectedPost->type === 'poll' ? 'Enquete' : 'Publicação' }}
                </h3>
                <button wire:click="closeViewModal" title="Fechar"
                    class="inline-flex items-center justify-center rounded-xl p-1.5 transition-all duration-200 hover:bg-[var(--bg-hover)] focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:ring-offset-2"
                    style="color: var(--text-secondary);">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="text-sm font-medium" style="color: var(--text-secondary);">Autor</label>
                    <p style="color: var(--text-primary);">{{ optional($selectedPost->user)->name ?? 'Desconhecido' }}</p>
                </div>

                @if($selectedPost->title)
                    <div>
                        <label class="text-sm font-medium" style="color: var(--text-secondary);">Título</label>
                        <p style="color: var(--text-primary);">{{ $selectedPost->title }}</p>
                    </div>
                @endif

                <div>
                    <label class="text-sm font-medium" style="color: var(--text-secondary);">Conteúdo</label>
                    <p class="whitespace-pre-line" style="color: var(--text-primary);">{{ $selectedPost->content }}</p>
                </div>

                @if($selectedPost->type === 'poll')
                    <div>
                        <label class="text-sm font-medium" style="color: var(--text-secondary);">Opções da Enquete</label>
                        <ul class="mt-2 space-y-2">
                            @forelse($selectedPost->pollOptions as $option)
                                <li class="flex items-center justify-between rounded-lg px-3 py-2" style="border: 1px solid var(--border-color);">
                                    <span style="color: var(--text-primary);">{{ $option->option_text }}</span>
                                    <span class="text-sm" style="color: var(--text-secondary);">{{ $option->votes_count }} votos</span>
                                </li>
                            @empty
                                <li class="text-sm" style="color: var(--text-secondary);">Nenhuma opção cadastrada.</li>
                            @endforelse
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium" style="color: var(--text-secondary);">Curtidas</label>
                        <p style="color: var(--text-primary);">{{ $selectedPost->likes()->count() }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium" style="color: var(--text-secondary);">Comentários</label>
                        <p style="color: var(--text-primary);">{{ $selectedPost->comments()->count() }}</p>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-medium" style="color: var(--text-secondary);">Criado em</label>
                    <p style="color: var(--text-primary);">{{ $selectedPost->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- Delete Confirmation Modal --}}
@if($confirmingDeletion)
    <div class="fixed inset-0 bg-black/50 dark:bg-black/70 flex items-center justify-center z-50" wire:click="cancelDelete">
        <div class="rounded-2xl p-6 max-w-md w-full mx-4"
            style="background-color: var(--bg-surface); border: 1px solid var(--border-color);"
            wire:click.stop>
            <div class="flex items-center gap-4 mb-4">
                <div class="flex-shrink-0 w-12 h-12 bg-red-500/10 rounded-full flex items-center justify-center">
                    <x-ui.icon name="exclamation-triangle" class="w-6 h-6 text-red-500" />
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold" style="color: var(--text-primary);">Confirmar Exclusão</h3>
                    <p class="text-sm mt-1" style="color: var(--text-secondary);">
                        Tem certeza que deseja excluir esta publicação? Comentários, curtidas e votos associados também serão removidos. Esta ação não pode ser desfeita.
                    </p>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-5">
                <button type="button" wire:click="cancelDelete"
                    class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold transition-all duration-200 hover:opacity-80 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:ring-offset-2"
                    style="background-color: var(--bg-elevated); color: var(--text-primary); border: 1px solid var(--border-color);">
                    Cancelar
                </button>
                <button wire:click="delete"
                    class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold transition-all duration-200 hover:opacity-80 focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:ring-offset-2"
                    style="background-color: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3);">
                    Sim, Excluir
                </button>
            </div>
        </div>
    </div>
@endif
</div>
