<?php

namespace Tests\Feature\Livewire\Support;

use App\Models\Support;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_support_detail()
    {
        $user = User::factory()->create();
        $user->profile()->update(['role' => 'admin']);
        $ticket = Support::create([
            'user_id' => $user->id,
            'subject' => 'Test Ticket',
            'message' => 'Message content',
            'status' => 'pending',
            'priority' => 'low',
        ]);

        $this->actingAs($user)
            ->get(route('support.show', $ticket))
            ->assertOk()
            ->assertSee($ticket->subject);
    }

    public function test_cannot_view_others_ticket()
    {
        // Manager: passa pelo gate de acesso web, mas não tem o bypass de
        // dono de ticket que só existe para admins (ver SupportShow::mount).
        $user = User::factory()->create();
        $user->profile()->update(['role' => 'manager']);
        $otherUser = User::factory()->create();
        $ticket = Support::create([
            'user_id' => $otherUser->id,
            'subject' => 'Other Ticket',
            'message' => 'Message content',
            'status' => 'pending',
            'priority' => 'low',
        ]);

        $this->actingAs($user)
            ->get(route('support.show', $ticket))
            ->assertForbidden();
    }

    public function test_404_for_non_existent_ticket()
    {
        $user = User::factory()->create();
        $user->profile()->update(['role' => 'admin']);

        $this->actingAs($user)
            ->get(route('support.show', 99999))
            ->assertNotFound();
    }

    public function test_admin_can_reply_to_a_ticket_via_the_reply_form()
    {
        $admin = User::factory()->create();
        $admin->profile()->update(['role' => 'admin']);
        $owner = User::factory()->create();
        $ticket = Support::create([
            'user_id' => $owner->id,
            'subject' => 'Preciso de ajuda',
            'message' => 'Message content',
            'status' => 'open',
            'priority' => 'low',
        ]);

        $this->actingAs($admin)
            ->post(route('support.reply', $ticket), ['message' => 'Vamos resolver isso.'])
            ->assertRedirect(route('support.show', $ticket));

        $this->assertDatabaseHas('support_replies', [
            'support_id' => $ticket->id,
            'user_id' => $admin->id,
            'message' => 'Vamos resolver isso.',
        ]);

        $this->actingAs($admin)
            ->get(route('support.show', $ticket))
            ->assertOk()
            ->assertSee('Vamos resolver isso.');
    }

    public function test_admin_can_update_ticket_status_via_the_status_form()
    {
        $admin = User::factory()->create();
        $admin->profile()->update(['role' => 'admin']);
        $ticket = Support::create([
            'user_id' => $admin->id,
            'subject' => 'Test Ticket',
            'message' => 'Message content',
            'status' => 'open',
            'priority' => 'low',
        ]);

        $this->actingAs($admin)
            ->patch(route('support.update-status', $ticket), ['status' => 'resolved'])
            ->assertRedirect(route('support.show', $ticket));

        $this->assertDatabaseHas('supports', [
            'id' => $ticket->id,
            'status' => 'resolved',
        ]);
    }

    public function test_non_admin_cannot_update_ticket_status()
    {
        $owner = User::factory()->create();
        $ticket = Support::create([
            'user_id' => $owner->id,
            'subject' => 'Test Ticket',
            'message' => 'Message content',
            'status' => 'open',
            'priority' => 'low',
        ]);

        $this->actingAs($owner)
            ->patch(route('support.update-status', $ticket), ['status' => 'resolved'])
            ->assertForbidden();
    }
}
