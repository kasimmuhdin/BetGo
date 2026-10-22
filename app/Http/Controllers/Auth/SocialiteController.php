<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialiteController extends Controller
{
    public function redirect(): RedirectResponse
    {
        abort_unless(config('services.google.client_id'), 404);

        // Always show the account chooser, so people on shared computers can pick the right one.
        return Socialite::driver('google')->with(['prompt' => 'select_account'])->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->filled('error')) {
            return $this->fail('Google sign-in was cancelled. You can try again or use your email.');
        }

        try {
            $google = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            report($e);

            return $this->fail('We could not sign you in with Google. Please try again.');
        }

        $email = Str::lower(trim((string) $google->getEmail()));
        $verified = filter_var(
            $google->user['email_verified'] ?? $google->user['verified_email'] ?? false,
            FILTER_VALIDATE_BOOLEAN
        );

        if ($email === '' || ! $verified) {
            return $this->fail('Your Google email address is not verified, so we cannot use it.');
        }

        $user = User::where('google_id', $google->getId())->first()
            ?? User::where('email', $email)->first();

        if ($user) {
            $attributes = [
                'google_id' => $user->google_id ?: $google->getId(),
                'avatar' => $user->avatar ?: $google->getAvatar(),
                'email_verified_at' => $user->email_verified_at ?? now(),
            ];

            // This email was never confirmed, so the person who registered it may not own it.
            // Google just proved the real owner: remove the old password, "remember me" and sessions.
            if (! $user->email_verified_at) {
                $attributes['password'] = Hash::make(Str::random(40));
                $attributes['remember_token'] = Str::random(60);

                if (config('session.driver') === 'database') {
                    DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
                }
            }

            $user->forceFill($attributes)->save();
        } else {
            $user = new User();
            $user->forceFill([
                'name' => $google->getName() ?: Str::before($email, '@'),
                'email' => $email,
                'google_id' => $google->getId(),
                'avatar' => $google->getAvatar(),
                'password' => Hash::make(Str::random(40)), // unusable; they can use "Forgot password" later
                'role' => 'user',
                'email_verified_at' => now(),
            ])->save();
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended($user->dashboardUrl());
    }

    private function fail(string $message): RedirectResponse
    {
        return redirect()->route('login')->withErrors(['email' => $message]);
    }
}
