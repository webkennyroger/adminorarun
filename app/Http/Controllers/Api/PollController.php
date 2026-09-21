<?php

namespace App\Http\Controllers\Api;

use App\Actions\Posts\CreatePost;
use App\Actions\Posts\DeletePost;
use App\Actions\Posts\UpdatePost;
use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Traits\ResolvesActivityItems;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class PollController extends Controller
{
    use ResolvesActivityItems;

    /**
     * Store a newly created poll in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question' => 'required|string',
            'options' => 'required|array|min:2',
            'options.*' => 'required|string',
            'expiresAt' => 'nullable|date',
            'privacy' => 'nullable|in:public,friends,private',
            'feedType' => 'nullable|string',
            'isMandatory' => 'nullable|boolean',
            'description' => 'nullable|string',
            'isMultiple' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        // Create Poll (as a Post with type='poll')
        $poll = (new CreatePost)->execute([
            'user_id' => $user->id,
            'title' => $request->question,
            'content' => $request->description ?? '',
            'type' => 'poll',
            'privacy' => $request->privacy ?? 'public',
            'feed_type' => $request->feedType ?? 'personal',
            'poll_expires_at' => $request->expiresAt ? Carbon::parse($request->expiresAt) : null,
            'poll_options' => $request->options,
            'meta' => ['isMultiple' => $request->isMultiple ?? false],
        ]);

        // Eager load relationships for formatting
        $poll->load(['user', 'pollOptions', 'pollVotes.user', 'likes', 'savedItems', 'comments' => function ($q) {
            $q->whereNull('parent_id')->latest();
        }]);

        return response()->json([
            'success' => true,
            'message' => 'Enquete criada com sucesso',
            'data' => $this->formatPoll($poll, $user),
        ], 201);
    }

    /**
     * Update the specified poll.
     */
    public function update(Request $request, $id)
    {
        $item = $this->resolveItem($id);
        $user = $request->user();

        if ($item->user_id != $user->id && ! $user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'question' => 'nullable|string',
            'description' => 'nullable|string',
            'privacy' => 'nullable|in:public,friends,private',
            'expiresAt' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $item = (new UpdatePost)->execute($item, [
            'title' => $request->question,
            'content' => $request->description,
            'privacy' => $request->privacy,
            'poll_expires_at' => $request->expiresAt ? Carbon::parse($request->expiresAt) : $item->poll_expires_at,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Enquete atualizada com sucesso',
            'data' => $this->formatPoll($item->refresh(), $user),
        ]);
    }

    /**
     * Remove the specified poll.
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
            'message' => 'Enquete removida com sucesso',
        ]);
    }

    /**
     * Vote on the specified poll.
     */
    public function vote(Request $request, $id)
    {
        $cleanId = str_replace(['post_', 'poll_'], '', $id);
        $request->validate(['option_id' => 'required|exists:poll_options,id']);

        $poll = Post::where('id', $cleanId)->where('type', 'poll')->firstOrFail();
        $user = $request->user();
        $isMultiple = (bool) (is_array($poll->meta) && ($poll->meta['isMultiple'] ?? false));

        if (! $isMultiple && $poll->hasVoted($user)) {
            return response()->json(['success' => false, 'message' => 'Você já votou nesta enquete'], 400);
        }

        // Check if already voted for THIS specific option
        if ($poll->pollVotes()->where('user_id', $user->id)->where('poll_option_id', $request->option_id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Você já votou nesta opção'], 400);
        }

        if ($poll->poll_expires_at && $poll->poll_expires_at->isPast()) {
            return response()->json(['success' => false, 'message' => 'Enquete expirada'], 400);
        }

        $option = $poll->pollOptions()->find($request->option_id);

        $poll->pollVotes()->create([
            'user_id' => $user->id,
            'poll_option_id' => $option->id,
        ]);

        $option->increment('votes_count');

        return response()->json([
            'success' => true,
            'data' => $this->formatPoll($poll->refresh(), $user),
        ]);
    }

    public function formatPoll($post, $user)
    {
        $hasVoted = $post->pollVotes->where('user_id', $user->id)->isNotEmpty();
        $totalVotes = $post->pollVotes->count();

        $meta = is_array($post->meta) ? $post->meta : [];
        $pollData = [
            'expiresAt' => $post->poll_expires_at ? $post->poll_expires_at->toIso8601String() : null,
            'isMandatory' => (bool) $post->is_mandatory,
            'isMultiple' => (bool) ($meta['isMultiple'] ?? false),
            'isExpired' => $post->poll_expires_at && $post->poll_expires_at->isPast(),
            'hasVoted' => $hasVoted,
            'totalVotes' => $totalVotes,
            'options' => $post->pollOptions->map(function ($opt) use ($user, $post, $totalVotes) {
                return [
                    'id' => (int) $opt->id,
                    'text' => $opt->option_text,
                    'votes' => (int) $opt->votes_count,
                    'percentage' => $totalVotes > 0 ? round(($opt->votes_count / $totalVotes) * 100) : 0,
                    'isUserVote' => $post->pollVotes->where('user_id', $user->id)->where('poll_option_id', $opt->id)->isNotEmpty(),
                ];
            })->values(),
        ];

        return [
            'id' => 'poll_'.$post->id,
            'type' => 'poll',
            'title' => $post->title,
            'description' => $post->content,
            'user_id' => (string) $post->user_id,
            'userName' => $post->user->name,
            'userAvatarUrl' => $post->user->image_url,
            'createdAt' => $post->created_at->toIso8601String(),
            'pollData' => $pollData,
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
        ];
    }
}
