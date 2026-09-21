<?php

namespace Tests\Feature\Livewire\Stories;

use App\Livewire\Stories\StoryIndex;
use App\Models\Story;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StoryIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_stories_index_page()
    {
        $user = User::factory()->create();
        $user->profile()->update(['role' => 'admin']);

        $this->actingAs($user)
            ->get(route('admin.stories.index'))
            ->assertOk()
            ->assertSee('Stories');
    }

    public function test_active_story_appears_in_the_listing()
    {
        $user = User::factory()->create();
        $user->profile()->update(['role' => 'admin']);

        $author = User::factory()->create(['name' => 'Autora da Story']);
        $story = Story::factory()->create([
            'user_id' => $author->id,
            'expires_at' => now()->addHours(12),
        ]);

        Livewire::actingAs($user)
            ->test(StoryIndex::class)
            ->assertSee('Autora da Story')
            ->assertSee($story->image_url);
    }

    public function test_can_delete_a_story()
    {
        $user = User::factory()->create();
        $user->profile()->update(['role' => 'admin']);

        $story = Story::factory()->create([
            'expires_at' => now()->addHours(12),
        ]);

        Livewire::actingAs($user)
            ->test(StoryIndex::class)
            ->call('confirmDelete', $story->id)
            ->call('delete');

        $this->assertDatabaseMissing('stories', ['id' => $story->id]);
    }
}
