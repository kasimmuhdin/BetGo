<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#0c0a0a">
    <title>@yield('title', 'Account') · {{ config('betgo.name', 'BetGo') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite('resources/css/auth.css')
    @stack('styles')
</head>

<body class="au-body">
    @php $brand = config('betgo.name', 'BetGo'); @endphp

    <div class="au-shell">
        {{-- Brand panel (hidden on tablets and phones) --}}
        <aside class="au-aside" aria-hidden="false">
            <a href="{{ route('home') }}" class="au-brand">
                <span class="au-logo"><img src="{{ asset('logo.svg') }}" alt="" width="40"
                        height="40"></span> {{ $brand }}
            </a>

            <div>
                <h2>Find, list and rent property in Addis Ababa.</h2>
                <ul class="au-points">
                    <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i><span><strong>Reviewed
                                listings</strong>Our team checks every listing before it goes live.</span></li>
                    <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i><span><strong>Verified owners and
                                agents</strong>Everyone who posts is checked with a national ID.</span></li>
                    <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i><span><strong>Free for renters and
                                buyers</strong>Search, save and message at no cost.</span></li>
                </ul>
            </div>

            <p class="au-copy">&copy; {{ date('Y') }} {{ $brand }}</p>
        </aside>

        <main class="au-main">
            <a href="{{ route('home') }}" class="au-brand au-mbrand">
                <span class="au-logo"><img src="{{ asset('logo.svg') }}" alt="" width="40"
                        height="40"></span> {{ $brand }}
            </a>

            <div class="au-card @yield('card_class')">
                @yield('content')
            </div>
        </main>
    </div>

    <script>
        (function() {
            'use strict';

            // Show or hide the password.
            document.querySelectorAll('[data-toggle-password]').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var input = btn.parentElement.querySelector('input');
                    var show = input.type === 'password';
                    input.type = show ? 'text' : 'password';
                    btn.setAttribute('aria-pressed', show ? 'true' : 'false');
                    btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
                    btn.firstElementChild.className = show ? 'fa-regular fa-eye-slash' :
                        'fa-regular fa-eye';
                });
            });

            // Password strength meter (a hint only; the server decides).
            document.querySelectorAll('input[data-strength]').forEach(function(input) {
                var meter = input.closest('.au-field').querySelector('[data-meter]');
                if (!meter) return;
                input.addEventListener('input', function() {
                    var v = input.value,
                        s = 0;
                    if (v.length) {
                        if (v.length >= 8) s++;
                        if (/[a-z]/.test(v) && /[A-Z]/.test(v)) s++;
                        if (/\d/.test(v)) s++;
                        if (/[^A-Za-z0-9]/.test(v) || v.length >= 14) s++;
                        if (v.length < 8) s = 1;
                    }
                    meter.dataset.level = s;
                });
            });

            // ---- Loading state for form buttons and link buttons (e.g. Google) ----
            // We never use the "disabled" attribute: Firefox restores it after a reload and the
            // button would stay dead. A "busy" flag blocks double clicks instead.
            var SAFETY_MS = 20000; // if we are still on this page after 20s (user pressed Stop), unlock
            var timers = new WeakMap();

            function paint(btn, on) {
                var label = btn.querySelector('.au-btn-text');
                btn.classList.toggle('is-loading', on);
                if (on) {
                    btn.setAttribute('aria-busy', 'true');
                    btn.setAttribute('aria-disabled', 'true');
                } else {
                    btn.removeAttribute('aria-busy');
                    btn.removeAttribute('aria-disabled');
                }
                if (label) {
                    if (on && btn.dataset.loadingText) label.textContent = btn.dataset.loadingText;
                    if (!on && btn.dataset.text) label.textContent = btn.dataset.text;
                }
            }

            function lock(owner, btn) {
                owner.dataset.busy = '1';
                paint(btn, true);
                timers.set(owner, setTimeout(function() {
                    unlock(owner, btn);
                }, SAFETY_MS));
            }

            function unlock(owner, btn) {
                clearTimeout(timers.get(owner));
                delete owner.dataset.busy;
                paint(btn, false);
            }

            document.querySelectorAll('form[data-loading]').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    if (form.dataset.busy) {
                        e.preventDefault();
                        return;
                    }
                    var btn = (e.submitter && e.submitter.matches('button[type=submit]')) ?
                        e.submitter : form.querySelector('button[type=submit]');
                    if (btn) lock(form, btn);
                    else form.dataset.busy = '1';
                });
            });

            document.querySelectorAll('a[data-loading-link]').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    // Ctrl/Cmd/Shift-click opens a new tab: leave this page alone.
                    if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey ||
                        e.altKey) return;
                    if (link.dataset.busy) {
                        e.preventDefault();
                        return;
                    }
                    lock(link, link);
                });
            });

            // Back button restores the page from cache: unlock everything.
            window.addEventListener('pageshow', function(e) {
                if (!e.persisted) return;
                document.querySelectorAll('[data-busy]').forEach(function(owner) {
                    var btn = owner.matches('a') ? owner : owner.querySelector('button[type=submit]');
                    if (btn) unlock(owner, btn);
                });
            });
        })();
    </script>
    @stack('scripts')
</body>

</html>
