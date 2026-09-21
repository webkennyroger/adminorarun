<?php

namespace Database\Seeders;

use App\Models\Goal;
use Illuminate\Database\Seeder;

class GoalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating 10 Goals...');

        $thisMonth = [now()->startOfMonth(), now()->endOfMonth()];
        $thisWeek = [now()->startOfWeek(), now()->endOfWeek()];
        $thisYear = [now()->startOfYear(), now()->endOfYear()];

        // 'metric' is validated against users|sales|expenses|revenue in GoalIndex — keep seeds within that set.
        $goals = [
            ['title' => 'Meta de Novos Usuários (Mês)', 'metric' => 'users', 'target_value' => 100, 'period' => 'monthly', 'range' => $thisMonth],
            ['title' => 'Meta de Vendas (Mês)', 'metric' => 'sales', 'target_value' => 50, 'period' => 'monthly', 'range' => $thisMonth],
            ['title' => 'Meta de Receita (Mês)', 'metric' => 'revenue', 'target_value' => 10000, 'period' => 'monthly', 'range' => $thisMonth],
            ['title' => 'Novos Usuários (Semana)', 'metric' => 'users', 'target_value' => 25, 'period' => 'weekly', 'range' => $thisWeek],
            ['title' => 'Vendas da Semana', 'metric' => 'sales', 'target_value' => 12, 'period' => 'weekly', 'range' => $thisWeek],
            ['title' => 'Controle de Despesas (Mês)', 'metric' => 'expenses', 'target_value' => 3000, 'period' => 'monthly', 'range' => $thisMonth],
            ['title' => 'Receita da Semana', 'metric' => 'revenue', 'target_value' => 2500, 'period' => 'weekly', 'range' => $thisWeek],
            ['title' => 'Novos Usuários (Ano)', 'metric' => 'users', 'target_value' => 1200, 'period' => 'yearly', 'range' => $thisYear],
            ['title' => 'Despesas Anuais', 'metric' => 'expenses', 'target_value' => 36000, 'period' => 'yearly', 'range' => $thisYear],
            ['title' => 'Receita Anual', 'metric' => 'revenue', 'target_value' => 120000, 'period' => 'yearly', 'range' => $thisYear],
        ];

        foreach ($goals as $goal) {
            [$start, $end] = $goal['range'];

            Goal::create([
                'title' => $goal['title'],
                'metric' => $goal['metric'],
                'target_value' => $goal['target_value'],
                'period' => $goal['period'],
                'start_date' => $start,
                'end_date' => $end,
            ]);
        }

        $this->command->info('✅ 10 Goals created.');
    }
}
