<?php

namespace Tests\Feature\Livewire\Categories;

use App\Livewire\Categories\CategoryIndex;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryIndexTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->profile()->update(['role' => 'admin']);

        return $admin;
    }

    public function test_admin_can_view_categories_page()
    {
        $admin = $this->admin();
        Category::factory()->create(['name' => 'Corrida de Rua']);

        Livewire::actingAs($admin)
            ->test(CategoryIndex::class)
            ->assertOk()
            ->assertSee('Corrida de Rua');
    }

    public function test_search_filters_categories_by_name()
    {
        $admin = $this->admin();
        Category::factory()->create(['name' => 'Trilha']);
        Category::factory()->create(['name' => 'Ciclismo']);

        Livewire::actingAs($admin)
            ->test(CategoryIndex::class)
            ->set('search', 'Trilha')
            ->assertSee('Trilha')
            ->assertDontSee('Ciclismo');
    }

    public function test_can_create_category()
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(CategoryIndex::class)
            ->call('create')
            ->assertSet('showModal', true)
            ->set('name', 'Natação')
            ->set('color', 'blue')
            ->call('save')
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('categories', [
            'name' => 'Natação',
            'slug' => 'natacao',
            'color' => 'blue',
        ]);
    }

    public function test_create_requires_unique_name()
    {
        $admin = $this->admin();
        Category::factory()->create(['name' => 'Duplicada']);

        Livewire::actingAs($admin)
            ->test(CategoryIndex::class)
            ->set('name', 'Duplicada')
            ->set('color', 'zinc')
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_can_edit_category()
    {
        $admin = $this->admin();
        $category = Category::factory()->create(['name' => 'Antigo Nome', 'color' => 'zinc']);

        Livewire::actingAs($admin)
            ->test(CategoryIndex::class)
            ->call('edit', $category->id)
            ->assertSet('showModal', true)
            ->assertSet('name', 'Antigo Nome')
            ->set('name', 'Novo Nome')
            ->set('color', 'rose')
            ->call('save');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Novo Nome',
            'color' => 'rose',
        ]);
    }

    public function test_can_delete_category()
    {
        $admin = $this->admin();
        $category = Category::factory()->create();

        Livewire::actingAs($admin)
            ->test(CategoryIndex::class)
            ->call('confirmDelete', $category->id)
            ->assertSet('confirmingDeletion', true)
            ->call('delete')
            ->assertSet('confirmingDeletion', false);

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_can_bulk_delete_selected_categories()
    {
        $admin = $this->admin();
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        Livewire::actingAs($admin)
            ->test(CategoryIndex::class)
            ->set('selected', [$category1->id, $category2->id])
            ->call('deleteSelected')
            ->assertSet('selected', []);

        $this->assertDatabaseMissing('categories', ['id' => $category1->id]);
        $this->assertDatabaseMissing('categories', ['id' => $category2->id]);
    }

    public function test_regular_user_is_forbidden_from_route()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('categories.index'))
            ->assertForbidden();
    }
}
