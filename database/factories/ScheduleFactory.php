<?php

namespace Database\Factories;

use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Schedule>
 */
class ScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $eventDate = fake()->dateTimeBetween('now', '+3 months');
        $colors = ['#3788d8', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6'];

        $titles = [
            'Reunião de Equipe',
            'Apresentação de Projeto',
            'Workshop de Capacitação',
            'Treinamento Técnico',
            'Revisão de Processos',
            'Planejamento Estratégico',
            'Atendimento ao Cliente',
            'Sessão de Brainstorming',
            'Conferência Online',
            'Evento de Networking',
        ];

        // Get first user or create one
        $user = User::first() ?? User::factory()->create();

        return [
            'user_id' => $user->id,
            'title' => fake()->randomElement($titles),
            'description' => fake()->optional(0.7)->paragraph(),
            'photo' => null, // Optional: you can add logic to generate test images
            'event_date' => $eventDate->format('Y-m-d'),
            'event_time' => fake()->time('H:i'),
            'color' => fake()->randomElement($colors),
        ];
    }
}
