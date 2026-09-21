<?php

namespace Database\Seeders;

use App\Models\Support;
use App\Models\User;
use Illuminate\Database\Seeder;

class SupportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating 10 Support Tickets...');

        $users = User::where('email', '!=', 'webkennyroger@gmail.com')->inRandomOrder()->take(10)->get();

        if ($users->isEmpty()) {
            $users = User::factory(1)->create();
        }

        $tickets = [
            ['subject' => 'Problema com login', 'message' => 'Não consigo acessar minha conta premium.', 'status' => 'open', 'priority' => 'high'],
            ['subject' => 'Dúvida sobre planos', 'message' => 'Quais as formas de pagamento aceitas?', 'status' => 'pending', 'priority' => 'medium'],
            ['subject' => 'Cobrança em duplicidade', 'message' => 'Fui cobrado duas vezes esse mês pela assinatura anual.', 'status' => 'open', 'priority' => 'high'],
            ['subject' => 'App travando ao abrir desafios', 'message' => 'Sempre que abro a tela de desafios o app fecha.', 'status' => 'pending', 'priority' => 'high'],
            ['subject' => 'Como alterar meu nome de usuário?', 'message' => 'Gostaria de saber onde altero meu @usuario.', 'status' => 'resolved', 'priority' => 'low'],
            ['subject' => 'Segmento não está registrando tempo', 'message' => 'Corri o segmento do parque mas não apareceu no ranking.', 'status' => 'pending', 'priority' => 'medium'],
            ['subject' => 'Solicitação de exclusão de conta', 'message' => 'Quero excluir minha conta e todos os meus dados.', 'status' => 'closed', 'priority' => 'medium'],
            ['subject' => 'Notificações não chegam', 'message' => 'Parei de receber notificações de novos desafios.', 'status' => 'resolved', 'priority' => 'low'],
            ['subject' => 'Erro ao fazer upload de foto no clube', 'message' => 'A imagem do clube não salva, fica girando para sempre.', 'status' => 'open', 'priority' => 'medium'],
            ['subject' => 'Sugestão: filtro por distância nos clubes', 'message' => 'Seria ótimo filtrar clubes por distância da minha localização.', 'status' => 'closed', 'priority' => 'low'],
        ];

        $createdTickets = [];

        foreach ($tickets as $index => $data) {
            $ticket = Support::create([
                'user_id' => $users[$index % $users->count()]->id,
                ...$data,
            ]);

            $createdTickets[] = $ticket;
        }

        // Add a reply thread to a couple of tickets so the ticket-detail view has real conversation history.
        $adminReplier = User::where('email', '!=', 'webkennyroger@gmail.com')->first();

        foreach (array_slice($createdTickets, 0, 3) as $ticket) {
            $ticket->replies()->create([
                'user_id' => $ticket->user_id,
                'message' => 'Alguma novidade sobre isso?',
            ]);

            if ($ticket->status !== 'open' && $adminReplier) {
                $ticket->replies()->create([
                    'user_id' => $adminReplier->id,
                    'message' => 'Olá! Já estamos verificando e retornamos em breve.',
                ]);
            }
        }

        $this->command->info('✅ 10 Support Tickets created with reply threads.');
    }
}
