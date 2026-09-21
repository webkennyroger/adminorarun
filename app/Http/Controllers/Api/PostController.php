<?php

namespace App\Http\Controllers\Api;

use App\Actions\Posts\CreatePost;
use App\Actions\Posts\DeletePost;
use App\Actions\Posts\UpdatePost;
use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Traits\ResolvesActivityItems;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    use ResolvesActivityItems;

    /**
     * Store a newly created post in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'nullable|string',
            'title' => 'nullable|string',
            'mediaPaths' => 'nullable|array',
            'privacy' => 'nullable|in:public,friends,private',
            'feedType' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        $post = (new CreatePost)->execute([
            'user_id' => $user->id,
            'title' => $request->title,
            'content' => $request->input('content') ?? '',
            'type' => 'post',
            'media' => $request->mediaPaths ?? [],
            'privacy' => $request->privacy ?? 'public',
            'feed_type' => $request->feedType ?? 'personal',
        ]);

        $post->load(['user', 'likes', 'comments.user']);

        return response()->json([
            'success' => true,
            'message' => 'Post criado com sucesso',
            'data' => $this->formatPost($post, $user),
        ], 201);
    }

    /**
     * Update the specified post.
     */
    public function update(Request $request, $id)
    {
        $item = $this->resolveItem($id);
        $user = $request->user();

        if ($item->user_id != $user->id && ! $user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'nullable|string',
            'title' => 'nullable|string',
            'privacy' => 'nullable|in:public,friends,private',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $item = (new UpdatePost)->execute($item, [
            'title' => $request->title,
            'content' => $request->input('content') ?? $request->input('notes') ?? $request->input('description'),
            'privacy' => $request->privacy,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Post atualizado com sucesso',
            'data' => $this->formatPost($item->refresh(), $user),
        ]);
    }

    /**
     * Remove the specified post.
     */
    public function destroy(Request $request, $id)
    {
        $item = $this->resolveItem($id);
        $user = $request->user();

        if ($item->user_id != $user->id && ! $user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        (new DeletePost)->execute($item);

        return response()->json([
            'success' => true,
            'message' => 'Post removido com sucesso',
        ]);
    }

    public function formatPost($post, $user)
    {
        return [
            'id' => 'post_'.$post->id,
            'type' => 'post',
            'title' => $post->title,
            'user_id' => (string) $post->user_id,
            'userName' => $post->user->name,
            'userAvatarUrl' => $post->user->image_url,
            'createdAt' => $post->created_at->toIso8601String(),
            'description' => $post->content,
            'mediaPaths' => $post->media ?? [],
            'likes' => $post->likes->count(),
            'isLiked' => $post->likes->where('user_id', $user->id)->isNotEmpty(),
            'isSaved' => $user ? $post->savedItems->where('user_id', $user->id)->isNotEmpty() : false,
            'isArchived' => (bool) ($post->is_archived ?? false),
            'commentsList' => $post->comments->map(function ($comment) use ($user) {
                return [
                    'id' => (string) $comment->id,
                    'user_id' => (string) $comment->user_id,
                    'userName' => $comment->user->name,
                    'userAvatarUrl' => $comment->user->image_url,
                    'text' => $comment->body,
                    'mediaUrl' => $comment->media_url,
                    'timestamp' => $comment->created_at->toIso8601String(),
                    'parent_id' => (string) $comment->parent_id,
                    'likes' => $comment->likes->count(),
                    'isLiked' => $user ? $comment->likes->where('user_id', $user->id)->isNotEmpty() : false,
                    'replies' => $comment->replies->map(function ($reply) use ($user) {
                        return [
                            'id' => (string) $reply->id,
                            'user_id' => (string) $reply->user_id,
                            'userName' => $reply->user->name,
                            'userAvatarUrl' => $reply->user->image_url,
                            'text' => $reply->body,
                            'mediaUrl' => $reply->media_url,
                            'timestamp' => $reply->created_at->toIso8601String(),
                            'parent_id' => (string) $reply->parent_id,
                            'likes' => $reply->likes->count(),
                            'isLiked' => $user ? $reply->likes->where('user_id', $user->id)->isNotEmpty() : false,
                        ];
                    })->toArray(),
                ];
            })->toArray(),
            'shares' => 0,
            'privacy' => $post->privacy,
        ];
    }
}
