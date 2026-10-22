@extends('layouts.guest')

@section('title', $page->title)
@section('meta_description', $page->meta_description ?:
    \Illuminate\Support\Str::limit(\App\Support\Markdown::plain($page->content), 155))
@section('full_width', '1')

@push('styles')
    @vite('resources/css/home.css')
@endpush

@php
    $icons = [
        'facebook' => 'fa-facebook-f',
        'instagram' => 'fa-instagram',
        'x' => 'fa-x-twitter',
        'youtube' => 'fa-youtube',
        'telegram' => 'fa-telegram',
    ];
@endphp

@section('content')
    <section class="hp-page-hero" aria-labelledby="page-title">
        <div class="hp-wrap">
            <h1 id="page-title" class="hp-h1">{{ $page->title }}</h1>
            <p class="hp-sub" style="margin:12px auto 0">Last updated {{ $page->updated_at->format('F j, Y') }}</p>
        </div>
    </section>

    <article class="hp-wrap" style="max-width:780px; padding-block:48px 24px">
        <div class="md-body">{{ $html }}</div>
    </article>

    @if ($page->slug === 'contact')
        <section class="hp-wrap" style="max-width:780px; padding-bottom:72px" aria-label="Contact details">
            @if ($contact || $social)
                <div class="hp-contact">
                    @isset($contact['email'])
                        <a class="hp-contact-card" href="mailto:{{ $contact['email'] }}">
                            <i class="fa-regular fa-envelope" aria-hidden="true"></i>
                            <span><strong>Email</strong>{{ $contact['email'] }}</span>
                        </a>
                    @endisset
                    @isset($contact['phone'])
                        <a class="hp-contact-card" href="tel:{{ preg_replace('/[^\d+]/', '', $contact['phone']) }}">
                            <i class="fa-solid fa-phone" aria-hidden="true"></i>
                            <span><strong>Phone</strong>{{ $contact['phone'] }}</span>
                        </a>
                    @endisset
                    @isset($contact['address'])
                        <div class="hp-contact-card">
                            <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                            <span><strong>Address</strong>{{ $contact['address'] }}</span>
                        </div>
                    @endisset
                </div>

                @if ($social)
                    <div class="hp-social">
                        @foreach ($social as $name => $url)
                            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
                                aria-label="{{ ucfirst($name) }}">
                                <i class="fa-brands {{ $icons[$name] ?? 'fa-link' }}" aria-hidden="true"></i>
                            </a>
                        @endforeach
                    </div>
                @endif
            @elseif (auth()->user()?->role === 'admin')
                <p class="hp-note">No contact details are set yet. <a href="{{ route('admin.pages.edit', $page) }}"
                        style="color:var(--accent)">Add them in the admin panel</a>.</p>
            @endif
        </section>
    @endif
@endsection
