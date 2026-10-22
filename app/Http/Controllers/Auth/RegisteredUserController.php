<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Throwable;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();

        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Hash::make($data['password']),
                'role' => 'user', // do not depend on the database default
            ]);
        } catch (UniqueConstraintViolationException) {
            // Two requests at the same moment: the database unique index caught the second one.
            return back()
                ->withInput($request->safe()->except(['password', 'password_confirmation']))
                ->withErrors(['email' => 'An account with these details already exists. Try logging in instead.']);
        }

        $mailFailed = false;
        try {
            event(new Registered($user));
        } catch (Throwable $e) {
            report($e);
            $mailFailed = true;
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('verification.notice')
            ->with('status', $mailFailed ? 'verification-link-failed' : 'verification-link-first');
    }
}