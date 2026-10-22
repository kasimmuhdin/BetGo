@extends('layouts.auth')
@section('title', 'Log in')

@section('content')
    <h1 class="au-title">Welcome back</h1>
    <p class="au-sub">Log in to manage your listings, messages and saved homes.</p>

    @if (session('status'))
        <div class="au-alert au-alert--ok" role="status"><i class="fa-solid fa-circle-check" aria-hidden="true"></i>
            {{ session('status') }}</div>
    @endif

    @if (config('services.google.client_id'))
        <a href="{{ route('auth.google') }}" class="au-btn au-btn--google" data-loading-link
            data-loading-text="Opening Google…" data-text="Continue with google">
            <span class="au-spin" aria-hidden="true"></span>
            <svg class="au-google-logo" viewBox="0 0 48 48" width="20" height="20" aria-hidden="true">
                <path fill="#EA4335"
                    d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z" />
                <path fill="#4285F4"
                    d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z" />
                <path fill="#FBBC05"
                    d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z" />
                <path fill="#34A853"
                    d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z" />
            </svg>
            <span class="au-btn-text">Continue with google</span>
        </a>
        <div class="au-or"><span>or</span></div>
    @endif

    <form method="POST" action="{{ route('login') }}" data-loading>
        @csrf

        {{-- Email Field --}}
        <div class="au-field">
            <div class="au-label-row">
                <label for="email" class="au-label">Email</label>
            </div>
            <div @class(['au-input', 'has-error' => $errors->has('email')])>
                <i class="fa-regular fa-envelope au-ico" aria-hidden="true"></i>
                <input id="email" name="email" type="email" value="{{ old('email') }}"
                    placeholder="you@example.com" autocomplete="email" required autofocus autocapitalize="none"
                    autocorrect="off" spellcheck="false"
                    @if ($errors->has('email')) aria-describedby="email-err" aria-invalid="true" @endif>
            </div>
            @if ($errors->has('email'))
                <p class="au-err" id="email-err" role="alert">
                    <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> {{ $errors->first('email') }}
                </p>
            @endif
        </div>

        {{-- Password Field --}}
        <div class="au-field">
            <label for="password" class="au-label mb-2">Password</label>

            <div @class(['au-input', 'has-error' => $errors->has('password')])>

                <i class="fa-solid fa-lock au-ico" aria-hidden="true"></i>
                <input id="password" name="password" type="password" placeholder="Your password"
                    autocomplete="current-password" required
                    @if ($errors->has('password')) aria-describedby="password-err" aria-invalid="true" @endif>
                <button type="button" class="au-eye" data-toggle-password aria-label="Show password" aria-pressed="false">
                    <i class="fa-regular fa-eye" aria-hidden="true"></i>
                </button>
            </div>
            <div class="au-label-row">
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}">Forgot password?</a>
                @endif
            </div>
            @if ($errors->has('password'))
                <p class="au-err" id="password-err" role="alert">
                    <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> {{ $errors->first('password') }}
                </p>
            @endif
        </div>

        <div class="au-row">
            <label class="au-check">
                <input type="checkbox" name="remember" value="1" @checked(old('remember'))> Keep me logged in
            </label>
        </div>

        <button type="submit" class="au-btn au-btn--primary" data-loading-text="Logging in…" data-text="Log in">
            <span class="au-spin" aria-hidden="true"></span><span class="au-btn-text">Log in</span>
        </button>
    </form>

    <p class="au-foot">New to {{ config('betgo.name', 'BetGo') }}? <a href="{{ route('register') }}">Create an account</a>
    </p>
@endsection
