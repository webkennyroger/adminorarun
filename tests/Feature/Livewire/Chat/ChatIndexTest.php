<?php

namespace Tests\Feature\Livewire\Chat;

use App\Livewire\Chat\ChatIndex;
use App\Models\ChatGroup;
use App\Models\GroupMessage;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ChatIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_chat_page()
    {
        $admin = User::factory()->create();
        $admin->profile()->update(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.chat.index'))
            ->assertOk()
            ->assertSeeText('Chat');
    }

    public function test_direct_conversation_appears_in_the_listing()
    {
        $admin = User::factory()->create();
        $admin->profile()->update(['role' => 'admin']);

        $alice = User::factory()->create(['name' => 'Alice Runner']);
        $bob = User::factory()->create(['name' => 'Bob Sprinter']);

        Message::create([
            'sender_id' => $alice->id,
            'receiver_id' => $bob->id,
            'content' => 'Oi Bob, bora correr amanhã?',
        ]);

        // A reply from Bob to Alice must collapse into the SAME conversation
        // (normalized pair), not create a second row in the listing.
        Message::create([
            'sender_id' => $bob->id,
            'receiver_id' => $alice->id,
            'content' => 'Bora sim!',
        ]);

        Livewire::actingAs($admin)
            ->test(ChatIndex::class)
            ->assertSee('Alice Runner')
            ->assertSee('Bob Sprinter')
            ->assertSee('Bora sim!');
    }

    public function test_group_conversation_appears_in_the_listing()
    {
        $admin = User::factory()->create();
        $admin->profile()->update(['role' => 'admin']);

        $creator = User::factory()->create();
        $group = new ChatGroup(['name' => 'Corredores de Elite']);
        $group->created_by = $creator->id;
        $group->save();

        GroupMessage::create([
            'chat_group_id' => $group->id,
            'user_id' => $creator->id,
            'content' => 'Bem-vindos ao grupo!',
        ]);

        Livewire::actingAs($admin)
            ->test(ChatIndex::class)
            ->assertSee('Corredores de Elite')
            ->assertSee('Bem-vindos ao grupo!');
    }

    public function test_admin_can_open_a_direct_thread_and_see_messages_in_order()
    {
        $admin = User::factory()->create();
        $admin->profile()->update(['role' => 'admin']);

        $alice = User::factory()->create(['name' => 'Alice Runner']);
        $bob = User::factory()->create(['name' => 'Bob Sprinter']);

        $first = Message::create([
            'sender_id' => $alice->id,
            'receiver_id' => $bob->id,
            'content' => 'Primeira mensagem',
            'created_at' => now()->subMinutes(5),
        ]);

        Message::create([
            'sender_id' => $bob->id,
            'receiver_id' => $alice->id,
            'content' => 'Segunda mensagem',
            'created_at' => now(),
        ]);

        Livewire::actingAs($admin)
            ->test(ChatIndex::class)
            ->call('openThread', 'direct', $alice->id, $bob->id)
            ->assertSet('showThreadModal', true)
            ->assertSee('Primeira mensagem')
            ->assertSee('Segunda mensagem');

        $this->assertDatabaseHas('messages', ['id' => $first->id]);
    }

    public function test_admin_can_delete_an_abusive_message_from_a_thread()
    {
        $admin = User::factory()->create();
        $admin->profile()->update(['role' => 'admin']);

        $alice = User::factory()->create();
        $bob = User::factory()->create();

        $message = Message::create([
            'sender_id' => $alice->id,
            'receiver_id' => $bob->id,
            'content' => 'Conteúdo abusivo',
        ]);

        Livewire::actingAs($admin)
            ->test(ChatIndex::class)
            ->call('openThread', 'direct', $alice->id, $bob->id)
            ->call('confirmMessageDeletion', $message->id)
            ->assertSet('confirmingMessageDeletion', true)
            ->call('deleteMessage');

        $this->assertDatabaseMissing('messages', ['id' => $message->id]);
    }

    public function test_regular_user_is_forbidden()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.chat.index'))
            ->assertForbidden();
    }
}
