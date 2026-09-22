<?php

namespace Tests\Feature\Livewire\Plans;

use App\Livewire\Plans\PlanIndex;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PlanIndexTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->profile()->update(['role' => 'admin']);

        return $admin;
    }

    private function plan(array $overrides = []): SubscriptionPlan
    {
        return SubscriptionPlan::create(array_merge([
            'name' => 'Plano Básico',
            'slug' => 'plano-basico-mensal-'.uniqid(),
            'stripe_plan_id' => 'price_'.uniqid(),
            'price' => 1990,
            'currency' => 'BRL',
            'billing_period' => 'monthly',
            'features' => ['Recurso A', 'Recurso B'],
            'is_active' => true,
        ], $overrides));
    }

    public function test_plan_index_renders_and_lists_seeded_plans()
    {
        $admin = $this->admin();
        $this->plan(['name' => 'Plano Premium']);

        Livewire::actingAs($admin)
            ->test(PlanIndex::class)
            ->assertOk()
            ->assertSee('Plano Premium');
    }

    public function test_can_create_a_plan()
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(PlanIndex::class)
            ->call('create')
            ->set('name', 'Plano Novo')
            ->set('stripe_plan_id', 'price_novo123')
            ->set('price', 2990)
            ->set('billing_period', 'yearly')
            ->set('features', 'Recurso A,Recurso B')
            ->set('is_active', true)
            ->call('store')
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('subscription_plans', [
            'name' => 'Plano Novo',
            'stripe_plan_id' => 'price_novo123',
            'billing_period' => 'yearly',
        ]);
    }

    public function test_can_edit_a_plan()
    {
        $admin = $this->admin();
        $plan = $this->plan(['name' => 'Plano Antigo']);

        Livewire::actingAs($admin)
            ->test(PlanIndex::class)
            ->call('edit', $plan->id)
            ->assertSet('name', 'Plano Antigo')
            ->set('name', 'Plano Atualizado')
            ->call('update');

        $this->assertDatabaseHas('subscription_plans', [
            'id' => $plan->id,
            'name' => 'Plano Atualizado',
        ]);
    }

    public function test_can_delete_a_plan()
    {
        $admin = $this->admin();
        $plan = $this->plan();

        Livewire::actingAs($admin)
            ->test(PlanIndex::class)
            ->call('confirmDelete', $plan->id)
            ->assertSet('confirmingDeletion', true)
            ->call('delete');

        $this->assertDatabaseMissing('subscription_plans', ['id' => $plan->id]);
    }

    public function test_create_requires_unique_stripe_plan_id()
    {
        $admin = $this->admin();
        $this->plan(['stripe_plan_id' => 'price_duplicado']);

        Livewire::actingAs($admin)
            ->test(PlanIndex::class)
            ->call('create')
            ->set('name', 'Outro Plano')
            ->set('stripe_plan_id', 'price_duplicado')
            ->set('price', 1000)
            ->set('billing_period', 'monthly')
            ->call('store')
            ->assertHasErrors(['stripe_plan_id']);
    }

    public function test_regular_user_is_forbidden_from_plans_route()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('plans.index'))
            ->assertForbidden();
    }
}
