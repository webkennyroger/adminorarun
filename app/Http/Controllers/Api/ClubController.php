<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Club;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClubController extends Controller
{
    public function index(Request $request)
    {
        $query = Club::query();

        // Filter by category
        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // Filter by location
        if ($request->has('city')) {
            $query->where('city', 'like', '%'.$request->city.'%');
        }
        if ($request->has('state')) {
            $query->where('state', $request->state);
        }

        // Search by name
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('description', 'like', '%'.$request->search.'%');
            });
        }

        // Sort
        $sortBy = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');
        $query->orderBy($sortBy, $order);

        $clubs = $query->paginate($request->get('per_page', 15));

        // Add is_following for each club
        $userId = Auth::id();
        $clubs->getCollection()->transform(function ($club) use ($userId) {
            $club->is_following = $club->isMember($userId);

            return $club;
        });

        return response()->json($clubs);
    }

    public function myClubs(Request $request)
    {
        $userId = Auth::id();
        $clubs = Club::whereHas('members', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->get();

        $clubs->transform(function ($club) use ($userId) {
            $club->is_following = true;
            $club->role = $club->getMemberRole($userId);

            return $club;
        });

        return response()->json($clubs);
    }

    public function show($id)
    {
        $club = Club::findOrFail($id);
        $club->is_following = $club->isMember(Auth::id());

        return response()->json($club);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'is_public' => 'boolean',
            'image' => 'nullable|image|max:2048',
            'avatar' => 'nullable|image|max:1024',
        ]);

        $user = Auth::user();

        $club = Club::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'city' => $validated['city'],
            'state' => $validated['state'],
            'category' => $validated['category'],
            'is_public' => $validated['is_public'] ?? true,
            'creator_id' => $user->id,
            'creator_name' => $user->name,
            'members_count' => 1,
            'image' => 'assets/images/defaults/club_cover.png',
            'avatar' => 'assets/images/defaults/club_avatar.png',
        ]);

        // Add creator as member with role 'creator'
        $club->members()->attach($user->id, ['role' => 'creator']);

        $club->is_following = true;
        $club->role = 'creator';

        return response()->json($club, 201);
    }

    public function update(Request $request, $id)
    {
        $club = Club::findOrFail($id);

        // Check if user is admin/creator of the club
        $role = $club->getMemberRole(Auth::id());
        if (! in_array($role, ['creator', 'admin'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'city' => 'sometimes|string|max:255',
            'state' => 'sometimes|string|max:255',
            'category' => 'sometimes|string|max:255',
            'is_public' => 'sometimes|boolean',
        ]);

        $club->update($validated);

        return response()->json($club);
    }

    public function destroy($id)
    {
        $club = Club::findOrFail($id);

        // Check if user is creator of the club
        $role = $club->getMemberRole(Auth::id());
        if ($role !== 'creator') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $club->delete();

        return response()->json(['message' => 'Club deleted successfully']);
    }

    public function join(Request $request, $id)
    {
        $club = Club::findOrFail($id);
        $user = Auth::user();

        if ($club->isMember($user->id)) {
            return response()->json(['message' => 'Already a member'], 400);
        }

        $club->members()->attach($user->id, ['role' => 'member']);
        $club->increment('members_count');

        return response()->json(['message' => 'Joined successfully', 'is_following' => true]);
    }

    public function leave(Request $request, $id)
    {
        $club = Club::findOrFail($id);
        $user = Auth::user();

        if (! $club->isMember($user->id)) {
            return response()->json(['message' => 'Not a member'], 400);
        }

        // Creator cannot leave
        $role = $club->getMemberRole($user->id);
        if ($role === 'creator') {
            return response()->json(['message' => 'Creator cannot leave the club. Transfer ownership or delete the club.'], 400);
        }

        $club->members()->detach($user->id);
        $club->decrement('members_count');

        return response()->json(['message' => 'Left successfully', 'is_following' => false]);
    }

    public function members($id)
    {
        $club = Club::findOrFail($id);
        $members = $club->members()->select('users.id', 'users.name', 'users.image_url')
            ->withPivot('role')
            ->get()
            ->map(function ($member) {
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'avatar_url' => $member->image_url,
                    'role' => $member->pivot->role,
                ];
            });

        return response()->json($members);
    }

    public function categories()
    {
        $categories = Club::distinct()->pluck('category')->filter();

        return response()->json($categories);
    }
}
