@extends('layouts.auth')
@section('title', 'Create account')

@section('content')
    <h1 class="au-title">Create your account</h1>
    <p class="au-sub">Free for renters and buyers. Owners and agents get verified after signing up.</p>

    <x-auth.google>Sign up with Google</x-auth.google>

    <form method="POST" action="{{ route('register.store') }}" data-loading>
        @csrf

        {{-- Name Field --}}
        <div class="au-field">
            <div class="au-label-row">
                <label for="name" class="au-label">Full name</label>
            </div>
            <div @class(['au-input', 'has-error' => $errors->has('name')])>
                <i class="fa-regular fa-user au-ico" aria-hidden="true"></i>
                <input id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Your full name"
                    autocomplete="name" maxlength="255" required autofocus
                    @if ($errors->has('name')) aria-describedby="name-err" aria-invalid="true" @endif>
            </div>
            @if ($errors->has('name'))
                <p class="au-err" id="name-err" role="alert">
                    <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> {{ $errors->first('name') }}
                </p>
            @endif
        </div>

        {{-- Email Field --}}
        <div class="au-field">
            <div class="au-label-row">
                <label for="email" class="au-label">Email</label>
            </div>
            <div @class(['au-input', 'has-error' => $errors->has('email')])>
                <i class="fa-regular fa-envelope au-ico" aria-hidden="true"></i>
                <input id="email" name="email" type="email" value="{{ old('email') }}"
                    placeholder="you@example.com" autocomplete="email" maxlength="255" required autocapitalize="none"
                    autocorrect="off" spellcheck="false"
                    @if ($errors->has('email')) aria-describedby="email-err" aria-invalid="true" @endif>
            </div>
            @if ($errors->has('email'))
                <p class="au-err" id="email-err" role="alert">
                    <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> {{ $errors->first('email') }}
                </p>
            @endif
        </div>

        {{-- Phone Field --}}
        <div class="au-field">
            <div class="au-label-row">
                <label for="phone" class="au-label">Phone number</label>
            </div>
            <div @class(['au-input', 'has-error' => $errors->has('phone')])>
                <i class="fa-solid fa-phone au-ico" aria-hidden="true"></i>
                <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" placeholder="0911 234 567"
                    autocomplete="tel" inputmode="tel" maxlength="20" required
                    aria-describedby="{{ $errors->has('phone') ? 'phone-err' : 'phone-hint' }}"
                    @if ($errors->has('phone')) aria-invalid="true" @endif>
            </div>
            @if ($errors->has('phone'))
                <p class="au-err" id="phone-err" role="alert">
                    <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> {{ $errors->first('phone') }}
                </p>
            @else
                <p class="au-hint" id="phone-hint">Ethiopian mobile number. +251 911 234 567 also works.</p>
            @endif
        </div>

        {{-- Password Field --}}
        <div class="au-field">
            <div class="au-label-row">
                <label for="password" class="au-label">Password</label>
            </div>
            <div @class(['au-input', 'has-error' => $errors->has('password')])>
                <i class="fa-solid fa-lock au-ico" aria-hidden="true"></i>
                <input id="password" name="password" type="password" placeholder="At least 8 characters"
                    autocomplete="new-password" required data-strength
                    aria-describedby="{{ $errors->has('password') ? 'password-err' : 'password-hint' }}"
                    @if ($errors->has('password')) aria-invalid="true" @endif>
                <button type="button" class="au-eye" data-toggle-password aria-label="Show password" aria-pressed="false">
                    <i class="fa-regular fa-eye" aria-hidden="true"></i>
                </button>
            </div>
            <div class="au-meter" data-meter data-level="0" aria-hidden="true">
                <span></span><span></span><span></span><span></span>
            </div>
            @if ($errors->has('password'))
                <p class="au-err" id="password-err" role="alert">
                    <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> {{ $errors->first('password') }}
                </p>
            @else
                <p class="au-hint" id="password-hint">Use 8 or more characters. Mix letters and numbers for a stronger
                    password.</p>
            @endif
        </div>

        {{-- Confirm Password Field --}}
        <div class="au-field">
            <div class="au-label-row">
                <label for="password_confirmation" class="au-label">Confirm password</label>
            </div>
            <div @class([
                'au-input',
                'has-error' => $errors->has('password_confirmation'),
            ])>
                <i class="fa-solid fa-lock au-ico" aria-hidden="true"></i>
                <input id="password_confirmation" name="password_confirmation" type="password"
                    placeholder="Type the password again" autocomplete="new-password" required
                    @if ($errors->has('password_confirmation')) aria-describedby="password_confirmation-err" aria-invalid="true" @endif>
                <button type="button" class="au-eye" data-toggle-password aria-label="Show password"
                    aria-pressed="false">
                    <i class="fa-regular fa-eye" aria-hidden="true"></i>
                </button>
            </div>
            @if ($errors->has('password_confirmation'))
                <p class="au-err" id="password_confirmation-err" role="alert">
                    <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                    {{ $errors->first('password_confirmation') }}
                </p>
            @endif
        </div>

        <button type="submit" class="au-btn au-btn--primary" data-loading-text="Creating account…"
            data-text="Create account">
            <span class="au-spin" aria-hidden="true"></span><span class="au-btn-text">Create account</span>
        </button>

        @if (Route::has('pages.show'))
            <p class="au-legal">
                By creating an account you agree to our
                <a href="{{ route('pages.show', 'terms') }}">Terms</a> and
                <a href="{{ route('pages.show', 'privacy') }}">Privacy Policy</a>.
            </p>
        @endif
    </form>

    <p class="au-foot">Already have an account? <a href="{{ route('login') }}">Log in</a></p>
@endsection
