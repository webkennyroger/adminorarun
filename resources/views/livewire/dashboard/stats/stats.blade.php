<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-5 gap-6">
    <!-- Total Users -->
    <x-ui.card>
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium" style="color: var(--text-secondary);">
                    Total de usuários
                </p>
                <h3 class="text-2xl font-bold mt-1" style="color: var(--text-primary);">
                    {{ number_format($totalUsers, 0, ',', '.') }}
                </h3>
            </div>
            <div class="w-12 h-12 bg-cyan-600 rounded-full flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-users-round text-white">
                    <path d="M18 21a8 8 0 0 0-16 0"></path>
                    <circle cx="10" cy="8" r="5"></circle>
                    <path d="M22 20c0-3.37-2-6.5-4-8a5 5 0 0 0-.45-8.3"></path>
                </svg>
            </div>
        </div>
        <div class="flex items-center gap-2 mt-4">
            @if(isset($activeGoals['users']))
                <x-ui.badge variant="default" size="sm">
                    Meta: {{ number_format($activeGoals['users']->target_value, 0, ',', '.') }}
                </x-ui.badge>
            @else
                <x-ui.badge variant="default" size="sm">
                    Sem meta definida
                </x-ui.badge>
            @endif
        </div>
    </x-ui.card>

    <!-- Premium Users (Assinatura Total) -->
    <x-ui.card>
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium" style="color: var(--text-secondary);">
                    Assinatura total
                </p>
                <h3 class="text-2xl font-bold mt-1" style="color: var(--text-primary);">
                    {{ number_format($premiumUsers, 0, ',', '.') }}
                </h3>
            </div>
            <div class="w-12 h-12 bg-purple-600 rounded-full flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-medal text-white">
                    <path
                        d="M7.21 15 2.66 7.14a2 2 0 0 1 .13-2.2L4.4 2.8A2 2 0 0 1 6 2h12a2 2 0 0 1 1.6.8l1.6 2.14a2 2 0 0 1 .14 2.2L16.79 15">
                    </path>
                    <path d="M11 12 5.12 2.2"></path>
                    <path d="m13 12 5.88-9.8"></path>
                    <path d="M8 7h8"></path>
                    <circle cx="12" cy="17" r="5"></circle>
                    <path d="M12 18v-2h-.5"></path>
                </svg>
            </div>
        </div>
        <div class="flex items-center gap-2 mt-4">
            @if(isset($activeGoals['sales']))
                <x-ui.badge variant="default" size="sm">
                    Meta Vendas: {{ number_format($activeGoals['sales']->target_value, 0, ',', '.') }}
                </x-ui.badge>
            @else
                <x-ui.badge variant="default" size="sm">
                    Sem meta de vendas
                </x-ui.badge>
            @endif
        </div>
    </x-ui.card>

    <!-- Free Users -->
    <x-ui.card>
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium" style="color: var(--text-secondary);">
                    Total de usuários gratuitos
                </p>
                <h3 class="text-2xl font-bold mt-1" style="color: var(--text-primary);">
                    {{ number_format($freeUsers, 0, ',', '.') }}
                </h3>
            </div>
            <div class="w-12 h-12 bg-zinc-400 rounded-full flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-users-round text-white">
                    <path d="M18 21a8 8 0 0 0-16 0"></path>
                    <circle cx="10" cy="8" r="5"></circle>
                    <path d="M22 20c0-3.37-2-6.5-4-8a5 5 0 0 0-.45-8.3"></path>
                </svg>
            </div>
        </div>
        <div class="flex items-center gap-2 mt-4">
            <x-ui.badge variant="default" size="sm">
                Plano Gratuito
            </x-ui.badge>
        </div>
    </x-ui.card>

    <!-- Revenue (Renda) -->
    <x-ui.card>
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium" style="color: var(--text-secondary);">
                    Renda total
                </p>
                <!-- Placeholder for actual revenue, assuming 0 for now as no transaction table -->
                <h3 class="text-2xl font-bold mt-1" style="color: var(--text-primary);">
                    R$ 0,00
                </h3>
            </div>
            <div class="w-12 h-12 bg-emerald-600 rounded-full flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-wallet text-white">
                    <path
                        d="M19 7V4a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4h15a1 1 0 0 1 1 1v4h-3a2 2 0 0 0 0 4h3a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1">
                    </path>
                    <path d="M3 5v14a2 2 0 0 0 2 2h15a1 1 0 0 0 1-1v-4"></path>
                </svg>
            </div>
        </div>
        <div class="flex items-center gap-2 mt-4">
            @if(isset($activeGoals['revenue']))
                <x-ui.badge variant="default" size="sm">
                    Meta: R$ {{ number_format($activeGoals['revenue']->target_value, 2, ',', '.') }}
                </x-ui.badge>
            @else
                <x-ui.badge variant="default" size="sm">
                    Sem meta de renda
                </x-ui.badge>
            @endif
        </div>
    </x-ui.card>

    <!-- Expenses (Despesa) -->
    <x-ui.card>
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium" style="color: var(--text-secondary);">
                    Despesa total
                </p>
                <!-- Placeholder -->
                <h3 class="text-2xl font-bold mt-1" style="color: var(--text-primary);">
                    R$ 0,00
                </h3>
            </div>
            <div class="w-12 h-12 bg-red-600 rounded-full flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-file-text text-white">
                    <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
                    <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                    <path d="M10 9H8"></path>
                    <path d="M16 13H8"></path>
                    <path d="M16 17H8"></path>
                </svg>
            </div>
        </div>
        <div class="flex items-center gap-2 mt-4">
            @if(isset($activeGoals['expenses']))
                <x-ui.badge variant="default" size="sm">
                    Meta: R$ {{ number_format($activeGoals['expenses']->target_value, 2, ',', '.') }}
                </x-ui.badge>
            @else
                <x-ui.badge variant="default" size="sm">
                    Sem meta de despesas
                </x-ui.badge>
            @endif
        </div>
    </x-ui.card>
</div>
