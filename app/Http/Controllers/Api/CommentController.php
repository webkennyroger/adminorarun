<?php

namespace App\Http\Controllers\Api;

use App\Events\CommentPosted;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Comment;
use App\Models\Post;
use App\Traits\ResolvesActivityItems;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{
    /**
     * List comments for an item (post, poll, activity).
     */
    public function index(Request $request, $id)
    {
        $item = $this->resolveItem($id);
        $user = $request->user();

        $comments = $item->comments()
            ->with(['user', 'likes', 'replies.user', 'replies.likes'])
            ->whereNull('parent_id')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $comments->map(fn ($c) => $this->formatComment($c, $user->id)),
        ]);
    }

    /**
     * Store comment on an item (post, poll, activity).
     */
    public function store(Request $request, $id)
    {
        $request->validate([
            'body' => 'nullable|string',
            'parent_id' => 'nullable|exists:comments,id',
            'image' => 'nullable|file|max:10240', // Permite todos arquivos no app para evitar bloqueio MIME
        ]);

        $item = $this->resolveItem($id);
        $user = $request->user();

        $mediaPath = null;
        if ($request->hasFile('image')) {
            $mediaPath = $request->file('image')->store('comments/media', 'public');
        }

        $comment = $item->comments()->create([
            'user_id' => $user->id,
            'body' => $request->body ?? '',
            'media_path' => $mediaPath,
            'parent_id' => ! empty($request->parent_id) ? $request->parent_id : null,
        ]);

        $formattedComment = $this->formatComment($comment->load('user'), $user->id);

        // Determinar o ID prefixado correto para o broadcast
        $prefixedId = $id;
        if (! is_string($id) || ! str_contains($id, '_')) {
            $prefix = ($item instanceof Activity) ? 'activity_' : (($item->type === 'poll') ? 'poll_' : 'post_');
            $prefixedId = $prefix.$item->id;
        }

        try {
            event(new CommentPosted($prefixedId, $formattedComment));
        } catch (\Throwable $e) {
            // Broadcast failure should not prevent comment creation
            Log::warning('CommentPosted broadcast failed: '.$e->getMessage());
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatComment($comment->load('user'), $user->id),
        ]);
    }

    /**
     * Update the specified comment.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'body' => 'required|string',
        ]);

        $comment = Comment::findOrFail($id);

        if ($comment->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $comment->update([
            'body' => $request->body,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comment updated successfully',
            'data' => $this->formatComment($comment->load('user'), $request->user()->id),
        ]);
    }

    /**
     * Remove the specified comment.
     */
    public function destroy(Request $request, $id)
    {
        $comment = Comment::findOrFail($id);

        if ($comment->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully',
        ]);
    }

    use ResolvesActivityItems;

    protected function formatComment($comment, $userId)
    {
        if (! $comment->relationLoaded('likes') || ! $comment->relationLoaded('user') || ! $comment->relationLoaded('replies')) {
            $comment->load(['user', 'likes', 'replies.user', 'replies.likes']);
        }

        return [
            'id' => (string) $comment->id,
            'userId' => (string) $comment->user_id,
            'userName' => $comment->user->name,
            'userAvatarUrl' => $comment->user->image_url,
            'text' => $comment->body,
            'mediaUrl' => $comment->media_url,
            'timestamp' => $comment->created_at->toIso8601String(),
            'parent_id' => (string) $comment->parent_id,
            'replies' => $comment->replies->map(function ($reply) use ($userId) {
                return $this->formatComment($reply, $userId);
            })->toArray(),
            'isArchived' => false,
            'likes' => $comment->likes->count(),
            'isLiked' => $comment->likes->where('user_id', $userId)->isNotEmpty(),
        ];
    }
}
