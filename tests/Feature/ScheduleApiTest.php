<?php

namespace Tests\Feature;

use App\Models\Schedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleApiTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->profile()->update(['role' => 'admin']);

        return $admin;
    }

    public function test_index_only_returns_events_for_the_authenticated_user()
    {
        $admin = $this->admin();
        $other = User::factory()->create();

        Schedule::factory()->create(['user_id' => $admin->id, 'title' => 'Meu Evento']);
        Schedule::factory()->create(['user_id' => $other->id, 'title' => 'Evento de Outro Usuário']);

        $response = $this->actingAs($admin)->getJson(route('api.events.index'));

        $response->assertOk();
        $titles = collect($response->json())->pluck('title');

        $this->assertTrue($titles->contains('Meu Evento'));
        $this->assertFalse($titles->contains('Evento de Outro Usuário'));
    }

    public function test_store_creates_event_for_authenticated_user()
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->postJson(route('api.events.store'), [
            'title' => 'Novo Evento',
            'description' => 'Descrição do evento',
            'start_date' => '2026-10-05',
            'event_time' => '10:30',
            'event_level' => 'Success',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('schedules', [
            'user_id' => $admin->id,
            'title' => 'Novo Evento',
            'color' => 'Success',
        ]);
    }

    public function test_update_modifies_own_event()
    {
        $admin = $this->admin();
        $event = Schedule::factory()->create(['user_id' => $admin->id, 'title' => 'Título Antigo']);

        $response = $this->actingAs($admin)->putJson(route('api.events.update', $event->id), [
            'title' => 'Título Novo',
            'description' => 'Atualizado',
            'start_date' => '2026-11-01',
            'event_time' => '09:00',
            'event_level' => 'Warning',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('schedules', [
            'id' => $event->id,
            'title' => 'Título Novo',
            'color' => 'Warning',
        ]);
    }

    public function test_update_cannot_modify_another_users_event()
    {
        $admin = $this->admin();
        $other = User::factory()->create();
        $event = Schedule::factory()->create(['user_id' => $other->id]);

        $response = $this->actingAs($admin)->putJson(route('api.events.update', $event->id), [
            'title' => 'Hackeado',
            'start_date' => '2026-11-01',
        ]);

        $response->assertNotFound();
    }

    public function test_destroy_deletes_own_event()
    {
        $admin = $this->admin();
        $event = Schedule::factory()->create(['user_id' => $admin->id]);

        $response = $this->actingAs($admin)->deleteJson(route('api.events.destroy', $event->id));

        $response->assertOk();
        $this->assertDatabaseMissing('schedules', ['id' => $event->id]);
    }

    public function test_destroy_cannot_delete_another_users_event()
    {
        $admin = $this->admin();
        $other = User::factory()->create();
        $event = Schedule::factory()->create(['user_id' => $other->id]);

        $response = $this->actingAs($admin)->deleteJson(route('api.events.destroy', $event->id));

        $response->assertNotFound();
        $this->assertDatabaseHas('schedules', ['id' => $event->id]);
    }

    public function test_regular_user_is_forbidden_from_events_api()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson(route('api.events.index'))
            ->assertForbidden();
    }
}
