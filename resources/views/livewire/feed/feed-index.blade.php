<div>
<x-common.page-breadcrumb title="Feed (Posts e Enquetes)" />

<div class="rounded-2xl border border-zinc-200 bg-white pt-4 dark:border-zinc-800 dark:bg-white/[0.03]">
    <!-- Header -->
    <div class="flex flex-col gap-2 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
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

            <select wire:model.live="typeFilter"
                class="h-11 rounded-lg border border-zinc-200 bg-transparent px-3 text-sm text-zinc-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-zinc-800 dark:bg-white/3 dark:text-white/90">
                <option value="">Todos</option>
                <option value="post">Posts</option>
                <option value="poll">Enquetes</option>
            </select>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <form>
                <div class="relative">
                    <span class="absolute -translate-y-1/2 pointer-events-none left-4 top-1/2">
                        <!-- Search Icon -->
                        <svg class="fill-zinc-500 dark:fill-zinc-400" width="20" height="20" viewBox="0 0 20 20"
                            fill="none">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M3.04175 9.37363C3.04175 5.87693 5.87711 3.04199 9.37508 3.04199C12.8731 3.04199 15.7084 5.87693 15.7084 9.37363C15.7084 12.8703 12.8731 15.7053 9.37508 15.7053C5.87711 15.7053 3.04175 12.8703 3.04175 9.37363ZM9.37508 1.54199C5.04902 1.54199 1.54175 5.04817 1.54175 9.37363C1.54175 13.6991 5.04902 17.2053 9.37508 17.2053C11.2674 17.2053 13.003 16.5344 14.357 15.4176L17.177 18.238C17.4699 18.5309 17.9448 18.5309 18.2377 18.238C18.5306 17.9451 18.5306 17.4703 18.2377 17.1774L15.418 14.3573C16.5365 13.0033 17.2084 11.2669 17.2084 9.37363C17.2084 5.04817 13.7011 1.54199 9.37508 1.54199Z"
                                fill="" />
                        </svg>
                    </span>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Pesquisar..."
                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-zinc-200 bg-transparent py-2.5 pl-12 pr-14 text-sm text-zinc-800 shadow-theme-xs placeholder:text-zinc-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-zinc-800 dark:bg-white/3 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800 xl:w-[380px]" />
                    <button
                        class="absolute right-2.5 top-1/2 inline-flex -translate-y-1/2 items-center gap-0.5 rounded-lg border border-zinc-200 bg-zinc-50 px-[7px] py-[4.5px] text-xs -tracking-[0.2px] text-zinc-500 dark:border-zinc-800 dark:bg-white/[0.03] dark:text-zinc-400">
                        <span> ⌘ </span>
                        <span> K </span>
                    </button>
                </div>
            </form>

            @if (is_array($selected) && count($selected) > 0)
                <button wire:click="deleteSelected"
                    class="flex w-full items-center justify-center gap-2 rounded-lg border border-red-300 bg-red-200 px-4 py-[11px] text-sm font-medium text-red-700 shadow-theme-xs dark:border-red-700 dark:bg-red-800 dark:text-zinc-400 sm:w-auto">
                    Deletar Selecionados ({{ count($selected) }})
                </button>
            @endif
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
                                :class="@js($selectAll) ? 'border-orange-500 bg-orange-500 text-white' : 'bg-transparent border-zinc-300 dark:border-zinc-700 text-transparent'">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                        </div>
                    </th>
                    <th scope="col"
                        class="px-4 py-3 font-normal text-zinc-500 text-start text-theme-sm dark:text-zinc-400">
                        Autor
                    </th>
                    <th scope="col"
                        class="px-4 py-3 font-normal text-zinc-500 text-start text-theme-sm dark:text-zinc-400">
                        Conteúdo
                    </th>
                    <th scope="col"
                        class="px-4 py-3 font-normal text-zinc-500 text-start text-theme-sm dark:text-zinc-400">
                        Tipo
                    </th>
                    <th scope="col"
                        class="px-4 py-3 font-normal text-zinc-500 text-start text-theme-sm dark:text-zinc-400">
                        Curtidas
                    </th>
                    <th scope="col"
                        class="px-4 py-3 font-normal text-zinc-500 text-start text-theme-sm dark:text-zinc-400">
                        Comentários
                    </th>
                    <th scope="col"
                        class="px-4 py-3 font-normal text-zinc-500 text-start text-theme-sm dark:text-zinc-400">
                        Criado em
                    </th>
                    <th scope="col"
                        class="px-4 py-3 font-normal text-zinc-500 text-start text-theme-sm dark:text-zinc-400">
                        Ações
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse($posts as $post)
                    <tr wire:key="{{ $post->id }}" @class(['hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors duration-200', 'relative' => is_array($selected) && in_array($post->id, $selected)])
                        @style(['background-color: rgba(251, 101, 20, 0.15)' => is_array($selected) && in_array($post->id, $selected)])>
                        <td class="px-4 py-4 whitespace-nowrap" @if(is_array($selected) && in_array($post->id, $selected))
                        style="border-left: 3px solid #fb6514;" @endif>
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" wire:model.live="selected" value="{{ $post->id }}" class="sr-only peer" />
                                <div class="flex h-5 w-5 items-center justify-center rounded-md border-[1.25px] transition-all duration-200 bg-transparent border-zinc-300 dark:border-zinc-700 peer-checked:border-blue-500 peer-checked:bg-blue-500 peer-checked:text-white text-transparent">
                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            </label>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-zinc-800 dark:text-white/90">
                                {{ optional($post->user)->name ?? 'Desconhecido' }}
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="text-sm text-zinc-500 dark:text-zinc-400 max-w-xs">
                                @if($post->title)
                                    <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ Str::limit($post->title, 30) }}</span><br>
                                @endif
                                {{ Str::limit(strip_tags($post->content), 60) }}
                            </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            @if($post->type === 'poll')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800 dark:bg-purple-500/15 dark:text-purple-400">
                                    Enquete
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-500/15 dark:text-blue-400">
                                    Post
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $post->likes_count }}
                            </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $post->comments_count }}
                            </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $post->created_at->format('d/m/Y H:i') }}
                            </div>
                        </td>
                        <td class="px-4 py-4 text-sm font-medium text-right whitespace-nowrap">
                            <div class="flex justify-center gap-2">
                                <button wire:click="view({{ $post->id }})"
                                    class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium transition-all disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg:not([class*='size-'])]:size-4 shrink-0 [&amp;_svg]:shrink-0 outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive active:scale-[.95] cursor-pointer hover:bg-accent hover:text-accent-foreground dark:hover:bg-accent/50 size-9 rounded-[50%] text-blue-500 bg-primary/10">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="lucide lucide-eye w-5 h-5">
                                        <path
                                            d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0">
                                        </path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                                <button wire:click="confirmDelete({{ $post->id }})"
                                    class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium transition-all disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg:not([class*='size-'])]:size-4 shrink-0 [&amp;_svg]:shrink-0 outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive active:scale-[.95] cursor-pointer hover:bg-accent hover:text-accent-foreground dark:hover:bg-accent/50 size-9 rounded-[50%] text-red-500 bg-red-500/10">
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
                        <td colspan="8" class="px-6 py-4 text-center text-zinc-500">
                            Nenhuma publicação encontrada
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <!-- Pagination -->
    <div
        class="flex items-center flex-col sm:flex-row justify-between border-t border-zinc-200 px-5 py-4 dark:border-zinc-800">
        {{ $posts->links('components.pagination.custom') }}
    </div>
</div>

{{-- View Modal --}}
@if($showViewModal && $selectedPost)
    <div class="fixed inset-0 bg-black/50 dark:bg-black/70 flex items-center justify-center z-50"
        wire:click="closeViewModal">
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto"
            wire:click.stop>
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">
                    Detalhes da {{ $selectedPost->type === 'poll' ? 'Enquete' : 'Publicação' }}
                </h3>
                <button wire:click="closeViewModal" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Autor</label>
                    <p class="text-zinc-900 dark:text-zinc-100">{{ optional($selectedPost->user)->name ?? 'Desconhecido' }}</p>
                </div>

                @if($selectedPost->title)
                    <div>
                        <label class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Título</label>
                        <p class="text-zinc-900 dark:text-zinc-100">{{ $selectedPost->title }}</p>
                    </div>
                @endif

                <div>
                    <label class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Conteúdo</label>
                    <p class="text-zinc-900 dark:text-zinc-100 whitespace-pre-line">{{ $selectedPost->content }}</p>
                </div>

                @if($selectedPost->type === 'poll')
                    <div>
                        <label class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Opções da Enquete</label>
                        <ul class="mt-2 space-y-2">
                            @forelse($selectedPost->pollOptions as $option)
                                <li class="flex items-center justify-between rounded-lg border border-zinc-200 dark:border-zinc-700 px-3 py-2">
                                    <span class="text-zinc-900 dark:text-zinc-100">{{ $option->option_text }}</span>
                                    <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ $option->votes_count }} votos</span>
                                </li>
                            @empty
                                <li class="text-sm text-zinc-500 dark:text-zinc-400">Nenhuma opção cadastrada.</li>
                            @endforelse
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Curtidas</label>
                        <p class="text-zinc-900 dark:text-zinc-100">{{ $selectedPost->likes()->count() }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Comentários</label>
                        <p class="text-zinc-900 dark:text-zinc-100">{{ $selectedPost->comments()->count() }}</p>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Criado em</label>
                    <p class="text-zinc-900 dark:text-zinc-100">{{ $selectedPost->created_at->format('d/m/Y H:i') }}</p>
                </div>
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
                        Tem certeza que deseja excluir esta publicação? Comentários, curtidas e votos associados também serão removidos. Esta ação não pode ser desfeita.
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
