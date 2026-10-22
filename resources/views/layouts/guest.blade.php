@php
    $flash = session('error') ?? (session('success') ?? session('status'));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0e0e2a">
    <meta name="description" content="@yield('meta_description', 'Verified rentals and properties for sale in Addis Ababa.')">

    <title>@yield('title', 'Home') · BetGo</title>

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="BetGo">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title', 'Home') · BetGo">
    <meta property="og:description" content="@yield('meta_description', 'Verified rentals and properties for sale in Addis Ababa.')">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|bricolage-grotesque:600,700,800&display=swap"
        rel="stylesheet" />

    <!-- Icons -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* No global reset here: Tailwind's preflight already does it, and an
           unlayered `* { margin:0; padding:0 }` would override Tailwind utilities. */

        :root {
            --bg: #0b0a0a;
            --surface: #161414;
            --surface-2: #1e1b1b;
            --surface-3: #2a2727;
            --line: #2f2d30;
            --accent: #00c8ff;
            --accent-hover: #4fdcff;
            --accent-ink: #04141c;
            --accent-glow: rgba(0, 200, 255, 0.35);
            --text: #f4f4f6;
            --text-white: #ffffff;
            --text-muted: rgba(255, 255, 255, 0.72);
            --muted: #9b9ba6;
            --danger: #ff6b6b;
            --ok: #3ddc97;
            --header-bg: linear-gradient(135deg, #0e0e2a 0%, #1a1a4e 100%);
            --font-sans: 'Figtree', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            --font-display: 'Bricolage Grotesque', 'Figtree', -apple-system, 'Segoe UI', Roboto, sans-serif;
            --transition: 0.25s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        html {
            scroll-behavior: smooth;
            scroll-padding-top: 90px;
            color-scheme: dark;
            background: #282525;

        }

        body {
            background: var(--bg);

            color: var(--text);
            font-family: var(--font-sans);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            -webkit-font-smoothing: antialiased;
            max-width: 1600px;
            margin: 0 auto;
        }

        .site-main {
            flex: 1 0 auto;
            width: 100%;
            max-width: 1600px;
            margin: 0 auto;
            background: var(--bg);
        }

        /* Auth / narrow pages keep the old centered behaviour */
        .site-main--centered {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
        }

        /* ===== ACCESSIBILITY ===== */
        .skip-link {
            position: absolute;
            left: -9999px;
            top: 8px;
            z-index: 2000;
            background: var(--accent);
            color: var(--accent-ink);
            padding: 8px 14px;
            border-radius: 8px;
            font-weight: 600;
        }

        .skip-link:focus {
            left: 8px;
        }

        a:focus-visible,
        button:focus-visible,
        select:focus-visible,
        input:focus-visible,
        summary:focus-visible {
            outline: 2px solid var(--accent);
            outline-offset: 2px;
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            border: 0;
        }

        /* ===== FLASH MESSAGE ===== */
        .flash {
            max-width: 720px;
            margin: 16px auto 0;
            padding: 12px 18px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 500;
            border: 1px solid;
        }

        .flash-ok {
            color: var(--ok);
            background: rgba(61, 220, 151, .08);
            border-color: rgba(61, 220, 151, .3);
        }

        .flash-error {
            color: var(--danger);
            background: rgba(255, 107, 107, .08);
            border-color: rgba(255, 107, 107, .3);
        }

        /* ===== HEADER ===== */
        .home-header {
            height: 76px;
            background: var(--header-bg);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.4);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 32px;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .logo-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            flex-shrink: 0;
        }

        .logo-wrapper .logo-icon {
            width: 48px;
            height: 48px;
            flex-shrink: 0;
        }

        .logo-wrapper .logo-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            border: 2.5px solid var(--accent);
            box-shadow: 0 0 8px var(--accent-glow), 0 0 20px var(--accent-glow);
            transition: box-shadow var(--transition), transform var(--transition);
            display: block;
        }

        .logo-wrapper:hover .logo-icon img {
            box-shadow: 0 0 14px var(--accent-glow), 0 0 34px var(--accent-glow);
            transform: scale(1.04);
        }

        .logo-wrapper .logo-text {
            font-family: var(--font-display);
            font-size: 27px;
            font-weight: 800;
            letter-spacing: -0.5px;
            line-height: 1;
            background: linear-gradient(to right, #ffffff, #b0e0ff);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .logo-wrapper .logo-badge {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: var(--accent);
            background: rgba(0, 200, 255, 0.12);
            padding: 2px 10px;
            border-radius: 20px;
            border: 1px solid rgba(0, 200, 255, 0.2);
        }

        .home-nav {
            display: flex;
            align-items: center;
            gap: 28px;
            flex: 1;
            justify-content: flex-end;
        }

        .home-nav-items ul {
            display: flex;
            align-items: center;
            gap: 6px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .home-nav-items ul li a {
            display: inline-block;
            padding: 8px 16px;
            font-size: 15px;
            font-weight: 500;
            color: var(--text-muted);
            text-decoration: none;
            border-radius: 8px;
            transition: color var(--transition), background var(--transition);
            position: relative;
        }

        .home-nav-items ul li a::after {
            content: '';
            position: absolute;
            bottom: 4px;
            left: 50%;
            width: 0;
            height: 2.5px;
            background: var(--accent);
            border-radius: 4px;
            opacity: 0;
            box-shadow: 0 0 10px var(--accent-glow);
            transform: translateX(-50%);
            transition: width var(--transition), opacity var(--transition);
        }

        .home-nav-items ul li a:hover {
            color: var(--text-white);
            background: rgba(255, 255, 255, 0.06);
        }

        .home-nav-items ul li a:hover::after,
        .home-nav-items ul li a.active::after {
            width: 60%;
            opacity: 1;
        }

        .home-nav-items ul li a.active {
            color: var(--text-white);
            background: rgba(0, 200, 255, 0.08);
        }

        .home-nav-auth-items {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-sign-in {
            padding: 8px 18px;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-muted);
            border-radius: 8px;
            text-decoration: none;
            transition: color var(--transition), background var(--transition);
        }

        .btn-sign-in:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.06);
        }

        .btn-sign-up {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 22px;
            font-size: 14px;
            font-weight: 700;
            color: var(--accent-ink);
            background: var(--accent);
            border-radius: 10px;
            text-decoration: none;
            box-shadow: 0 0 16px var(--accent-glow);
            transition: background var(--transition), transform var(--transition), box-shadow var(--transition);
        }

        .btn-sign-up:hover {
            background: var(--accent-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 28px var(--accent-glow);
        }

        .btn-sign-up:active {
            transform: scale(0.96);
        }




        /* Mobile toggle: one real <button> */
        .menu-toggle {
            display: none;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: 0;
            font-size: 26px;
            color: #fff;
            cursor: pointer;
            padding: 6px 10px;
            border-radius: 8px;
            line-height: 1;
            transition: background var(--transition), transform var(--transition);
        }

        .menu-toggle:hover {
            background: rgba(255, 255, 255, 0.06);
        }

        .menu-toggle:active {
            transform: scale(0.92);
        }

        .menu-toggle .x-button {
            display: none;
        }

        .menu-toggle.open .x-button {
            display: block;
        }

        .menu-toggle.open .bars-button {
            display: none;
        }

        /* ===== FOOTER ===== */
        .site-footer {
            background: var(--header-bg);
            color: rgba(255, 255, 255, 0.8);
            padding: 60px 48px 24px;
            margin: 40px 20px 20px;
            border-radius: 32px 32px 20px 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
        }

        .footer-top {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-brand {
            flex: 1 1 280px;
            max-width: 360px;
        }

        .footer-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .footer-logo .logo-icon {
            width: 44px;
            height: 44px;
        }

        .footer-logo .logo-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid var(--accent);
            box-shadow: 0 0 12px rgba(0, 200, 255, 0.2);
        }

        .footer-logo .logo-text {
            font-family: var(--font-display);
            font-size: 24px;
            font-weight: 800;
            background: linear-gradient(to right, #ffffff, #b0e0ff);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .footer-brand p {
            font-size: 14px;
            line-height: 1.7;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 20px;
        }

        .footer-social {
            display: flex;
            gap: 14px;
        }

        .footer-social a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.06);
            color: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.06);
            text-decoration: none;
            font-size: 16px;
            transition: all .3s;
        }

        .footer-social a:hover {
            background: var(--accent);
            color: var(--accent-ink);
            transform: translateY(-3px);
            border-color: var(--accent);
        }

        .footer-links {
            display: flex;
            flex-wrap: wrap;
            gap: 40px;
            flex: 2 1 560px;
        }

        .footer-column {
            min-width: 140px;
            flex: 1 0 auto;
        }

        .footer-column h4 {
            font-size: 16px;
            font-weight: 600;
            color: #fff;
            margin-bottom: 18px;
        }

        .footer-column h4::after {
            content: '';
            display: block;
            width: 28px;
            height: 2px;
            background: var(--accent);
            margin-top: 6px;
            border-radius: 4px;
        }

        .footer-column ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-column ul li {
            margin-bottom: 10px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
        }

        .footer-column ul li a {
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            transition: color .3s;
        }

        .footer-column ul li a:hover,
        .footer-legal a:hover {
            color: var(--accent);
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            padding-top: 24px;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.45);
        }

        .footer-legal {
            display: flex;
            gap: 20px;
        }

        .footer-legal a {
            color: rgba(255, 255, 255, 0.45);
            text-decoration: none;
            transition: color .3s;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1024px) {
            .home-header {
                padding: 0 24px;
            }

            .home-nav-items ul li a {
                padding: 8px 12px;
                font-size: 14px;
            }

            .site-footer {
                padding: 48px 32px 20px;
                margin: 32px 16px 16px;
            }
        }

        @media (max-width: 880px) {
            .home-nav {
                gap: 14px;
            }

            .home-nav-items ul li a {
                padding: 8px 10px;
                font-size: 13px;
            }

            .btn-sign-in {
                padding: 6px 12px;
                font-size: 13px;
            }

            .btn-sign-up {
                padding: 7px 16px;
                font-size: 13px;
            }
        }

        @media (max-width: 768px) {
            .home-header {
                height: 68px;
                padding: 0 18px;
            }

            .logo-wrapper .logo-text {
                font-size: 22px;
            }

            .logo-wrapper .logo-badge {
                display: none;
            }

            .logo-wrapper .logo-icon {
                width: 40px;
                height: 40px;
            }

            .home-nav {
                display: none;
                position: absolute;
                top: 68px;
                left: 0;
                right: 0;
                flex-direction: column;
                align-items: stretch;
                background: var(--header-bg);
                padding: 20px 24px 28px;
                gap: 20px;
                border-bottom: 1px solid rgba(255, 255, 255, 0.06);
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
                border-radius: 0 0 16px 16px;
            }

            .home-nav.open {
                display: flex;
            }

            .home-nav-items ul {
                flex-direction: column;
                align-items: stretch;
                gap: 4px;
            }

            .home-nav-items ul li a {
                display: block;
                padding: 12px 16px;
                font-size: 16px;
                border-radius: 10px;
            }

            .home-nav-items ul li a::after {
                display: none;
            }

            .home-nav-auth-items {
                flex-direction: column;
                align-items: stretch;
                padding-top: 12px;
                border-top: 1px solid rgba(255, 255, 255, 0.06);
            }

            .btn-sign-in,
            .btn-sign-up {
                justify-content: center;
                text-align: center;
                padding: 13px;
                font-size: 15px;
                border-radius: 10px;
            }

            .menu-toggle {
                display: flex;
            }

            .site-footer {
                padding: 36px 20px 16px;
                margin: 24px 12px 16px;
                border-radius: 20px;
            }

            .footer-top,
            .footer-links {
                flex-direction: column;
                gap: 28px;
            }

            .footer-brand {
                max-width: 100%;
            }

            .footer-bottom {
                flex-direction: column;
                text-align: center;
            }

            .footer-legal {
                flex-wrap: wrap;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .home-header {
                padding: 0 14px;
                height: 62px;
            }

            .home-nav {
                top: 62px;
            }

            .logo-wrapper .logo-text {
                font-size: 19px;
            }

            .logo-wrapper .logo-icon {
                width: 36px;
                height: 36px;
            }

            .site-footer {
                margin: 20px 8px 12px;
            }
        }

        @media (prefers-reduced-motion: reduce) {

            *,
            *::before,
            *::after {
                animation: none !important;
                transition-duration: .01ms !important;
                scroll-behavior: auto !important;
            }
        }
    </style>

    @stack('styles')
</head>

<body class="font-sans antialiased">
    <a href="#main" class="skip-link">Skip to content</a>

    @include('home.header')

    @if ($flash)
        <div class="flash {{ session('error') ? 'flash-error' : 'flash-ok' }}" role="status">{{ $flash }}</div>
    @endif

    {{-- Pages opt out of the centered wrapper with: @section('full_width', '1') --}}
    @hasSection('full_width')
        <main id="main" class="site-main">
            @yield('content')
        </main>
    @else
        <main id="main" class="site-main site-main--centered">
            @yield('content')
        </main>
    @endif

    @include('home.footer')

    @stack('scripts')
</body>

</html>
