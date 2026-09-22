<?php

namespace Tests\Feature\Livewire\Challenges;

use App\Livewire\Challenges\ChallengeIndex;
use App\Models\Category;
use App\Models\Challenge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class ChallengeIndexTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->profile()->update(['role' => 'admin']);

        return $admin;
    }

    public function test_admin_can_view_challenges_page()
    {
        $admin = $this->admin();
        $category = Category::factory()->create();
        Challenge::factory()->forCategory($category)->create(['title' => 'Desafio 100km']);

        Livewire::actingAs($admin)
            ->test(ChallengeIndex::class)
            ->assertOk()
            ->assertSee('Desafio 100km');
    }

    public function test_search_filters_challenges_by_title()
    {
        $admin = $this->admin();
        Challenge::factory()->create(['title' => 'Corrida de Verão']);
        Challenge::factory()->create(['title' => 'Maratona de Inverno']);

        Livewire::actingAs($admin)
            ->test(ChallengeIndex::class)
            ->set('search', 'Verão')
            ->assertSee('Corrida de Verão')
            ->assertDontSee('Maratona de Inverno');
    }

    public function test_can_create_challenge()
    {
        Notification::fake();
        $admin = $this->admin();
        $category = Category::factory()->create();

        Livewire::actingAs($admin)
            ->test(ChallengeIndex::class)
            ->call('create')
            ->assertSet('showCreateModal', true)
            ->set('title', 'Desafio Novo')
            ->set('description', 'Descrição do desafio')
            ->set('start_date', now()->format('Y-m-d'))
            ->set('end_date', now()->addDays(30)->format('Y-m-d'))
            ->set('goal_km', 42.5)
            ->set('category_id', $category->id)
            ->call('save')
            ->assertSet('showCreateModal', false);

        $this->assertDatabaseHas('challenges', [
            'title' => 'Desafio Novo',
            'category_id' => $category->id,
        ]);
    }

    public function test_creating_featured_challenge_unfeatures_previous_one()
    {
        Notification::fake();
        $admin = $this->admin();
        $category = Category::factory()->create();
        $existing = Challenge::factory()->forCategory($category)->create(['is_featured' => true]);

        Livewire::actingAs($admin)
            ->test(ChallengeIndex::class)
            ->set('title', 'Novo Destaque')
            ->set('description', 'Descrição')
            ->set('start_date', now()->format('Y-m-d'))
            ->set('end_date', now()->addDays(10)->format('Y-m-d'))
            ->set('goal_km', 10)
            ->set('category_id', $category->id)
            ->set('is_featured', true)
            ->call('save');

        $this->assertDatabaseHas('challenges', ['id' => $existing->id, 'is_featured' => false]);
        $this->assertDatabaseHas('challenges', ['title' => 'Novo Destaque', 'is_featured' => true]);
    }

    public function test_can_view_challenge_details()
    {
        $admin = $this->admin();
        $challenge = Challenge::factory()->create(['title' => 'Ver Detalhes']);

        Livewire::actingAs($admin)
            ->test(ChallengeIndex::class)
            ->call('view', $challenge->id)
            ->assertSet('showViewModal', true)
            ->assertSee('Ver Detalhes');
    }

    public function test_can_edit_challenge()
    {
        $admin = $this->admin();
        $category = Category::factory()->create();
        $challenge = Challenge::factory()->forCategory($category)->create(['title' => 'Título Antigo']);

        Livewire::actingAs($admin)
            ->test(ChallengeIndex::class)
            ->call('edit', $challenge->id)
            ->assertSet('showEditModal', true)
            ->assertSet('title', 'Título Antigo')
            ->set('title', 'Título Editado')
            ->call('update')
            ->assertSet('showEditModal', false);

        $this->assertDatabaseHas('challenges', [
            'id' => $challenge->id,
            'title' => 'Título Editado',
        ]);
    }

    public function test_can_delete_challenge()
    {
        $admin = $this->admin();
        $challenge = Challenge::factory()->create();

        Livewire::actingAs($admin)
            ->test(ChallengeIndex::class)
            ->call('confirmDelete', $challenge->id)
            ->assertSet('confirmingDeletion', true)
            ->call('delete')
            ->assertSet('confirmingDeletion', false);

        $this->assertSoftDeleted('challenges', ['id' => $challenge->id]);
    }

    public function test_can_bulk_delete_selected_challenges()
    {
        $admin = $this->admin();
        $challenge1 = Challenge::factory()->create();
        $challenge2 = Challenge::factory()->create();

        Livewire::actingAs($admin)
            ->test(ChallengeIndex::class)
            ->set('selected', [$challenge1->id, $challenge2->id])
            ->call('deleteSelected')
            ->assertSet('selected', []);

        $this->assertSoftDeleted('challenges', ['id' => $challenge1->id]);
        $this->assertSoftDeleted('challenges', ['id' => $challenge2->id]);
    }

    public function test_regular_user_is_forbidden_from_route()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.challenges.index'))
            ->assertForbidden();
    }
}
