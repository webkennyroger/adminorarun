<?php

namespace Database\Seeders;

use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating 10 Schedule Events...');

        $users = User::where('email', '!=', 'webkennyroger@gmail.com')->inRandomOrder()->take(10)->get();

        if ($users->isEmpty()) {
            $users = User::factory(1)->create();
        }

        $events = [
            ['title' => 'Treino Matinal', 'description' => 'Corrida leve no parque', 'days' => 1, 'time' => '07:00:00', 'color' => 'Primary'],
            ['title' => 'Live de Yoga', 'description' => 'Sessão de yoga transmitida ao vivo para os assinantes', 'days' => 2, 'time' => '18:00:00', 'color' => 'Success'],
            ['title' => 'Início do Desafio 30 Dias', 'description' => 'Abertura oficial do Desafio 30 Dias de Corrida', 'days' => 3, 'time' => '06:00:00', 'color' => 'Warning'],
            ['title' => 'Manutenção Programada', 'description' => 'App pode ficar instável entre 00h e 02h', 'days' => 5, 'time' => '00:00:00', 'color' => 'Danger'],
            ['title' => 'Encontro Corredores da Cidade', 'description' => 'Corrida em grupo saindo do parque Ibirapuera', 'days' => -2, 'time' => '08:00:00', 'color' => 'Primary'],
            ['title' => 'Pedal em Grupo', 'description' => 'Saída de bike com o clube Pedal em Grupo', 'days' => -5, 'time' => '07:30:00', 'color' => 'Success'],
            ['title' => 'Webinar de Nutrição', 'description' => 'Dicas de alimentação para corredores', 'days' => 7, 'time' => '19:00:00', 'color' => 'Warning'],
            ['title' => 'Fechamento Mensal de Metas', 'description' => 'Revisão das metas do mês com a equipe', 'days' => 10, 'time' => '10:00:00', 'color' => 'Primary'],
            ['title' => 'Trilha do Trail Runners', 'description' => 'Trilha em grupo na Serra da Cantareira', 'days' => -8, 'time' => '06:30:00', 'color' => 'Success'],
            ['title' => 'Lançamento de Novo Plano', 'description' => 'Divulgação de um novo plano de assinatura', 'days' => 14, 'time' => '09:00:00', 'color' => 'Danger'],
        ];

        foreach ($events as $index => $data) {
            Schedule::create([
                'user_id' => $users[$index % $users->count()]->id,
                'title' => $data['title'],
                'description' => $data['description'],
                'event_date' => now()->addDays($data['days'])->toDateString(),
                'event_time' => $data['time'],
                'color' => $data['color'],
            ]);
        }

        $this->command->info('✅ 10 Schedule Events created (past and upcoming).');
    }
}
