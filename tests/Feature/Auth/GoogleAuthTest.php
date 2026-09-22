<?php

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\Two\User as SocialiteUser;

test('new user is created and logged in on first google login', function () {
    $googleUser = new SocialiteUser;
    $googleUser->map([
        'id' => 'google-123',
        'name' => 'Nova Corredora',
        'email' => 'nova.corredora@example.com',
        'avatar' => 'https://example.com/avatar.png',
    ]);

    $provider = Mockery::mock(GoogleProvider::class);
    $provider->shouldReceive('user')->once()->andReturn($googleUser);
    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $response = $this->get(route('auth.google.callback'));

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'email' => 'nova.corredora@example.com',
        'google_id' => 'google-123',
    ]);

    // A regular (non admin/manager) user is sent to billing, not the admin dashboard.
    $response->assertRedirect('/billing');
});

test('existing admin is logged in and redirected to dashboard on google login', function () {
    $admin = User::factory()->create(['email' => 'admin.runner@example.com']);
    $admin->profile()->update(['role' => 'admin']);

    $googleUser = new SocialiteUser;
    $googleUser->map([
        'id' => 'google-456',
        'name' => $admin->name,
        'email' => $admin->email,
        'avatar' => 'https://example.com/avatar2.png',
    ]);

    $provider = Mockery::mock(GoogleProvider::class);
    $provider->shouldReceive('user')->once()->andReturn($googleUser);
    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $response = $this->get(route('auth.google.callback'));

    $this->assertAuthenticatedAs($admin->fresh());
    $response->assertRedirect(route('dashboard'));

    $this->assertDatabaseHas('users', [
        'id' => $admin->id,
        'google_id' => 'google-456',
    ]);
});

test('google login failure redirects back to login with an error', function () {
    Socialite::shouldReceive('driver')->with('google')->andThrow(new Exception('provider unreachable'));

    $response = $this->get(route('auth.google.callback'));

    $response->assertRedirect('/login');
    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});
