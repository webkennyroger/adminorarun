<?php

namespace Database\Seeders;

use App\Models\Challenge;
use App\Models\ChatGroup;
use App\Models\Club;
use App\Models\Comment;
use App\Models\GroupMessage;
use App\Models\Message;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\Post;
use App\Models\Report;
use App\Models\Segment;
use App\Models\SegmentEffort;
use App\Models\TrainingPlan;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Populates the parts of the app that a real day-to-day user would touch but
 * that the other seeders leave empty: club memberships, challenge/training
 * plan enrollments with real progress, a few posts, DM threads, and a couple
 * of segments with efforts so the leaderboard isn't empty.
 */
class DemoUsageSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('email', '!=', 'webkennyroger@gmail.com')->inRandomOrder()->get();

        if ($users->count() < 5) {
            $this->command->warn('Not enough users to seed demo usage data — skipping.');

            return;
        }

        $this->seedClubs($users);
        $this->seedChallengeEnrollments($users);
        $this->seedTrainingPlanEnrollment($users);
        $this->seedPosts($users);
        $this->seedPoll($users);
        $this->seedComments($users);
        $this->seedMessages($users);
        $this->seedGroupChat($users);
        $this->seedSegments($users);
        $this->seedReports($users);
        $this->seedInactiveUser($users);
    }

    private function seedClubs($users): void
    {
        $clubs = [
            ['name' => 'Corredores da Cidade', 'category' => 'running', 'description' => 'Grupo de corrida para quem ama a rua e a rotina.'],
            ['name' => 'Trail Runners', 'category' => 'trail', 'description' => 'Trilhas, montanhas e muita aventura.'],
            ['name' => 'Pedal em Grupo', 'category' => 'cycling', 'description' => 'Saídas de bike toda semana, todo nível.'],
            ['name' => 'Caminhada e Bem-Estar', 'category' => 'walking', 'description' => 'Caminhadas leves com foco em saúde e conversa.'],
        ];

        foreach ($clubs as $index => $data) {
            $creator = $users[$index % $users->count()];

            $club = Club::create([
                ...$data,
                'city' => 'São Paulo',
                'state' => 'SP',
                'is_public' => true,
                'creator_id' => $creator->id,
                'creator_name' => $creator->name,
            ]);

            $club->members()->attach($creator->id, ['role' => 'creator']);

            $members = $users->where('id', '!=', $creator->id)->random(min(6, $users->count() - 1));
            foreach ($members as $member) {
                $club->members()->syncWithoutDetaching([$member->id => ['role' => 'member']]);
            }

            $club->update(['members_count' => $club->members()->count()]);
        }

        $this->command->info('Clubs seeded: '.count($clubs));
    }

    private function seedChallengeEnrollments($users): void
    {
        $challenges = Challenge::all();
        if ($challenges->isEmpty()) {
            return;
        }

        $enrollments = 0;
        foreach ($challenges as $challenge) {
            $participants = $users->random(min(8, $users->count()));
            foreach ($participants as $user) {
                $progress = fake()->randomFloat(2, 0, (float) $challenge->goal_km);
                $status = $progress >= $challenge->goal_km ? 'completed' : 'joined';

                $challenge->users()->syncWithoutDetaching([
                    $user->id => ['progress' => $progress, 'status' => $status],
                ]);
                $enrollments++;
            }
        }

        $this->command->info("Challenge enrollments seeded: {$enrollments}");
    }

    private function seedTrainingPlanEnrollment($users): void
    {
        $plans = TrainingPlan::all();
        if ($plans->isEmpty()) {
            return;
        }

        $enrollments = 0;
        foreach ($plans as $index => $plan) {
            $participants = $users->random(min(4, $users->count()));
            foreach ($participants as $i => $user) {
                // Alguns bem no início, outros na metade, um já concluído.
                $currentWeek = min($plan->weeks, max(1, intdiv($i + 1, 2) + 1));
                $currentDay = fake()->numberBetween(1, 7);
                $status = $currentWeek >= $plan->weeks && $i === 0 ? 'completed' : 'active';

                $plan->users()->syncWithoutDetaching([
                    $user->id => [
                        'started_at' => now()->subWeeks($currentWeek),
                        'current_week' => $currentWeek,
                        'current_day' => $currentDay,
                        'status' => $status,
                    ],
                ]);
                $enrollments++;
            }
        }

        $this->command->info("Training plan enrollments seeded: {$enrollments}");
    }

    private function seedPosts($users): void
    {
        $captions = [
            'Fechei meu treino de hoje! 💪',
            'Alguém topa uma corrida no fim de semana?',
            'Nova meta batida esse mês 🎉',
            'Dica de hidratação para os dias mais quentes.',
            'Que treino difícil hoje, mas valeu a pena!',
        ];

        foreach ($captions as $index => $content) {
            Post::create([
                'user_id' => $users[$index % $users->count()]->id,
                'content' => $content,
                'type' => 'post',
                'privacy' => 'public',
                'feed_type' => 'personal',
                'media' => [],
                'meta' => [],
            ]);
        }

        $this->command->info('Posts seeded: '.count($captions));
    }

    private function seedMessages($users): void
    {
        $pairs = 0;
        for ($i = 0; $i < 4 && $i + 1 < $users->count(); $i += 2) {
            $sender = $users[$i];
            $receiver = $users[$i + 1];

            Message::create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'content' => 'Bora treinar junto essa semana?',
                'read_at' => null,
            ]);
            Message::create([
                'sender_id' => $receiver->id,
                'receiver_id' => $sender->id,
                'content' => 'Bora! Que dia fica melhor pra você?',
                'read_at' => now(),
            ]);
            $pairs++;
        }

        $this->command->info("Message threads seeded: {$pairs}");
    }

    private function seedSegments($users): void
    {
        $segment = Segment::create([
            'name' => 'Volta do Parque Ibirapuera',
            'sport_type' => 'run',
            'start_lat' => -23.5874,
            'start_lng' => -46.6576,
            'end_lat' => -23.5877,
            'end_lng' => -46.6633,
            'radius_m' => 40,
            'created_by' => $users->first()->id,
        ]);

        $participants = $users->random(min(6, $users->count()));
        foreach ($participants as $user) {
            $activity = $user->activities()->inRandomOrder()->first()
                ?? $user->activities()->create([
                    'title' => 'Corrida no parque',
                    'sport_type' => 'run',
                    'start_time' => now()->subDays(fake()->numberBetween(1, 30)),
                    'distance' => 5000,
                    'duration' => 1800,
                    'calories' => 400,
                    'privacy' => 'public',
                    'feed_type' => 'personal',
                ]);

            SegmentEffort::updateOrCreate(
                ['segment_id' => $segment->id, 'activity_id' => $activity->id],
                [
                    'user_id' => $user->id,
                    'duration_seconds' => fake()->numberBetween(240, 420),
                    'achieved_at' => $activity->start_time ?? now(),
                ]
            );
        }

        $this->command->info('Segments seeded: 1 (with '.$participants->count().' efforts)');

        // A couple more segments so the leaderboard/list isn't just one entry.
        $extraSegments = [
            ['name' => 'Subida da Av. Paulista', 'sport_type' => 'ride', 'lat' => -23.5613, 'lng' => -46.6565],
            ['name' => 'Volta na Pedra Grande', 'sport_type' => 'run', 'lat' => -23.4025, 'lng' => -46.1897],
        ];

        foreach ($extraSegments as $data) {
            $extraSegment = Segment::create([
                'name' => $data['name'],
                'sport_type' => $data['sport_type'],
                'start_lat' => $data['lat'],
                'start_lng' => $data['lng'],
                'end_lat' => $data['lat'] + 0.01,
                'end_lng' => $data['lng'] + 0.01,
                'radius_m' => 40,
                'created_by' => $users->first()->id,
            ]);

            $efforts = $users->random(min(4, $users->count()));
            foreach ($efforts as $user) {
                $activity = $user->activities()->inRandomOrder()->first()
                    ?? $user->activities()->create([
                        'title' => 'Treino',
                        'sport_type' => $data['sport_type'],
                        'start_time' => now()->subDays(fake()->numberBetween(1, 30)),
                        'distance' => 5000,
                        'duration' => 1800,
                        'calories' => 400,
                        'privacy' => 'public',
                        'feed_type' => 'personal',
                    ]);

                SegmentEffort::updateOrCreate(
                    ['segment_id' => $extraSegment->id, 'activity_id' => $activity->id],
                    [
                        'user_id' => $user->id,
                        'duration_seconds' => fake()->numberBetween(180, 600),
                        'achieved_at' => $activity->start_time ?? now(),
                    ]
                );
            }
        }

        $this->command->info('Extra segments seeded: '.count($extraSegments));
    }

    private function seedPoll($users): void
    {
        $poll = Post::create([
            'user_id' => $users->first()->id,
            'title' => 'Qual seu tipo de treino favorito?',
            'content' => 'Queremos saber o que a comunidade mais gosta de treinar!',
            'type' => 'poll',
            'privacy' => 'public',
            'feed_type' => 'personal',
            'poll_expires_at' => now()->addDays(3),
            'media' => [],
            'meta' => ['isMultiple' => false],
        ]);

        $options = collect(['Corrida', 'Ciclismo', 'Musculação', 'Yoga'])
            ->map(fn ($text) => PollOption::create(['post_id' => $poll->id, 'option_text' => $text]));

        foreach ($users->random(min(8, $users->count())) as $voter) {
            $option = $options->random();

            PollVote::create([
                'user_id' => $voter->id,
                'post_id' => $poll->id,
                'poll_option_id' => $option->id,
            ]);
            $option->increment('votes_count');
        }

        $this->command->info('Poll seeded with '.$options->count().' options and votes.');
    }

    private function seedComments($users): void
    {
        $posts = Post::where('type', 'post')->get();
        if ($posts->isEmpty()) {
            return;
        }

        $bodies = [
            'Muito bom! Continue assim 💪',
            'Também estou nessa jornada, vamos juntos!',
            'Que inspirador, obrigado por compartilhar.',
            'Dica excelente, vou aplicar no meu treino.',
        ];

        $count = 0;
        foreach ($posts as $post) {
            $firstComment = null;

            foreach ($users->random(min(2, $users->count())) as $commenter) {
                $comment = Comment::create([
                    'user_id' => $commenter->id,
                    'commentable_type' => Post::class,
                    'commentable_id' => $post->id,
                    'body' => $bodies[array_rand($bodies)],
                ]);
                $count++;
                $firstComment ??= $comment;
            }

            // One reply on the first comment of each post, to demo nested threads.
            Comment::create([
                'user_id' => $users->random()->id,
                'commentable_type' => Post::class,
                'commentable_id' => $post->id,
                'parent_id' => $firstComment->id,
                'body' => 'Concordo plenamente!',
            ]);
            $count++;
        }

        $this->command->info("Comments seeded: {$count}");
    }

    private function seedGroupChat($users): void
    {
        $groups = [
            ['name' => 'Corredores da Cidade - Grupo Oficial', 'description' => 'Chat do clube para combinar treinos.'],
            ['name' => 'Suporte Desafio 30 Dias', 'description' => 'Tira-dúvidas dos participantes do desafio.'],
        ];

        foreach ($groups as $data) {
            $group = ChatGroup::create([...$data, 'created_by' => $users->first()->id]);

            $members = $users->random(min(5, $users->count()));
            foreach ($members as $index => $member) {
                $group->members()->attach($member->id, ['role' => $index === 0 ? 'admin' : 'member']);
            }

            $messages = [
                'Bom dia, pessoal! Quem topa treinar hoje?',
                'Eu topo! Que horário?',
                'Pode ser 18h na entrada do parque.',
                'Perfeito, nos vemos lá!',
            ];

            foreach ($messages as $i => $content) {
                GroupMessage::create([
                    'chat_group_id' => $group->id,
                    'user_id' => $members[$i % $members->count()]->id,
                    'content' => $content,
                ]);
            }
        }

        $this->command->info('Group chats seeded: '.count($groups));
    }

    private function seedReports($users): void
    {
        $reporters = $users->random(min(4, $users->count()));
        $posts = Post::where('type', 'post')->get();
        $comments = Comment::all();
        $messages = Message::all();

        $reasons = ['Spam', 'Conteúdo ofensivo', 'Assédio', 'Informação falsa'];
        $created = 0;

        // Pending reports on posts and comments — left open for the admin to act on.
        foreach ($posts->random(min(2, $posts->count())) as $post) {
            Report::create([
                'reporter_id' => $reporters->random()->id,
                'reportable_type' => Post::class,
                'reportable_id' => $post->id,
                'reported_user_id' => $post->user_id,
                'reason' => $reasons[array_rand($reasons)],
                'details' => 'Denúncia de exemplo para revisão da moderação.',
                'status' => 'pending',
            ]);
            $created++;
        }

        if ($comments->isNotEmpty()) {
            $comment = $comments->random();
            Report::create([
                'reporter_id' => $reporters->random()->id,
                'reportable_type' => Comment::class,
                'reportable_id' => $comment->id,
                'reported_user_id' => $comment->user_id,
                'reason' => 'Assédio',
                'details' => 'Comentário com linguagem inadequada.',
                'status' => 'pending',
            ]);
            $created++;
        }

        if ($messages->isNotEmpty()) {
            $message = $messages->random();
            Report::create([
                'reporter_id' => $message->receiver_id,
                'reported_message_id' => $message->id,
                'reported_user_id' => $message->sender_id,
                'reason' => 'Spam',
                'details' => 'Mensagem indesejada recebida no chat direto.',
                'status' => 'pending',
            ]);
            $created++;
        }

        // A resolved report so the status filter has an example on both sides.
        if ($posts->isNotEmpty()) {
            Report::create([
                'reporter_id' => $reporters->random()->id,
                'reportable_type' => Post::class,
                'reportable_id' => $posts->last()->id,
                'reported_user_id' => $posts->last()->user_id,
                'reason' => 'Informação falsa',
                'details' => 'Já revisado pela equipe de moderação.',
                'status' => 'resolved',
            ]);
            $created++;
        }

        $this->command->info("Reports seeded: {$created}");
    }

    private function seedInactiveUser($users): void
    {
        $target = $users->first(fn ($user) => $user->profile && $user->profile->status !== 'inactive');

        if ($target) {
            $target->profile->update(['status' => 'inactive']);
            $this->command->info("Marked {$target->email} as inactive (demo for the status toggle).");
        }
    }
}
