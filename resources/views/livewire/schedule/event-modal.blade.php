<div>
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="modal-title"
             role="dialog"
             aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 backdrop-blur-sm" style="background-color: rgba(0, 0, 0, 0.5);"
                     wire:click="closeModal" aria-hidden="true"></div>

                <!-- Modal panel -->
                <div class="inline-block align-bottom rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                     style="background-color: var(--bg-surface); border: 1px solid var(--border-color);">
                    @if($confirmingDeletion)
                        <div class="p-6">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="flex-shrink-0 w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold" style="color: var(--text-primary);">Confirmar Exclusão</h3>
                                    <p class="text-sm mt-1" style="color: var(--text-secondary);">
                                        Tem certeza que deseja excluir este evento? Esta ação não pode ser desfeita.
                                    </p>
                                </div>
                            </div>

                            <div class="flex justify-end gap-3 pt-5">
                                <x-ui.button type="button" variant="secondary" wire:click="cancelDelete">
                                    Cancelar
                                </x-ui.button>
                                <x-ui.button variant="danger" wire:click="deleteEvent">
                                    Sim, Excluir
                                </x-ui.button>
                            </div>
                        </div>
                    @else
                        <form wire:submit.prevent="saveEvent">
                            <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="mb-4">
                                    <h3 class="text-lg font-medium leading-6" style="color: var(--text-primary);" id="modal-title">
                                        {{ $editMode ? 'Editar Evento' : 'Criar Novo Evento' }}
                                    </h3>
                                </div>

                                <!-- Title -->
                                <div class="mb-4">
                                    <x-ui.field label="Título" :error="$errors->first('title')">
                                        <x-ui.input type="text" id="title" wire:model.defer="title" placeholder="Nome do evento" />
                                    </x-ui.field>
                                </div>

                                <!-- Description -->
                                <div class="mb-4">
                                    <x-ui.field label="Descrição" :error="$errors->first('description')">
                                        <x-ui.textarea id="description" wire:model.defer="description" rows="4" placeholder="Descrição do evento" />
                                    </x-ui.field>
                                </div>

                                <!-- Date and Time -->
                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <x-form.date-picker
                                            label="Data"
                                            name="event_date"
                                            id="event_date"
                                            placeholder="Selecione a data"
                                            :default-date="$event_date"
                                            x-on:date-change="$wire.set('event_date', $event.detail.dateStr)"
                                        />
                                        <div class="mt-1">
                                            @error('event_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div>
                                        <x-form.date-picker
                                            label="Hora"
                                            name="event_time"
                                            id="event_time"
                                            mode="time"
                                            dateFormat="H:i"
                                            placeholder="Selecione a hora"
                                            :default-date="$event_time"
                                            x-on:date-change="$wire.set('event_time', $event.detail.dateStr)"
                                        />
                                        <div class="mt-1">
                                            @error('event_time') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Color Picker -->
                                <div class="mb-4">
                                    <x-ui.field label="Cor do Evento" :error="$errors->first('color')">
                                        <div class="flex items-center gap-3">
                                            <input type="color"
                                                   id="color"
                                                   wire:model.defer="color"
                                                   class="h-10 w-20 cursor-pointer rounded border border-gray-300 dark:border-zinc-700">
                                            <span class="text-sm" style="color: var(--text-secondary);">{{ $color }}</span>
                                        </div>
                                    </x-ui.field>
                                </div>

                                <!-- Photo Upload -->
                                <div class="mb-4">
                                    <x-ui.field label="Foto do Evento" :error="$errors->first('photo')">
                                        @if($existingPhoto && !$photo)
                                            <div class="mb-3">
                                                <img src="{{ asset('storage/' . $existingPhoto) }}"
                                                     alt="Foto atual"
                                                     class="h-32 w-32 object-cover rounded-md border border-gray-300 dark:border-zinc-700">
                                                <p class="text-xs mt-1" style="color: var(--text-muted);">Foto atual</p>
                                            </div>
                                        @endif

                                        @if($photo)
                                            <div class="mb-3">
                                                <img src="{{ $photo->temporaryUrl() }}"
                                                     alt="Preview"
                                                     class="h-32 w-32 object-cover rounded-md border border-gray-300 dark:border-zinc-700">
                                                <p class="text-xs mt-1" style="color: var(--text-muted);">Nova foto</p>
                                            </div>
                                        @endif

                                        <x-form.file-input wire:model="photo" id="event_photo" accept="image/*" />

                                        <div wire:loading wire:target="photo" class="text-sm text-blue-500 mt-1">
                                            Carregando...
                                        </div>
                                    </x-ui.field>
                                </div>
                            </div>

                            <!-- Modal Actions -->
                            <div class="px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2" style="background-color: var(--bg-elevated); border-top: 1px solid var(--border-color);">
                                <x-ui.button type="submit" class="w-full sm:w-auto !justify-center">
                                    {{ $editMode ? 'Atualizar' : 'Criar' }}
                                </x-ui.button>

                                @if($editMode)
                                    <x-ui.button type="button" variant="danger" wire:click="confirmDelete" class="w-full sm:w-auto !justify-center">
                                        Excluir
                                    </x-ui.button>
                                @endif

                                <x-ui.button type="button" variant="secondary" wire:click="closeModal" class="mt-3 sm:mt-0 w-full sm:w-auto !justify-center">
                                    Cancelar
                                </x-ui.button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
