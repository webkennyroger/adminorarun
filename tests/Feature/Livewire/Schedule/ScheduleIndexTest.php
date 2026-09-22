<?php

namespace Tests\Feature\Livewire\Schedule;

use App\Livewire\Schedule\EventModal;
use App\Livewire\Schedule\ScheduleIndex;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ScheduleIndexTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->profile()->update(['role' => 'admin']);

        return $admin;
    }

    public function test_schedule_index_renders_without_exception()
    {
        $admin = $this->admin();
        Schedule::factory()->create(['user_id' => $admin->id, 'title' => 'Reunião de Equipe']);

        Livewire::actingAs($admin)
            ->test(ScheduleIndex::class)
            ->assertOk();
    }

    public function test_get_events_only_returns_authenticated_users_events()
    {
        $admin = $this->admin();
        $other = User::factory()->create();

        Schedule::factory()->create(['user_id' => $admin->id, 'title' => 'Meu Evento']);
        Schedule::factory()->create(['user_id' => $other->id, 'title' => 'Evento de Outro']);

        $component = Livewire::actingAs($admin)->test(ScheduleIndex::class);
        $events = $component->instance()->getEvents();

        $this->assertCount(1, $events);
        $this->assertSame('Meu Evento', $events[0]['title']);
    }

    public function test_event_modal_renders_without_exception()
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(EventModal::class)
            ->assertOk();
    }

    public function test_event_modal_can_create_an_event()
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(EventModal::class)
            ->call('openCreateModal', '2026-10-10', '09:00')
            ->set('title', 'Evento Criado via Modal')
            ->set('event_date', '2026-10-10')
            ->set('event_time', '09:00')
            ->call('saveEvent')
            ->assertDispatched('eventSaved');

        $this->assertDatabaseHas('schedules', [
            'user_id' => $admin->id,
            'title' => 'Evento Criado via Modal',
        ]);
    }

    public function test_event_modal_can_update_an_event()
    {
        $admin = $this->admin();
        $event = Schedule::factory()->create(['user_id' => $admin->id, 'title' => 'Antes']);

        Livewire::actingAs($admin)
            ->test(EventModal::class)
            ->call('openEditModal', $event->id)
            ->set('title', 'Depois')
            ->call('saveEvent')
            ->assertDispatched('eventSaved');

        $this->assertDatabaseHas('schedules', [
            'id' => $event->id,
            'title' => 'Depois',
        ]);
    }

    public function test_event_modal_can_delete_an_event()
    {
        $admin = $this->admin();
        $event = Schedule::factory()->create(['user_id' => $admin->id]);

        Livewire::actingAs($admin)
            ->test(EventModal::class)
            ->call('openEditModal', $event->id)
            ->call('confirmDelete')
            ->call('deleteEvent')
            ->assertDispatched('eventSaved');

        $this->assertDatabaseMissing('schedules', ['id' => $event->id]);
    }

    public function test_view_contains_dom_hooks_required_by_calendar_init_js()
    {
        // calendar-init.js manipulates these elements by id/class (see
        // resources/js/components/calendar-init.js). If the Blade markup is
        // rewritten without keeping these hooks, event create/update/delete
        // break with a silent JS TypeError (querySelector/getElementById
        // returning null) even though the page renders fine.
        $admin = $this->admin();

        $html = Livewire::actingAs($admin)->test(ScheduleIndex::class)->html();

        foreach (['id="event-photo"', 'id="confirmDeleteModal"', 'class="cancel-delete-btn', 'class="confirm-delete-btn'] as $needle) {
            $this->assertStringContainsString($needle, $html, "Missing DOM hook [$needle] required by calendar-init.js");
        }
    }

    public function test_regular_user_is_forbidden_from_schedule_route()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('schedule.index'))
            ->assertForbidden();
    }
}
