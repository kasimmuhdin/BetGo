@php
    $user = auth()->user();
    $nav = \App\Support\DashboardNav::build($user);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#111827">
    <title>@yield('title', 'Dashboard') · {{ config('betgo.name', 'BetGo') }}</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    {{-- Applied before first paint, so a collapsed sidebar does not flash open on every page. --}}
    <script>
        try {
            if (window.innerWidth >= 768 && localStorage.getItem('bg.sidebar') === 'collapsed') {
                document.documentElement.classList.add('sb-collapsed');
            }
        } catch (e) {}
    </script>

    {{-- dashboard.js must come before app.js: it registers Alpine data before Alpine starts. --}}
    @vite(['resources/css/app.css', 'resources/js/dashboard.js', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }

        .sb-dot,
        .sb-rule {
            display: none;
        }

        @media (min-width: 768px) {

            html.sb-collapsed .sb-aside {
                width: 4rem;
            }

            html.sb-collapsed .sb-hide {
                display: none !important;
            }

            html.sb-collapsed .sb-dot {
                display: block;
            }

            html.sb-collapsed .sb-rule {
                display: block;
            }
        }
    </style>
    @stack('styles')
</head>

<body class="h-full overflow-hidden bg-gray-900 font-sans text-gray-200 antialiased" data-user-id="{{ $user->id }}"
    data-messages="{{ $nav['counts']['messages'] }}" data-notifications="{{ $nav['counts']['notifications'] }}">

    <a href="#main"
        class="sr-only focus:not-sr-only focus:fixed focus:left-3 focus:top-3 focus:z-[110] focus:rounded-lg focus:bg-indigo-600 focus:px-4 focus:py-2 focus:text-white">
        Skip to content
    </a>

    <div x-data="dashboardShell" @keydown.escape.window="drawer = false" class="relative flex overflow-hidden"
        style="height: 100vh; height: 100dvh;">

        {{-- Mobile backdrop --}}
        <div x-show="drawer" x-cloak x-transition.opacity @click="drawer = false"
            class="fixed inset-0 z-30 bg-black/60 md:hidden" aria-hidden="true"></div>

        @include('layouts.sidebar')

        <div class="flex min-w-0 flex-1 flex-col">
            @include('layouts.header')

            <main id="main" tabindex="-1"
                class="relative flex-1 overflow-y-auto bg-gray-900 p-4 focus:outline-none sm:p-6">
                @if ($nav['notice'])
                    <div role="status"
                        class="mb-6 rounded-xl border px-4 py-3 text-sm {{ $nav['notice']['tone'] === 'red' ? 'border-red-500/30 bg-red-500/10 text-red-300' : 'border-amber-500/30 bg-amber-500/10 text-amber-300' }}">
                        {{ $nav['notice']['text'] }}
                        @if ($nav['notice']['tone'] === 'red' && Route::has('pages.show'))
                            <a href="{{ route('pages.show', 'contact') }}" class="font-semibold underline">Contact
                                us</a>
                        @endif
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <div id="toast-container" class="fixed right-5 top-5 z-[100] flex flex-col gap-2" aria-live="polite"></div>

    @stack('scripts')
</body>

</html>
