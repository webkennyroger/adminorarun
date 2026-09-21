<div class="rounded-2xl px-5 pb-5 pt-5 sm:px-6 sm:pt-6" style="background-color: var(--bg-card); border: 1px solid var(--border-color);"
    x-data="{
        chart: null,
        initChart() {
            if (typeof ApexCharts === 'undefined') {
                console.error('ApexCharts não carregado!');
                return;
            }

            const usersData = @js($usersChartValues);
            const subscribersData = @js($subscribersChartValues);
            const chartLabels = @js($chartLabels);

            const options = {
                series: [
                    {
                        name: 'Usuários',
                        data: usersData,
                        color: '#4c4ee7'
                    },
                    {
                        name: 'Assinantes',
                        data: subscribersData,
                        color: '#0ea5e9'
                    }
                ],
                chart: {
                    type: 'bar',
                    height: 260,
                    toolbar: { show: false },
                    fontFamily: 'inherit',
                },
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        columnWidth: '55%',
                    }
                },
                dataLabels: { enabled: false },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                xaxis: {
                    categories: chartLabels,
                    labels: {
                        style: {
                            colors: '#9ca3af',
                            fontSize: '12px'
                        }
                    }
                },
                yaxis: {
                    labels: { show: false }
                },
                grid: {
                    borderColor: '#e5e7eb',
                    strokeDashArray: 0,
                },
                legend: {
                    show: true,
                    position: 'top',
                    horizontalAlign: 'right',
                    markers: {
                        width: 10,
                        height: 10,
                        radius: 2,
                    }
                },
                tooltip: {
                    y: {
                        formatter: function (val, { seriesIndex }) {
                            return val + (seriesIndex === 0 ? ' usuários' : ' assinantes');
                        }
                    }
                }
            };

            if (this.chart) {
                this.chart.destroy();
            }

            this.chart = new ApexCharts(document.querySelector('#userGrowthChart'), options);
            this.chart.render();
        }
    }" x-init="$nextTick(() => initChart())" @chart-update.window="initChart()">

    <!-- Header -->
    <div class="flex flex-col gap-5 mb-6 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-1 items-center justify-between space-x-2 sm:flex-initial">
            <h2 class="text-sm-plus font-medium tracking-wide" style="color: var(--text-primary);">
                Crescimento
            </h2>
        </div>

        <!-- Period Selector -->
        <div class="inline-flex items-center gap-0.5 rounded-lg p-0.5" style="background-color: var(--bg-elevated);">
            <button wire:click="$set('period', 'monthly')"
                class="px-3 py-2 font-medium rounded-md text-theme-sm transition-colors"
                style="{{ $period === 'monthly' ? 'background-color: var(--bg-card); color: var(--text-primary);' : 'color: var(--text-secondary);' }}">
                Mensal
            </button>
            <button wire:click="$set('period', 'quarterly')"
                class="px-3 py-2 font-medium rounded-md text-theme-sm transition-colors"
                style="{{ $period === 'quarterly' ? 'background-color: var(--bg-card); color: var(--text-primary);' : 'color: var(--text-secondary);' }}">
                Trimestral
            </button>
            <button wire:click="$set('period', 'yearly')"
                class="px-3 py-2 font-medium rounded-md text-theme-sm transition-colors"
                style="{{ $period === 'yearly' ? 'background-color: var(--bg-card); color: var(--text-primary);' : 'color: var(--text-secondary);' }}">
                Anual
            </button>
        </div>
    </div>

    <!-- Stats Display -->
    <div class="flex gap-4 sm:gap-9">
        <!-- Users Stats -->
        <div class="flex items-start gap-2">
            <div>
                <h4 class="mb-0.5 text-base font-bold sm:text-theme-xl" style="color: var(--text-primary);">
                    {{ number_format($currentPeriodUsers, 0, ',', '.') }}
                </h4>
                <span class="text-theme-xs" style="color: var(--text-secondary);">
                    Total Usuários
                    ({{ $period === 'monthly' ? 'Mês' : ($period === 'quarterly' ? 'Trimestre' : 'Ano') }})
                </span>
            </div>
            <x-ui.badge :variant="$usersGrowthPercentage >= 0 ? 'success' : 'danger'" size="sm" class="mt-1.5">
                {{ $usersGrowthPercentage >= 0 ? '+' : '' }}{{ number_format($usersGrowthPercentage, 1) }}%
            </x-ui.badge>
        </div>

        <!-- Subscribers Stats -->
        <div class="flex items-start gap-2">
            <div>
                <h4 class="mb-0.5 text-base font-bold sm:text-theme-xl" style="color: var(--text-primary);">
                    {{ number_format($currentPeriodSubscribers, 0, ',', '.') }}
                </h4>
                <span class="text-theme-xs" style="color: var(--text-secondary);">
                    Total Assinantes
                    ({{ $period === 'monthly' ? 'Mês' : ($period === 'quarterly' ? 'Trimestre' : 'Ano') }})
                </span>
            </div>
            <x-ui.badge :variant="$subscribersGrowthPercentage >= 0 ? 'info' : 'danger'" size="sm" class="mt-1.5">
                {{ $subscribersGrowthPercentage >= 0 ? '+' : '' }}{{ number_format($subscribersGrowthPercentage, 1) }}%
            </x-ui.badge>
        </div>
    </div>

    <!-- Chart -->
    <div class="max-w-full overflow-x-auto custom-scrollbar mt-6">
        <div id="userGrowthChart" class="-ml-4 min-w-[650px] pl-2 xl:min-w-full" style="min-height: 265px;"></div>
    </div>
</div>
