<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        // Development bypass / quick credentials
        if (config('app.env') === 'local') {
            // Se tentar logar com 'admin@dev.com', loga como o admin
            if ($request->email === 'admin@dev.com') {
                $user = User::whereHas('profile', fn ($q) => $q->where('role', 'admin'))->first();
            } else {
                $user = User::where('email', $request->email)->first();
            }

            if ($user) {
                $token = $user->createToken('auth_token')->plainTextToken;

                return response()->json([
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'user' => $user,
                ]);
            }
        }

        if (! Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Credenciais inválidas'],
            ]);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    public function googleLogin(Request $request)
    {
        $request->validate([
            'access_token' => 'required_without:id_token|string',
            'id_token' => 'required_without:access_token|string',
        ]);

        try {
            $googleUser = null;

            if ($request->filled('id_token')) {
                // Verify ID Token via Google API
                $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
                    'id_token' => $request->id_token,
                ]);

                if ($response->failed()) {
                    return response()->json([
                        'message' => 'Falha ao validar ID Token do Google.',
                        'error' => 'invalid_id_token',
                    ], 401);
                }

                $tokenData = $response->json();

                // Check audience (client_id)
                $clientId = config('services.google.client_id');
                if ($clientId && $tokenData['aud'] !== $clientId) {
                    return response()->json([
                        'message' => 'ID Token não pertence a esta aplicação.',
                        'error' => 'invalid_audience',
                    ], 401);
                }

                // Map to a generic object similar to Socialite User
                $googleUser = (object) [
                    'email' => $tokenData['email'] ?? null,
                    'name' => $tokenData['name'] ?? null,
                    'id' => $tokenData['sub'] ?? null,
                    'avatar' => $tokenData['picture'] ?? null,
                ];
            } else {
                /** @var GoogleProvider $driver */
                $driver = Socialite::driver('google');
                $user = $driver->stateless()->userFromToken($request->access_token);
                $googleUser = (object) [
                    'email' => $user->getEmail(),
                    'name' => $user->getName(),
                    'id' => $user->getId(),
                    'avatar' => $user->getAvatar(),
                ];
            }

            if (! $googleUser->email) {
                return response()->json([
                    'message' => 'E-mail não retornado pelo Google.',
                    'error' => 'no_email_provided',
                ], 401);
            }

            $user = User::where('email', $googleUser->email)->first();

            if (! $user) {
                $user = User::create([
                    'email' => $googleUser->email,
                    'name' => $googleUser->name ?? explode('@', $googleUser->email)[0],
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                    'password' => Hash::make(str()->random(24)),
                    'email_verified_at' => now(),
                ]);
            } else {
                $user->update([
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                ]);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ]);

        } catch (\Exception $e) {
            \Log::error('Google Login Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Falha ao fazer login com Google: '.$e->getMessage(),
                'error' => 'google_auth_failed',
            ], 401);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
