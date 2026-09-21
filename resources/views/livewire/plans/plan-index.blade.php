<div>
    <x-common.page-breadcrumb title="Planos de Assinatura" />

    <!-- Header -->
    <div class="flex flex-col gap-2 pb-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3 text-theme-sm" style="color: var(--text-secondary);">
            <span>Mostrar</span>
            <span>Planos</span>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <x-ui.button wire:click="create" variant="primary" size="sm" icon="plus">
                Novo Plano
            </x-ui.button>
        </div>
    </div>

    <!-- Table -->
    <x-ui.data-table striped hoverable>
        <x-slot:header>
            <th scope="col" class="px-4 py-3 font-normal text-start text-theme-sm">Nome</th>
            <th scope="col" class="px-4 py-3 font-normal text-start text-theme-sm">Preço</th>
            <th scope="col" class="px-4 py-3 font-normal text-start text-theme-sm">Período</th>
            <th scope="col" class="px-4 py-3 font-normal text-start text-theme-sm">Stripe ID</th>
            <th scope="col" class="px-4 py-3 font-normal text-center text-theme-sm">Status</th>
            <th scope="col" class="px-4 py-3 font-normal text-center text-theme-sm">Ações</th>
        </x-slot:header>

        @forelse($plans as $plan)
            <tr wire:key="{{ $plan->id }}">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium" style="color: var(--text-primary);">{{ $plan->name }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-emerald-500">{{ $plan->formatted_price }}</div>
                </td>
                <td class="px-4 py-4 whitespace-nowrap">
                    <div class="text-sm" style="color: var(--text-secondary);">
                        {{ ucfirst($plan->billing_period) }}
                    </div>
                </td>
                <td class="px-4 py-4 whitespace-nowrap">
                    <div class="text-xs font-mono" style="color: var(--text-secondary);">
                        {{ $plan->stripe_plan_id }}
                    </div>
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-center">
                    @if($plan->is_active)
                        <x-ui.badge variant="success" dot>Ativo</x-ui.badge>
                    @else
                        <x-ui.badge variant="danger" dot>Inativo</x-ui.badge>
                    @endif
                </td>
                <td class="px-4 py-4 text-sm font-medium text-center whitespace-nowrap">
                    <div class="flex justify-center gap-2">
                        <x-ui.button wire:click="edit({{ $plan->id }})" variant="ghost" size="xs" icon="pencil" />
                        <x-ui.button wire:click="confirmDelete({{ $plan->id }})" variant="danger" size="xs"
                            icon="trash" />
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6">
                    <x-ui.empty-state title="Nenhum plano encontrado"
                        description="Cadastre um novo plano de assinatura para começar." />
                </td>
            </tr>
        @endforelse
    </x-ui.data-table>

    {{-- Create / Edit Modal --}}
    @if($showModal)
        <x-ui.modal name="plan" maxWidth="lg" :open="true">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold" style="color: var(--text-primary);">
                    {{ $isEditMode ? 'Editar Plano' : 'Novo Plano' }}
                </h3>
                <button type="button" wire:click="closeModal" style="color: var(--text-secondary);">
                    <x-ui.icon name="x-mark" class="w-6 h-6" />
                </button>
            </div>

            <form wire:submit="{{ $isEditMode ? 'update' : 'store' }}" class="space-y-6">
                <x-ui.input label="Nome do Plano *" wire:model="name" :error="$errors->first('name')" />

                <x-ui.input label="Preço (em centavos) *" type="number" wire:model="price"
                    :error="$errors->first('price')" />

                <x-ui.input label="Stripe ID Plan *" wire:model="stripe_plan_id"
                    :error="$errors->first('stripe_plan_id')" />

                <x-ui.select label="Período de Cobrança *" wire:model="billing_period" :placeholder="null"
                    :error="$errors->first('billing_period')">
                    <option value="monthly">Mensal</option>
                    <option value="yearly">Anual</option>
                </x-ui.select>

                <x-ui.textarea label="Funcionalidades" wire:model="features" rows="3"
                    :error="$errors->first('features')" />

                <x-ui.checkbox wire:model="is_active" id="modal_is_active" label="Plano Ativo" />

                <div class="flex justify-end gap-3 pt-4" style="border-top: 1px solid var(--border-color);">
                    <x-ui.button type="button" wire:click="closeModal" variant="secondary">
                        Cancelar
                    </x-ui.button>
                    <x-ui.button type="submit" variant="primary">
                        {{ $isEditMode ? 'Salvar Alterações' : 'Criar Plano' }}
                    </x-ui.button>
                </div>
            </form>
        </x-ui.modal>
    @endif

    {{-- Modal de Confirmação de Exclusão --}}
    @if($confirmingDeletion)
        <x-ui.modal name="plan-delete" maxWidth="md" :open="true">
            <div class="flex items-center gap-4 mb-4">
                <div class="flex shrink-0 items-center justify-center w-12 h-12 rounded-full bg-red-500/10">
                    <x-ui.icon name="exclamation-triangle" class="w-6 h-6 text-red-500" />
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold" style="color: var(--text-primary);">Confirmar Exclusão</h3>
                    <p class="text-sm mt-1" style="color: var(--text-secondary);">
                        Tem certeza que deseja excluir o plano "<strong>{{ $planToDelete?->name }}</strong>"?
                    </p>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-5">
                <x-ui.button type="button" wire:click="$set('confirmingDeletion', false)" variant="secondary">
                    Cancelar
                </x-ui.button>
                <x-ui.button wire:click="delete" variant="danger">
                    Sim, Excluir
                </x-ui.button>
            </div>
        </x-ui.modal>
    @endif
</div>
