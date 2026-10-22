@extends('layouts.auth')
@section('title', 'Confirm password')
@section('card_class', 'au-card--center')

@section('content')
    <div class="au-icon-lg"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i></div>
    <h1 class="au-title">Confirm your password</h1>
    <p class="au-sub">This is a secure area. Enter your password to continue.</p>

    <form method="POST" action="{{ route('password.confirm') }}" data-loading style="text-align:left">
        @csrf

        <x-auth.field name="password" type="password" label="Password" icon="fa-solid fa-lock"
            placeholder="Your password" autocomplete="current-password" required autofocus />

        <button type="submit" class="au-btn au-btn--primary" data-loading-text="Checking…" data-text="Confirm">
            <span class="au-spin" aria-hidden="true"></span><span class="au-btn-text">Confirm</span>
        </button>
    </form>
@endsection
