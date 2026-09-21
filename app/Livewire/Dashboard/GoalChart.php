<?php

namespace App\Livewire\Dashboard;

use App\Models\Goal;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Component;

class GoalChart extends Component
{
    public $chartType = 'users'; // Propriedade para o dropdown

    public $goalPercentage = 0;

    public $targetValue = 20000; // R$ 20K

    public $revenueValue = 15110; // R$ 15.11K (75.55% de 20K)

    public $todayValue = 3287; // R$ 3.287K

    public $growthPercentage = 10;

    public function mount()
    {
        $this->calculateGoalProgress();
    }

    public function updatedChartType()
    {
        // Quando mudar o tipo, recalcular
        $this->calculateGoalProgress();
    }

    private function calculateGoalProgress()
    {
        // Buscar a meta do mês atual para o tipo selecionado
        $currentMonth = Carbon::now()->startOfMonth();

        // Mapear o tipo do gráfico para a métrica do banco de dados
        $metricMap = [
            'users' => 'users',
            'sales' => 'sales',
            'expenses' => 'expenses',
            'revenue' => 'revenue',
        ];

        $metric = $metricMap[$this->chartType] ?? 'users';

        $goal = Goal::where('period', 'monthly')
            ->where('metric', $metric)
            ->latest('created_at')
            ->first();

        if ($goal) {
            $this->targetValue = $goal->target_value;
        } else {
            $this->targetValue = 0; // Sem meta definida
        }

        // Buscar valor atual (Real)
        $currentValue = $this->getCurrentValue($metric);

        // Atualizar valores para a view
        $this->revenueValue = $currentValue; // Reutilizando a var revenueValue como "Valor Atual" genérico

        // Calcular "Hoje" (Simulado ou Real se possível)
        $this->todayValue = $this->getTodayValue($metric);

        // Calcular Crescimento (Mês Atual vs Mês Passado)
        $lastMonthValue = $this->getLastMonthValue($metric);

        if ($lastMonthValue > 0) {
            $this->growthPercentage = (($currentValue - $lastMonthValue) / $lastMonthValue) * 100;
        } else {
            // Se mês passado foi 0 e este mês tem algo, crescimento é 100% (ou infinito).
            // Se ambos 0, é 0%.
            $this->growthPercentage = $currentValue > 0 ? 100 : 0;
        }

        // Calcular porcentagem da meta
        if ($this->targetValue > 0) {
            $this->goalPercentage = round(($currentValue / $this->targetValue) * 100, 2);
        } else {
            $this->goalPercentage = 0;
        }
    }

    private function getCurrentValue($metric)
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        switch ($metric) {
            case 'users':
                return User::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
            default:
                // Mock para outros tipos enquanto não existem as tabelas
                return 0;
        }
    }

    private function getLastMonthValue($metric)
    {
        $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();
        $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();

        switch ($metric) {
            case 'users':
                return User::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();
            default:
                return 0;
        }
    }

    private function getTodayValue($metric)
    {
        // Usar startOfDay para garantir que pegue desde o início do dia no timezone da aplicação
        $startOfDay = Carbon::now()->startOfDay();

        switch ($metric) {
            case 'users':
                return User::where('created_at', '>=', $startOfDay)->count();
            default:
                return 0; // Placeholder
        }
    }

    public function render()
    {
        return view('livewire.dashboard.charts.goal-chart');
    }
}
