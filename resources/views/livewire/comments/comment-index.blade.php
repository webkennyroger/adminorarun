<div>
<x-common.page-breadcrumb title="Comentários" />

<x-ui.card :padding="false">
    <x-slot:header>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
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
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="w-full xl:w-[430px]">
                    <x-ui.input wire:model.live.debounce.300ms="search" type="text" placeholder="Pesquisar no texto do comentário..."
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

    {{-- Success Message --}}
    @if (session()->has('message'))
        <div class="px-4 pt-4">
            <x-ui.alert variant="success" dismissible>
                {{ session('message') }}
            </x-ui.alert>
        </div>
    @endif

    <x-ui.data-table>
        <x-slot:header>
            <th scope="col" class="px-4 py-3 w-10">
                <x-ui.checkbox wire:click="toggleSelectAll" :checked="$selectAll" />
            </th>
            <th scope="col" class="px-4 py-3">Autor</th>
            <th scope="col" class="px-4 py-3">Comentário</th>
            <th scope="col" class="px-4 py-3">Em quê</th>
            <th scope="col" class="px-4 py-3">Data</th>
            <th scope="col" class="px-4 py-3 text-right">Ações</th>
        </x-slot:header>

        @forelse($comments as $comment)
            <tr wire:key="{{ $comment->id }}"
                @style(['background-color: rgba(16, 185, 129, 0.08)' => is_array($selected) && in_array($comment->id, $selected)])>
                <td class="px-4 py-4">
                    <x-ui.checkbox wire:model.live="selected" value="{{ $comment->id }}" />
                </td>
                <td class="px-4 py-4 font-medium" style="color: var(--text-primary);">
                    {{ $comment->user?->name ?? 'Usuário removido' }}
                </td>
                <td class="px-4 py-4">
                    <div class="max-w-xs" style="color: var(--text-secondary);">
                        {{ Str::limit($comment->body, 80) }}
                    </div>
                    @if ($comment->parent_id)
                        <div class="text-xs italic mt-1" style="color: var(--text-muted);">
                            Resposta a: {{ Str::limit($comment->parent?->body ?? '(comentário original removido)', 40) }}
                        </div>
                    @endif
                </td>
                <td class="px-4 py-4" style="color: var(--text-secondary);">
                    @if ($comment->commentable_type === \App\Models\Post::class)
                        {{ Str::limit($comment->commentable?->title ?? '(post removido)', 40) }}
                    @else
                        <span class="text-xs" style="color: var(--text-muted);">{{ class_basename($comment->commentable_type) }}</span>
                    @endif
                </td>
                <td class="px-4 py-4" style="color: var(--text-secondary);">
                    {{ $comment->created_at->format('d/m/Y H:i') }}
                </td>
                <td class="px-4 py-4 text-right">
                    <div class="flex justify-end gap-2">
                        <button wire:click="confirmDelete({{ $comment->id }})" title="Excluir"
                            class="inline-flex items-center justify-center rounded-xl px-2.5 py-1 transition-all duration-200 hover:opacity-80 focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:ring-offset-2"
                            style="background-color: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3);">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-4 py-4">
                    <x-ui.empty-state title="Nenhum comentário encontrado"
                        description="Ajuste a busca para encontrar comentários." icon="document" />
                </td>
            </tr>
        @endforelse

        <x-slot:footer>
            <x-ui.pagination :paginator="$comments" />
        </x-slot:footer>
    </x-ui.data-table>
</x-ui.card>

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
                        Tem certeza que deseja excluir este comentário? As respostas a ele também serão excluídas. Esta ação não pode ser desfeita.
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
