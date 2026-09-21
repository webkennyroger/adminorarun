<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StoryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // IDs dos usuários que eu sigo
        $followingIds = $user->following()->pluck('following_id')->toArray();

        // Incluir meu própro ID
        $followingIds[] = $user->id;

        // Buscar usuários com stories ativos
        $now = now()->toDateTimeString();
        $usersWithStories = User::whereIn('id', $followingIds)
            ->whereHas('stories', function ($query) use ($now) {
                $query->where('expires_at', '>', $now);
            })
            ->with(['stories' => function ($query) use ($now) {
                $query->where('expires_at', '>', $now)->orderBy('created_at', 'asc')->orderBy('id', 'asc');
            }, 'profile'])
            ->get();

        Log::info("Stories fetch (API) at $now: Found ".$usersWithStories->count().' users with stories.');

        $stories = $usersWithStories->map(function ($u) use ($user) {
            $userStories = $u->stories->map(function ($s) {
                $isVideo = preg_match('/\.(mp4|mov|avi|webm)$/i', $s->image_url);

                return [
                    'id' => $s->id,
                    'url' => $s->image_url,
                    'type' => $isVideo ? 'video' : 'image',
                    'duration' => $isVideo ? 15 : 5,
                    'created_at' => $s->created_at,
                ];
            });

            return [
                'user_id' => $u->id,
                'name' => $u->id === $user->id ? 'Seu story' : $u->name,
                'nickname' => $u->profile?->nickname ?? $u->name,
                'avatar' => $u->image_url,
                'has_story' => true,
                'is_own' => $u->id === $user->id,
                'stories' => $userStories,
                'latest_story_image' => $u->stories->last()->image_url,
                'expires_at' => $u->stories->last()->expires_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $stories,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi,webm|max:20480', // 20MB max
        ]);

        $user = $request->user();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('stories', 'public');

            $story = $user->stories()->create([
                'image_url' => asset('storage/'.$path),
                'expires_at' => now()->addHours(24),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Story criado com sucesso',
                'data' => $story,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Nenhum arquivo enviado',
        ], 400);
    }
}
