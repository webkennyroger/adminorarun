<?php

namespace Tests\Feature\Livewire\Feed;

use App\Livewire\Feed\FeedIndex;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FeedIndexTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->profile()->update(['role' => 'admin']);

        return $admin;
    }

    public function test_can_view_feed_index_page()
    {
        $admin = $this->admin();

        Post::factory()->create(['title' => 'Hello World', 'content' => 'Some content']);

        $this->actingAs($admin)
            ->get(route('admin.feed.index'))
            ->assertOk()
            ->assertSee('Hello World');
    }

    public function test_search_filters_posts_by_title_and_content()
    {
        $admin = $this->admin();

        Post::factory()->create(['title' => 'Corrida de Domingo', 'content' => 'Vamos correr juntos']);
        Post::factory()->create(['title' => 'Outro assunto qualquer', 'content' => 'Sem relação']);

        Livewire::actingAs($admin)
            ->test(FeedIndex::class)
            ->set('search', 'Corrida')
            ->assertSee('Corrida de Domingo')
            ->assertDontSee('Outro assunto qualquer');
    }

    public function test_type_filter_shows_only_polls()
    {
        $admin = $this->admin();

        Post::factory()->create(['type' => 'post', 'title' => 'Um Post Comum']);
        Post::factory()->create(['type' => 'poll', 'title' => 'Uma Enquete Legal']);

        Livewire::actingAs($admin)
            ->test(FeedIndex::class)
            ->set('typeFilter', 'poll')
            ->assertSee('Uma Enquete Legal')
            ->assertDontSee('Um Post Comum');
    }

    public function test_can_delete_post()
    {
        $admin = $this->admin();

        $post = Post::factory()->create();

        Livewire::actingAs($admin)
            ->test(FeedIndex::class)
            ->call('confirmDelete', $post->id)
            ->call('delete');

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_can_delete_selected_posts()
    {
        $admin = $this->admin();

        $post1 = Post::factory()->create();
        $post2 = Post::factory()->create();

        Livewire::actingAs($admin)
            ->test(FeedIndex::class)
            ->set('selected', [$post1->id, $post2->id])
            ->call('deleteSelected');

        $this->assertDatabaseMissing('posts', ['id' => $post1->id]);
        $this->assertDatabaseMissing('posts', ['id' => $post2->id]);
    }
}
