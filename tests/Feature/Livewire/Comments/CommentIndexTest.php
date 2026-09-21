<?php

namespace Tests\Feature\Livewire\Comments;

use App\Livewire\Comments\CommentIndex;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class CommentIndexTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->profile()->update(['role' => 'admin']);

        return $admin;
    }

    public function test_admin_can_view_comments_page()
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.comments.index'))
            ->assertOk();
    }

    public function test_page_lists_comments()
    {
        $admin = $this->admin();
        $author = User::factory()->create();
        $post = Post::factory()->create();

        $comment = Comment::create([
            'user_id' => $author->id,
            'body' => 'Um comentário bem específico para localizar',
            'commentable_id' => $post->id,
            'commentable_type' => Post::class,
        ]);

        Livewire::actingAs($admin)
            ->test(CommentIndex::class)
            ->assertSee(Str::limit($comment->body, 80));
    }

    public function test_search_filters_comments_by_body()
    {
        $admin = $this->admin();
        $author = User::factory()->create();
        $post = Post::factory()->create();

        Comment::create([
            'user_id' => $author->id,
            'body' => 'Comentário sobre corrida matinal',
            'commentable_id' => $post->id,
            'commentable_type' => Post::class,
        ]);

        Comment::create([
            'user_id' => $author->id,
            'body' => 'Outro assunto totalmente diferente',
            'commentable_id' => $post->id,
            'commentable_type' => Post::class,
        ]);

        Livewire::actingAs($admin)
            ->test(CommentIndex::class)
            ->set('search', 'corrida matinal')
            ->assertSee('Comentário sobre corrida matinal')
            ->assertDontSee('Outro assunto totalmente diferente');
    }

    public function test_deleting_a_comment_removes_it_and_its_replies()
    {
        $admin = $this->admin();
        $author = User::factory()->create();
        $post = Post::factory()->create();

        $parent = Comment::create([
            'user_id' => $author->id,
            'body' => 'Comentário pai',
            'commentable_id' => $post->id,
            'commentable_type' => Post::class,
        ]);

        $reply = Comment::create([
            'user_id' => $author->id,
            'body' => 'Resposta ao comentário pai',
            'commentable_id' => $post->id,
            'commentable_type' => Post::class,
            'parent_id' => $parent->id,
        ]);

        Livewire::actingAs($admin)
            ->test(CommentIndex::class)
            ->call('confirmDelete', $parent->id)
            ->call('delete');

        $this->assertDatabaseMissing('comments', ['id' => $parent->id]);
        $this->assertDatabaseMissing('comments', ['id' => $reply->id]);
    }

    public function test_bulk_delete_removes_selected_comments()
    {
        $admin = $this->admin();
        $author = User::factory()->create();
        $post = Post::factory()->create();

        $first = Comment::create([
            'user_id' => $author->id,
            'body' => 'Primeiro comentário',
            'commentable_id' => $post->id,
            'commentable_type' => Post::class,
        ]);

        $second = Comment::create([
            'user_id' => $author->id,
            'body' => 'Segundo comentário',
            'commentable_id' => $post->id,
            'commentable_type' => Post::class,
        ]);

        Livewire::actingAs($admin)
            ->test(CommentIndex::class)
            ->set('selected', [$first->id, $second->id])
            ->call('deleteSelected');

        $this->assertDatabaseMissing('comments', ['id' => $first->id]);
        $this->assertDatabaseMissing('comments', ['id' => $second->id]);
    }
}
