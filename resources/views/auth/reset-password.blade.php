@extends('layouts.auth')
@section('title', 'Choose a new password')

@section('content')
    <h1 class="au-title">Choose a new password</h1>
    <p class="au-sub">Pick a password you have not used on {{ config('betgo.name', 'BetGo') }} before.</p>

    <form method="POST" action="{{ route('password.store') }}" data-loading>
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <x-auth.field name="email" type="email" label="Email" icon="fa-regular fa-envelope"
            :value="$request->email" autocomplete="email" required />

        <x-auth.field name="password" type="password" label="New password" icon="fa-solid fa-lock"
            placeholder="At least 8 characters" autocomplete="new-password" :meter="true" required autofocus />

        <x-auth.field name="password_confirmation" type="password" label="Confirm new password" icon="fa-solid fa-lock"
            placeholder="Type the password again" autocomplete="new-password" required />

        <button type="submit" class="au-btn au-btn--primary" data-loading-text="Saving…" data-text="Save new password">
            <span class="au-spin" aria-hidden="true"></span><span class="au-btn-text">Save new password</span>
        </button>
    </form>
@endsection
