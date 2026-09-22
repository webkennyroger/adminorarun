<?php

namespace Tests\Feature\Livewire\Users;

use App\Livewire\Users\UserIndex;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class UserIndexTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->profile()->update(['role' => 'admin']);

        return $admin;
    }

    public function test_admin_can_view_users_page()
    {
        $admin = $this->admin();
        $user = User::factory()->create(['name' => 'Maria Souza']);

        Livewire::actingAs($admin)
            ->test(UserIndex::class)
            ->assertOk()
            ->assertSee('Maria Souza');
    }

    public function test_search_filters_users_by_name()
    {
        $admin = $this->admin();
        User::factory()->create(['name' => 'Carlos Pereira']);
        User::factory()->create(['name' => 'Ana Lima']);

        Livewire::actingAs($admin)
            ->test(UserIndex::class)
            ->set('search', 'Carlos')
            ->assertSee('Carlos Pereira')
            ->assertDontSee('Ana Lima');
    }

    public function test_admin_can_create_a_user()
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(UserIndex::class)
            ->call('create')
            ->set('name', 'Novo Usuario')
            ->set('email', 'novo.usuario@example.com')
            ->set('password', 'senha123')
            ->call('store')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'novo.usuario@example.com',
            'name' => 'Novo Usuario',
        ]);
    }

    public function test_admin_can_toggle_user_status()
    {
        $admin = $this->admin();
        $user = User::factory()->create();
        $user->profile()->update(['status' => 'active']);

        Livewire::actingAs($admin)
            ->test(UserIndex::class)
            ->call('toggleStatus', $user->id);

        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'status' => 'inactive',
        ]);
    }

    public function test_admin_can_delete_a_user()
    {
        $admin = $this->admin();
        $user = User::factory()->create();

        Livewire::actingAs($admin)
            ->test(UserIndex::class)
            ->call('confirmDelete', $user->id)
            ->call('delete');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_non_super_admin_cannot_change_role_or_plan_on_update()
    {
        $admin = $this->admin();
        $user = User::factory()->create();
        $user->profile()->update(['role' => 'user', 'plan' => 'free']);

        Livewire::actingAs($admin)
            ->test(UserIndex::class)
            ->call('edit', $user->id)
            ->set('role', 'admin')
            ->set('plan', 'annual')
            ->call('update');

        // Regular admin (not the hardcoded super-admin email) must not be able
        // to escalate another user's role/plan via this form.
        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'role' => 'user',
            'plan' => 'free',
        ]);
    }

    public function test_regular_user_is_forbidden_from_users_index_route()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('users.index'))
            ->assertForbidden();
    }

    public function test_users_show_route_renders_for_admin()
    {
        $admin = $this->admin();
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('users.show', $user->id))
            ->assertOk()
            ->assertSee($user->name);
    }

    public function test_render_query_count_does_not_scale_with_user_count()
    {
        $admin = $this->admin();
        User::factory()->count(3)->create();

        DB::enableQueryLog();
        Livewire::actingAs($admin)->test(UserIndex::class)->set('perPage', 100);
        $smallCount = count(DB::getQueryLog());
        DB::flushQueryLog();

        User::factory()->count(15)->create();
        DB::flushQueryLog();

        Livewire::actingAs($admin)->test(UserIndex::class)->set('perPage', 100);
        $largeCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertEquals(
            $smallCount,
            $largeCount,
            'Query count should not scale with the number of rows displayed (possible N+1).'
        );
    }
}
