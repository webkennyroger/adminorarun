<?php

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Support\Facades\Http;

/**
 * Estes testes simulam exatamente o que o app mobile faz: enviar um
 * id_token real do Google para /api/auth/google. Não temos (nem devemos
 * ter) uma senha real de Gmail para testar isso ao vivo — em vez disso,
 * simulamos a resposta que o endpoint oficial do Google
 * (oauth2.googleapis.com/tokeninfo) devolveria para um token válido,
 * exercitando o mesmo código que roda em produção a partir desse ponto.
 */
function fakeGoogleTokenInfo(string $email, string $name, string $sub = 'google-sub-123'): void
{
    Http::fake([
        'oauth2.googleapis.com/tokeninfo*' => Http::response([
            'email' => $email,
            'name' => $name,
            'sub' => $sub,
            'picture' => 'https://example.com/avatar.png',
            'aud' => 'any-client-id',
        ], 200),
    ]);
}

test('logging in with google using the existing admin email logs into that same admin account', function () {
    $this->seed(UserSeeder::class);

    $admin = User::where('email', 'orarunbr@gmail.com')->first();
    expect($admin)->not->toBeNull();
    expect($admin->isAdmin())->toBeTrue();
    expect($admin->google_id)->toBeNull();

    $countBefore = User::count();

    fakeGoogleTokenInfo('orarunbr@gmail.com', 'Kenny Roger');

    $response = $this->postJson('/api/auth/google', ['id_token' => 'fake-valid-token']);

    $response->assertOk()
        ->assertJsonPath('user.id', $admin->id)
        ->assertJsonPath('user.email', 'orarunbr@gmail.com');

    // No duplicate account was created — same row, now linked to Google.
    expect(User::count())->toBe($countBefore);

    $admin->refresh();
    expect($admin->google_id)->toBe('google-sub-123');
    expect($admin->isAdmin())->toBeTrue();

    // The issued token actually authenticates as this admin for a protected route.
    $token = $response->json('access_token');
    $me = $this->withHeader('Authorization', "Bearer {$token}")->getJson('/api/user');
    $me->assertOk()->assertJsonPath('id', $admin->id);
});

test('signing up with google using a brand new email creates a real account', function () {
    $email = 'nova.corredora.google@example.com';
    expect(User::where('email', $email)->exists())->toBeFalse();

    fakeGoogleTokenInfo($email, 'Nova Corredora', 'google-sub-456');

    $response = $this->postJson('/api/auth/google', ['id_token' => 'fake-valid-token']);

    $response->assertOk()
        ->assertJsonPath('user.email', $email)
        ->assertJsonPath('user.name', 'Nova Corredora');

    $this->assertDatabaseHas('users', [
        'email' => $email,
        'google_id' => 'google-sub-456',
    ]);

    $newUser = User::where('email', $email)->first();
    // A brand new Google sign-up gets a regular (non-admin) account.
    expect($newUser->isAdmin())->toBeFalse();

    $token = $response->json('access_token');
    $me = $this->withHeader('Authorization', "Bearer {$token}")->getJson('/api/user');
    $me->assertOk()->assertJsonPath('email', $email);
});

test('an invalid google token is rejected', function () {
    Http::fake([
        'oauth2.googleapis.com/tokeninfo*' => Http::response(['error' => 'invalid_token'], 400),
    ]);

    $response = $this->postJson('/api/auth/google', ['id_token' => 'not-a-real-token']);

    $response->assertStatus(401)->assertJsonPath('error', 'invalid_id_token');
});
