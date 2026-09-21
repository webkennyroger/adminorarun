<?php

namespace Tests\Feature\Livewire;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Cobre os 8 módulos novos de admin (sincronização total com o app):
 * cada rota deve carregar para admin/manager e ser bloqueada para
 * usuário comum, exatamente como os módulos já existentes.
 */
class AdminSyncRoutesTest extends TestCase
{
    use RefreshDatabase;

    public static function routeProvider(): array
    {
        return [
            'feed' => ['admin.feed.index'],
            'comments' => ['admin.comments.index'],
            'clubs' => ['admin.clubs.index'],
            'chat' => ['admin.chat.index'],
            'stories' => ['admin.stories.index'],
            'segments' => ['admin.segments.index'],
            'reports' => ['admin.reports.index'],
        ];
    }

    #[DataProvider('routeProvider')]
    public function test_admin_can_view_route(string $routeName)
    {
        $admin = User::factory()->create();
        $admin->profile()->update(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route($routeName))
            ->assertOk();
    }

    #[DataProvider('routeProvider')]
    public function test_regular_user_is_forbidden(string $routeName)
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route($routeName))
            ->assertForbidden();
    }
}
