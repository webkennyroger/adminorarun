<?php

namespace Tests\Feature\Livewire\Clubs;

use App\Livewire\Clubs\ClubIndex;
use App\Models\Club;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ClubIndexTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->profile()->update(['role' => 'admin']);

        return $user;
    }

    public function test_can_view_clubs_index_page()
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.clubs.index'))
            ->assertOk()
            ->assertSeeLivewire('clubs.club-index');
    }

    public function test_can_create_a_club()
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(ClubIndex::class)
            ->set('name', 'Corredores da Serra')
            ->set('description', 'Clube de corrida de trilha.')
            ->set('city', 'Petrópolis')
            ->set('state', 'RJ')
            ->set('category', 'trail')
            ->set('is_public', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('clubs', [
            'name' => 'Corredores da Serra',
            'city' => 'Petrópolis',
            'state' => 'RJ',
            'category' => 'trail',
            'creator_id' => $admin->id,
            'creator_name' => $admin->name,
        ]);
    }

    public function test_create_requires_mandatory_fields()
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(ClubIndex::class)
            ->set('name', '')
            ->set('city', '')
            ->set('state', '')
            ->set('category', '')
            ->call('save')
            ->assertHasErrors(['name', 'city', 'state', 'category']);
    }

    public function test_can_update_a_club()
    {
        $admin = $this->admin();
        $club = Club::factory()->create(['name' => 'Old Name']);

        Livewire::actingAs($admin)
            ->test(ClubIndex::class)
            ->call('edit', $club->id)
            ->set('name', 'New Name')
            ->call('update')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('clubs', [
            'id' => $club->id,
            'name' => 'New Name',
        ]);
    }

    public function test_deleting_a_club_soft_deletes_it()
    {
        $admin = $this->admin();
        $club = Club::factory()->create();

        Livewire::actingAs($admin)
            ->test(ClubIndex::class)
            ->call('confirmDelete', $club->id)
            ->call('delete');

        $this->assertSoftDeleted('clubs', ['id' => $club->id]);
        $this->assertDatabaseCount('clubs', 1);
    }

    public function test_can_manage_club_members()
    {
        $admin = $this->admin();
        $club = Club::factory()->create();
        $member = User::factory()->create();
        $club->members()->attach($member->id, ['role' => 'member']);

        Livewire::actingAs($admin)
            ->test(ClubIndex::class)
            ->call('manageMembers', $club->id)
            ->call('promoteMember', $member->id);

        $this->assertDatabaseHas('club_user', [
            'club_id' => $club->id,
            'user_id' => $member->id,
            'role' => 'admin',
        ]);

        Livewire::actingAs($admin)
            ->test(ClubIndex::class)
            ->call('manageMembers', $club->id)
            ->call('removeMember', $member->id);

        $this->assertDatabaseMissing('club_user', [
            'club_id' => $club->id,
            'user_id' => $member->id,
        ]);
    }
}
