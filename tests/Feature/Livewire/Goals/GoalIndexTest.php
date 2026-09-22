<?php

namespace Tests\Feature\Livewire\Goals;

use App\Livewire\Goals\GoalIndex;
use App\Models\Goal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GoalIndexTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->profile()->update(['role' => 'admin']);

        return $admin;
    }

    public function test_admin_can_view_goals_page()
    {
        $admin = $this->admin();
        Goal::factory()->create(['title' => 'Meta de Usuários']);

        Livewire::actingAs($admin)
            ->test(GoalIndex::class)
            ->assertOk()
            ->assertSee('Meta de Usuários');
    }

    public function test_search_filters_goals_by_title()
    {
        $admin = $this->admin();
        Goal::factory()->create(['title' => 'Meta de Vendas']);
        Goal::factory()->create(['title' => 'Meta de Receita']);

        Livewire::actingAs($admin)
            ->test(GoalIndex::class)
            ->set('search', 'Vendas')
            ->assertSee('Meta de Vendas')
            ->assertDontSee('Meta de Receita');
    }

    public function test_can_create_goal()
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(GoalIndex::class)
            ->call('create')
            ->assertSet('showCreateModal', true)
            ->set('title', 'Meta Nova')
            ->set('metric', 'sales')
            ->set('period', 'quarterly')
            ->set('target_value', 5000)
            ->set('start_date', now()->format('Y-m-d'))
            ->set('end_date', now()->addMonths(3)->format('Y-m-d'))
            ->call('save')
            ->assertSet('showCreateModal', false);

        $this->assertDatabaseHas('goals', [
            'title' => 'Meta Nova',
            'metric' => 'sales',
            'period' => 'quarterly',
        ]);
    }

    public function test_can_edit_goal()
    {
        $admin = $this->admin();
        $goal = Goal::factory()->create(['title' => 'Título Antigo', 'metric' => 'users']);

        Livewire::actingAs($admin)
            ->test(GoalIndex::class)
            ->call('edit', $goal->id)
            ->assertSet('showEditModal', true)
            ->assertSet('title', 'Título Antigo')
            ->set('title', 'Título Editado')
            ->call('update')
            ->assertSet('showEditModal', false);

        $this->assertDatabaseHas('goals', [
            'id' => $goal->id,
            'title' => 'Título Editado',
        ]);
    }

    public function test_can_delete_goal()
    {
        $admin = $this->admin();
        $goal = Goal::factory()->create();

        Livewire::actingAs($admin)
            ->test(GoalIndex::class)
            ->call('confirmDelete', $goal->id)
            ->assertSet('showDeleteModal', true)
            ->call('delete')
            ->assertSet('showDeleteModal', false);

        $this->assertDatabaseMissing('goals', ['id' => $goal->id]);
    }

    public function test_can_bulk_delete_selected_goals()
    {
        $admin = $this->admin();
        $goal1 = Goal::factory()->create();
        $goal2 = Goal::factory()->create();

        Livewire::actingAs($admin)
            ->test(GoalIndex::class)
            ->set('selected', [$goal1->id, $goal2->id])
            ->call('deleteSelected')
            ->assertSet('selected', []);

        $this->assertDatabaseMissing('goals', ['id' => $goal1->id]);
        $this->assertDatabaseMissing('goals', ['id' => $goal2->id]);
    }

    public function test_changing_page_clears_stale_row_selection()
    {
        $admin = $this->admin();
        Goal::factory()->count(15)->create();

        $component = Livewire::actingAs($admin)
            ->test(GoalIndex::class)
            ->call('toggleSelectAll');

        $component->assertSet('selectAll', true);
        $this->assertNotEmpty($component->get('selected'));

        $component->call('nextPage')
            ->assertSet('selected', [])
            ->assertSet('selectAll', false);
    }

    public function test_regular_user_is_forbidden_from_route()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('goals.index'))
            ->assertForbidden();
    }
}
