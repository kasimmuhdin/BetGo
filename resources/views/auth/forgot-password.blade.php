@extends('layouts.auth')
@section('title', 'Reset password')

@section('content')
    <h1 class="au-title">Forgot your password?</h1>
    <p class="au-sub">Enter your email and we will send you a link to choose a new one.</p>

    @if (session('status'))
        <div class="au-alert au-alert--ok" role="status"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> {{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" data-loading>
        @csrf

        <x-auth.field name="email" type="email" label="Email" icon="fa-regular fa-envelope"
            placeholder="you@example.com" autocomplete="email" required autofocus />

        <button type="submit" class="au-btn au-btn--primary" data-loading-text="Sending…" data-text="Send reset link">
            <span class="au-spin" aria-hidden="true"></span><span class="au-btn-text">Send reset link</span>
        </button>
    </form>

    <p class="au-foot"><a href="{{ route('login') }}">Back to log in</a></p>
@endsection
