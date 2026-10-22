@php
    $navItems = \App\Support\PublicNav::items();
    $user = auth()->user();
@endphp

<header class="home-header">
    <a href="{{ route('home') }}" class="logo-wrapper" aria-label="{{ config('betgo.name') }} home">
        <span class="logo-icon"><img src="{{ asset('logo.svg') }}" alt="" width="48" height="48"></span>
        <span class="logo-text">{{ config('betgo.name') }}</span>
        <span class="logo-badge">Beta</span>
    </a>

    <nav class="home-nav" id="mainNav" aria-label="Main navigation">
        <div class="home-nav-items">
            <ul>
                @foreach ($navItems as $item)
                    <li>
                        <a href="{{ $item['href'] }}"
                            @if ($item['section']) data-section="{{ $item['section'] }}" @endif
                            @class(['active' => $item['active']])
                            @if ($item['active']) aria-current="page" @endif>{{ $item['label'] }}</a>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="home-nav-auth-items">
            @auth
                @if (!$user->hasVerifiedEmail())
                    <a href="{{ route('verification.notice') }}" class="btn-sign-up">
                        <i class="fa-solid fa-envelope-circle-check" aria-hidden="true"></i> Verify email
                    </a>
                @else
                    @if ($user->role === 'user')
                        <a href="{{ route('saved-listings.index') }}" class="btn-sign-in">
                            <i class="fa-regular fa-heart" aria-hidden="true"></i> Saved
                        </a>
                    @endif
                    <a href="{{ $user->dashboardUrl() }}" class="btn-sign-up">
                        <i class="fa-solid fa-gauge-high" aria-hidden="true"></i> Dashboard
                    </a>
                @endif
            @else
                <a href="{{ route('login') }}" class="btn-sign-in">Log in</a>
                <a href="{{ route('register') }}" class="btn-sign-up">Get Started</a>
            @endauth
        </div>
    </nav>

    <button type="button" class="menu-toggle" id="menuToggle" aria-label="Open menu" aria-expanded="false"
        aria-controls="mainNav">
        <i class="fa-solid fa-bars bars-button" aria-hidden="true"></i>
        <i class="fa-solid fa-xmark x-button" aria-hidden="true"></i>
    </button>
</header>

<script>
    (function() {
        'use strict';
        const nav = document.getElementById('mainNav');
        const toggle = document.getElementById('menuToggle');
        const header = document.querySelector('.home-header');
        const isMobile = () => window.innerWidth <= 768;
        const isOpen = () => toggle.classList.contains('open');

        function setOpen(open) {
            toggle.classList.toggle('open', open);
            nav.classList.toggle('open', open);
            toggle.setAttribute('aria-expanded', String(open));
            toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
            document.body.style.overflow = open && isMobile() ? 'hidden' : '';
        }

        toggle.addEventListener('click', e => {
            e.stopPropagation();
            setOpen(!isOpen());
        });
        nav.querySelectorAll('a').forEach(a => a.addEventListener('click', () => isMobile() && setOpen(false)));
        document.addEventListener('click', e => {
            if (isMobile() && isOpen() && !header.contains(e.target)) setOpen(false);
        });

        // Only act when the menu is actually open, so Escape elsewhere is left alone.
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && isOpen()) {
                setOpen(false);
                toggle.focus();
            }
        });
        window.addEventListener('resize', () => {
            if (!isMobile() && isOpen()) setOpen(false);
        });
    })();
</script>
