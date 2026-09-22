<?php

use App\Models\User;

test('a plain regular user can reach billing', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/billing');

    $response->assertOk();
});

test('an admin can also reach billing', function () {
    $admin = User::factory()->create();
    $admin->profile()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get('/billing');

    $response->assertOk();
});

test('a guest is redirected to login when visiting billing', function () {
    $response = $this->get('/billing');

    $response->assertRedirect(route('login'));
});
