<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        try {
            Password::sendResetLink(['email' => Str::lower(trim($request->input('email')))]);
        } catch (Throwable $e) {
            report($e);

            return back()->withInput()
                ->withErrors(['email' => 'We could not send the email right now. Please try again in a few minutes.']);
        }

        // Same answer whether or not the account exists, so this page cannot be used to find registered emails.
        return back()->with('status', 'If an account exists for that email, a reset link is on its way.');
    }
}
