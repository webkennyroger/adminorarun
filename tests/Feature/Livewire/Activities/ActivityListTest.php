<?php

namespace Tests\Feature\Livewire\Activities;

use App\Livewire\Activities\ActivityList;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class ActivityListTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->profile()->update(['role' => 'admin']);

        return $admin;
    }

    public function test_activity_list_renders_and_shows_seeded_activity()
    {
        $admin = $this->admin();
        $author = User::factory()->create(['name' => 'Maria Corredora']);
        Activity::factory()->create([
            'user_id' => $author->id,
            'title' => 'Corrida Matinal',
        ]);

        Livewire::actingAs($admin)
            ->test(ActivityList::class)
            ->assertOk()
            ->assertSee('Corrida Matinal')
            ->assertSee('Maria Corredora');
    }

    public function test_search_filters_by_title_and_description()
    {
        $admin = $this->admin();
        $author = User::factory()->create();

        Activity::factory()->create([
            'user_id' => $author->id,
            'title' => 'Corrida Matinal',
            'description' => 'Uma corrida tranquila',
        ]);
        Activity::factory()->create([
            'user_id' => $author->id,
            'title' => 'Pedalada Noturna',
            'description' => 'Ciclismo à noite',
        ]);

        Livewire::actingAs($admin)
            ->test(ActivityList::class)
            ->set('search', 'Corrida')
            ->assertSee('Corrida Matinal')
            ->assertDontSee('Pedalada Noturna');
    }

    public function test_activity_list_does_not_n_plus_one_on_user_profile()
    {
        $admin = $this->admin();

        // Several activities from several distinct users, each with a profile.
        for ($i = 0; $i < 8; $i++) {
            $author = User::factory()->create();
            Activity::factory()->create(['user_id' => $author->id]);
        }

        DB::enableQueryLog();

        Livewire::actingAs($admin)->test(ActivityList::class)->html();

        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        // The Blade view reads $activity->user->profile->city (see
        // resources/views/livewire/activities/activity-index.blade.php).
        // render() only eager-loads 'user', 'comments', 'likes', so each row
        // used to trigger an extra lazy-loaded profile query. With 8 rows,
        // an N+1 pushes the count well past this bound.
        $this->assertLessThan(10, $queryCount, "Expected a bounded query count, got {$queryCount} — possible N+1 on user->profile.");
    }

    public function test_regular_user_is_forbidden_from_activities_route()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('activities.index'))
            ->assertForbidden();
    }
}
