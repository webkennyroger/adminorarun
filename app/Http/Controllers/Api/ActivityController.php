<?php

namespace App\Http\Controllers\Api;

use App\Actions\Activities\DeleteActivity;
use App\Actions\Activities\UpdateActivity;
use App\Actions\Posts\DeletePost;
use App\Actions\Posts\UpdatePost;
use App\Http\Controllers\Controller;
use App\Jobs\MatchSegmentsForActivity;
use App\Models\Activity;
use App\Models\Post;
use App\Models\User;
use App\Traits\ResolvesActivityItems;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class ActivityController extends Controller
{
    use ResolvesActivityItems;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $feed = $request->query('feed', 'personal');
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(20, max(5, (int) $request->query('per_page', 15)));
        $offset = ($page - 1) * $perPage;

        // Activities Query
        $activitiesQuery = Activity::with([
            'user.profile',
            'likes',
            'savedItems',
            'comments' => function ($q) {
                $q->whereNull('parent_id')->oldest();
            },
        ]);

        // Posts Query (Simple posts and polls)
        $postsQuery = Post::with([
            'user.profile',
            'likes',
            'savedItems',
            'pollOptions',
            'pollVotes.user',
            'comments' => function ($q) {
                $q->whereNull('parent_id')->oldest();
            },
        ]);

        // Nunca mostrar conteúdo de usuários que o usuário atual bloqueou.
        $blockedUserIds = $user->blockedUsers()->pluck('users.id')->toArray();
        if (! empty($blockedUserIds)) {
            $activitiesQuery->whereNotIn('user_id', $blockedUserIds);
            $postsQuery->whereNotIn('user_id', $blockedUserIds);
        }

        if ($feed === 'timeline' || $feed === 'network') {
            $followingIds = $user->following()->pluck('following_id')->toArray();
            $followingIds[] = $user->id; // Incluir o próprio usuário

            $activitiesQuery->whereIn('user_id', $followingIds)
                ->where('privacy', 'public')
                ->where(function ($q) {
                    $q->where('feed_type', '!=', 'community')
                        ->orWhereNull('feed_type');
                });
            $postsQuery->whereIn('user_id', $followingIds)
                ->where('privacy', 'public')
                ->where(function ($q) {
                    $q->where('feed_type', '!=', 'community')
                        ->orWhereNull('feed_type');
                });
        } elseif ($feed === 'community') {
            $activitiesQuery->where('feed_type', 'community')->where('privacy', 'public');
            $postsQuery->where('feed_type', 'community')->where('privacy', 'public');
        } elseif ($feed === 'personal') {
            $activitiesQuery->where('user_id', $user->id);
            $postsQuery->where('user_id', $user->id);
        }

        $activities = $activitiesQuery->latest('start_time')->get();
        $posts = $postsQuery->latest()->get();

        // Merge and sort all items
        $merged = collect([])
            ->merge($activities->map(fn ($a) => [
                'type' => 'activity',
                'item' => $a,
                'date' => $a->start_time ?? $a->created_at,
            ]))
            ->merge($posts->map(fn ($p) => [
                'type' => 'post',
                'item' => $p,
                'date' => $p->created_at,
            ]))
            ->sortByDesc('date')
            ->values();

        $total = $merged->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $paginatedItems = $merged->slice($offset, $perPage)->values();

        $formatted = $paginatedItems->map(function ($entry) use ($user) {
            if ($entry['type'] === 'activity') {
                return $this->formatActivity($entry['item'], $user);
            } else {
                $post = $entry['item'];
                if ($post->type === 'poll') {
                    return $this->formatPoll($post, $user);
                } else {
                    return $this->formatPost($post, $user);
                }
            }
        });

        return response()->json([
            'success' => true,
            'data' => $formatted,
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $lastPage,
                'has_more_pages' => $page < $lastPage,
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function formatPost(Post $post, ?User $user): array
    {
        // Garante que mediaPaths é sempre um array de strings
        $media = $post->media ?? [];
        if (is_string($media)) {
            $media = json_decode($media, true) ?? [];
        }

        return [
            'id' => 'post_'.$post->id,
            'type' => 'post',
            'title' => $post->title,
            'userId' => (string) $post->user_id,
            'user_id' => (string) $post->user_id,
            'userName' => $post->user->name,
            'userAvatarUrl' => $post->user->image_url,
            'createdAt' => $post->created_at->toIso8601String(),
            'content' => $post->content,
            'notes' => $post->content,
            'description' => $post->content,
            'mediaPaths' => array_values(array_filter((array) $media)),
            'likes' => $post->likes->count(),
            'isLiked' => $user ? $post->likes->where('user_id', $user->id)->isNotEmpty() : false,
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

    /**
     * @return array<string, mixed>
     */
    public function formatPoll(Post $post, ?User $user): array
    {
        $hasVoted = $user ? $post->pollVotes->where('user_id', $user->id)->isNotEmpty() : false;
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
                    'isUserVote' => $user ? $post->pollVotes->where('user_id', $user->id)->where('poll_option_id', $opt->id)->isNotEmpty() : false,
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
            'isLiked' => $user ? $post->likes->where('user_id', $user->id)->isNotEmpty() : false,
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

    /**
     * Get activity history for the current user.
     */
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = (int) $request->get('per_page', 20);

        $activities = Activity::with(['user.profile', 'likes', 'savedItems'])
            ->where('user_id', $user->id)
            ->whereNotNull('sport_type')
            ->latest('start_time')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $activities->map(function ($activity) use ($user) {
                return $this->formatActivity($activity, $user);
            }),
            'pagination' => [
                'current_page' => $activities->currentPage(),
                'last_page' => $activities->lastPage(),
                'per_page' => $activities->perPage(),
                'total' => $activities->total(),
            ],
        ]);
    }

    /**
     * Store or update an activity from the mobile app.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id' => 'nullable|string',
            'activityTitle' => 'required|string',
            'sport' => 'required|string',
            'createdAt' => 'required|date',
            'distanceInMeters' => 'required|numeric',
            'durationInSeconds' => 'required|integer',
            'routePoints' => 'nullable|array',
            'calories' => 'nullable|numeric',
            'privacy' => 'nullable|string',
            'location' => 'nullable|string',
            'feedType' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        $taggedUsers = [];
        if ($request->has('taggedPartnerIds')) {
            $taggedIds = $request->taggedPartnerIds;
            if (is_array($taggedIds) && count($taggedIds) > 0) {
                $taggedUsers = User::whereIn('id', $taggedIds)
                    ->select('id', 'name', 'avatar')
                    ->get()
                    ->toArray();
            }
        }

        $polylines = $request->routePoints ?? [];
        if (is_array($polylines) && ! empty($polylines)) {
            if (! isset($polylines['summary_polyline'])) {
                $summary = $this->encodePolyline($polylines);
                $polylines = [
                    'points' => $polylines,
                    'summary_polyline' => $summary,
                ];
            }
        } elseif (empty($polylines)) {
            $polylines = null;
        }

        $activity = Activity::updateOrCreate(
            [
                'app_id' => $request->id ?? null,
                'user_id' => $user->id,
            ],
            [
                'title' => $request->activityTitle,
                'sport_type' => $request->sport,
                'start_time' => Carbon::parse($request->createdAt),
                'distance' => $request->distanceInMeters,
                'duration' => $request->durationInSeconds,
                'calories' => $request->calories ?? 0,
                'polylines' => $polylines,
                'privacy' => $request->privacy ?? 'public',
                'location' => $request->location ?? '',
                'feed_type' => $request->feedType ?? 'personal',
                'description' => $request->description ?? '',
                'mood' => $request->mood,
                'media' => $request->mediaPaths ?? [],
                'tagged_users' => $taggedUsers,
            ]
        );

        MatchSegmentsForActivity::dispatch($activity->id);

        return response()->json([
            'success' => true,
            'data' => $this->formatActivity($activity, $user),
        ]);
    }

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|mimes:jpeg,png,jpg,gif,svg,webp,mp4,mov,avi,wmv,ogg,qt|max:102400', // 100MB max per file
        ]);

        $paths = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('activities_media', 'public');
                $paths[] = url('storage/'.$path);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $paths,
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        /** @var Activity|Post $item */
        $item = $this->resolveItem($id);
        /** @var User $user */
        $user = $request->user();

        if ($item->user_id != $user->id && ! $user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'activityTitle' => 'nullable|string',
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'privacy' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        if ($item instanceof Activity) {
            $action = new UpdateActivity;
            $item = $action->execute($item, [
                'title' => $request->activityTitle ?? $request->title,
                'description' => $request->description ?? $request->input('content'),
                'privacy' => $request->privacy,
            ]);
            $formatted = $this->formatActivity($item, $user);
        } else {
            $action = new UpdatePost;
            $item = $action->execute($item, [
                'title' => $request->title ?? $request->activityTitle,
                'content' => $request->input('content') ?? $request->description,
                'privacy' => $request->privacy,
            ]);
            $formatted = $this->formatPost($item, $user);
        }

        return response()->json([
            'success' => true,
            'data' => $formatted,
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        /** @var Activity|Post $item */
        $item = $this->resolveItem($id);
        /** @var User $user */
        $user = $request->user();

        if ($item->user_id != $user->id && ! $user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($item instanceof Activity) {
            (new DeleteActivity)->execute($item);
        } else {
            (new DeletePost)->execute($item);
        }

        return response()->json(['success' => true]);
    }

    /**
     * @return array<string, mixed>
     */
    public function formatActivity(Activity $activity, ?User $user): array
    {
        $routePoints = $activity->polylines;
        if (is_array($routePoints) && isset($routePoints['points'])) {
            $routePoints = $routePoints['points'];
        }

        // Garante que mediaPaths é sempre um array de strings
        $media = $activity->media ?? [];
        if (is_string($media)) {
            $media = json_decode($media, true) ?? [];
        }

        return [
            'id' => 'activity_'.$activity->id,
            'userId' => (string) $activity->user_id,
            'user_id' => (string) $activity->user_id,
            'userName' => $activity->user->name,
            'userAvatarUrl' => $activity->user->image_url,
            'type' => 'activity',
            'activityTitle' => $activity->title,
            'sport' => $activity->sport_type,
            'createdAt' => ($activity->start_time ?? $activity->created_at)->toIso8601String(),
            'distanceInMeters' => (float) $activity->distance,
            'durationInSeconds' => (int) $activity->duration,
            'calories' => (float) ($activity->calories ?? 0),
            'location' => $activity->location ?? '',
            'notes' => $activity->description ?? null,
            'routePoints' => $routePoints ?? [],
            'mediaPaths' => array_values(array_filter((array) $media)),
            'likes' => $activity->likes->count(),
            'isLiked' => $user ? $activity->likes->where('user_id', $user->id)->isNotEmpty() : false,
            'isSaved' => $user ? $activity->savedItems->where('user_id', $user->id)->isNotEmpty() : false,
            'commentsList' => $activity->comments->map(function ($comment) use ($user) {
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
            'privacy' => $activity->privacy ?? 'public',
            'feedType' => $activity->feed_type ?? 'personal',
            'shares' => 0,
        ];
    }

    /**
     * Google Polyline Algorithm Implementation
     */
    private function encodePolyline($points)
    {
        if (empty($points)) {
            return '';
        }
        $res = '';
        $last_lat = 0;
        $last_lng = 0;
        foreach ($points as $point) {
            $lat = round($point['lat'] * 1e5);
            $lng = round($point['lng'] * 1e5);
            $d_lat = $lat - $last_lat;
            $d_lng = $lng - $last_lng;
            $res .= $this->encodeSignedNumber($d_lat).$this->encodeSignedNumber($d_lng);
            $last_lat = $lat;
            $last_lng = $lng;
        }

        return $res;
    }

    private function encodeSignedNumber($n)
    {
        $sgn_num = $n << 1;
        if ($n < 0) {
            $sgn_num = ~($sgn_num);
        }

        return $this->encodeNumber($sgn_num);
    }

    private function encodeNumber($n)
    {
        $res = '';
        while ($n >= 0x20) {
            $res .= chr((0x20 | ($n & 0x1F)) + 63);
            $n >>= 5;
        }
        $res .= chr($n + 63);

        return $res;
    }
}
