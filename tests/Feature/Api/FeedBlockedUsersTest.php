<?php

use App\Models\Activity;
use App\Models\Post;
use App\Models\User;

test('timeline feed excludes posts and activities from a blocked user', function () {
    $me = User::factory()->create();
    $blocked = User::factory()->create();
    $me->following()->attach($blocked->id);
    $me->blockedUsers()->attach($blocked->id);

    Post::factory()->create([
        'user_id' => $blocked->id,
        'type' => 'post',
        'privacy' => 'public',
        'feed_type' => 'personal',
    ]);
    Activity::factory()->create([
        'user_id' => $blocked->id,
        'privacy' => 'public',
        'feed_type' => 'personal',
    ]);

    $response = $this->actingAs($me, 'sanctum')->getJson('/api/activities?feed=timeline');

    $response->assertOk();
    $userIds = collect($response->json('data'))->pluck('user_id');
    expect($userIds)->not->toContain((string) $blocked->id);
});

test('timeline feed still includes posts from users not blocked', function () {
    $me = User::factory()->create();
    $friend = User::factory()->create();
    $me->following()->attach($friend->id);

    $friendPost = Post::factory()->create([
        'user_id' => $friend->id,
        'type' => 'post',
        'privacy' => 'public',
        'feed_type' => 'personal',
    ]);

    $response = $this->actingAs($me, 'sanctum')->getJson('/api/activities?feed=timeline');

    $response->assertOk();
    $ids = collect($response->json('data'))->pluck('id');
    expect($ids)->toContain('post_'.$friendPost->id);
});

test('community feed excludes content from a blocked user', function () {
    $me = User::factory()->create();
    $blocked = User::factory()->create();
    $me->blockedUsers()->attach($blocked->id);

    Post::factory()->create([
        'user_id' => $blocked->id,
        'type' => 'post',
        'privacy' => 'public',
        'feed_type' => 'community',
    ]);

    $response = $this->actingAs($me, 'sanctum')->getJson('/api/activities?feed=community');

    $response->assertOk();
    $userIds = collect($response->json('data'))->pluck('user_id');
    expect($userIds)->not->toContain((string) $blocked->id);
});
