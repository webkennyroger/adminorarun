<div wire:ignore>
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <x-common.page-breadcrumb pageTitle="Calendário" />
        <x-ui.button variant="primary" class="btn-create-main">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Novo Evento
        </x-ui.button>
    </div>

    <x-ui.card :padding="false">
        <div id="calendar" class="p-4 min-h-[600px]"></div>
    </x-ui.card>

    <!-- Modal de Criar/Editar Evento -->
    <div id="eventModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50"
        style="display: none;">
        <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-xl w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 id="eventModalLabel" class="text-lg font-semibold text-zinc-900 dark:text-white">Adicionar
                        Evento</h3>
                    <button type="button"
                        class="modal-close-btn text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <!-- Título do Evento -->
                    <div>
                        <label for="event-title"
                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                            Título do Evento <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="event-title"
                            class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-700 rounded-lg dark:bg-zinc-800 dark:text-white"
                            placeholder="Digite o título do evento">
                    </div>

                    <!-- Descrição -->
                    <div>
                        <label for="event-description"
                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                            Descrição
                        </label>
                        <textarea id="event-description"
                            class="w-full h-32 px-3 py-2 border border-zinc-300 dark:border-zinc-700 rounded-lg dark:bg-zinc-800 dark:text-white resize-none"
                            placeholder="Descrição do evento"></textarea>
                    </div>

                    <!-- Data de Início -->
                    <div>
                        <x-form.date-picker label="Data *" name="event-start-date" id="event-start-date"
                            placeholder="Selecione a data" />
                    </div>

                    <!-- Hora -->
                    <div>
                        <x-form.date-picker label="Hora" name="event-time" id="event-time" mode="time" dateFormat="H:i"
                            placeholder="Selecione a hora" />
                    </div>

                    <!-- Tipo de Evento (Cor) -->
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                            Tipo de Evento
                        </label>
                        <div class="flex gap-4 flex-wrap">
                            <label class="flex items-center">
                                <input type="radio" name="event-level" value="Danger" class="mr-2">
                                <span class="text-sm text-red-600">Urgente</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="event-level" value="Success" class="mr-2">
                                <span class="text-sm text-green-600">Sucesso</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="event-level" value="Primary" class="mr-2" checked>
                                <span class="text-sm text-blue-600">Normal</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="event-level" value="Warning" class="mr-2">
                                <span class="text-sm text-yellow-600">Aviso</span>
                            </label>
                        </div>
                    </div>

                    <!-- Foto do Evento -->
                    <div>
                        <label for="event-photo"
                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                            Foto do Evento
                        </label>
                        <input type="file" id="event-photo" accept="image/*"
                            class="w-full text-sm text-zinc-600 dark:text-zinc-400">
                    </div>
                </div>

                <!-- Ações do Modal -->
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button"
                        class="modal-close-btn px-4 py-2 bg-zinc-200 text-zinc-800 rounded-lg hover:bg-zinc-300 dark:bg-zinc-700 dark:text-zinc-200">
                        Cancelar
                    </button>
                    <button type="button"
                        class="btn-add-event px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Adicionar Evento
                    </button>
                    <button type="button"
                        class="btn-update-event px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
                        style="display: none;">
                        Atualizar Evento
                    </button>
                    <button type="button"
                        class="btn-delete-event px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 flex items-center gap-2"
                        style="display: none;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                            </path>
                        </svg>
                        Excluir
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Visualização de Evento -->
    <div id="viewEventModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50"
        style="display: none;">
        <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-xl w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Detalhes do Evento</h3>
                    <button type="button"
                        class="view-modal-close-btn text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <div>
                        <h4 id="view-event-title" class="text-xl font-bold text-zinc-900 dark:text-white"></h4>
                    </div>

                    <div id="view-event-description-container" class="hidden">
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Descrição</label>
                        <p id="view-event-description" class="text-zinc-600 dark:text-zinc-400"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Data</label>
                        <p id="view-event-date" class="text-zinc-600 dark:text-zinc-400"></p>
                    </div>
                </div>

                <!-- Ações do Modal de Visualização -->
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button"
                        class="view-modal-close-btn px-4 py-2 bg-zinc-200 text-zinc-800 rounded-lg hover:bg-zinc-300 dark:bg-zinc-700 dark:text-zinc-200">
                        Fechar
                    </button>
                    <button type="button"
                        class="btn-edit-from-view px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                            </path>
                        </svg>
                        Editar
                    </button>
                    <button type="button"
                        class="btn-delete-event-view px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                            </path>
                        </svg>
                        Excluir
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    <div id="confirmDeleteModal" class="fixed inset-0 z-60 hidden items-center justify-center bg-black/50"
        style="display: none;">
        <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-xl w-full max-w-sm mx-4">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">Confirmar Exclusão</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-6">
                    Tem certeza que deseja excluir este evento? Esta ação não pode ser desfeita.
                </p>
                <div class="flex justify-end gap-3">
                    <button type="button"
                        class="cancel-delete-btn px-4 py-2 bg-zinc-200 text-zinc-800 rounded-lg hover:bg-zinc-300 dark:bg-zinc-700 dark:text-zinc-200">
                        Cancelar
                    </button>
                    <button type="button"
                        class="confirm-delete-btn px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Sim, Excluir
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


</div>