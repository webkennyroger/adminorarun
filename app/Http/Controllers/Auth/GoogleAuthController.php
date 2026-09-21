<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function handleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = User::where('email', $googleUser->getEmail())->first();

            if (! $user) {
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'password' => Hash::make(Str::random(24)),
                    'email_verified_at' => now(), // Auto-verify Google users
                ]);
            } else {
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                ]);
            }

            Auth::login($user);

            // /home nunca existiu como rota — o admin não é mais um site
            // público com feed, só o painel de gestão do app.
            $destination = ($user->isAdmin() || $user->isManager())
                ? route('dashboard')
                : '/billing';

            return redirect()->intended($destination);
        } catch (\Exception $e) {
            dd($e->getMessage()); // Debugging: Stop loop and show error
            // return redirect('/login')->withErrors(['email' => 'Unable to login with Google. Please try again.']);
        }
    }
}
