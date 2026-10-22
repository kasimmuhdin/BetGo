@php
    $navItems = \App\Support\PublicNav::items();
    $user = auth()->user();

    $contact = array_filter(config('betgo.contact', []));
    $social = array_filter(config('betgo.social', []));
    $socialIcons = [
        'facebook' => 'fa-facebook-f',
        'instagram' => 'fa-instagram',
        'x' => 'fa-x-twitter',
        'youtube' => 'fa-youtube',
        'telegram' => 'fa-telegram',
    ];

    $postUrl = match (true) {
        !$user => route('register'),
        $user->role === 'owner' => route('owner.listings.create'),
        $user->role === 'agent' => route('agent.listings.create'),
        default => $user->dashboardUrl(),
    };

    $legal = \Illuminate\Support\Facades\Route::has('pages.show')
        ? ['Privacy Policy' => 'privacy', 'Terms of Service' => 'terms']
        : [];
@endphp

<footer class="site-footer">
    <div class="footer-top">
        <div class="footer-brand">
            <div class="footer-logo">
                <span class="logo-icon"><img src="{{ asset('logo.svg') }}" alt="" width="44"
                        height="44"></span>
                <span class="logo-text">{{ config('betgo.name') }}</span>
            </div>
            <p>Reviewed rentals and properties for sale in Addis Ababa, with verified owners and agents.</p>

            @if ($social)
                <div class="footer-social">
                    @foreach ($social as $name => $url)
                        <a href="{{ $url }}" aria-label="{{ ucfirst($name) }}" target="_blank"
                            rel="noopener noreferrer">
                            <i class="fa-brands {{ $socialIcons[$name] ?? 'fa-link' }}" aria-hidden="true"></i>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="footer-links">
            <nav class="footer-column" aria-label="Explore">
                <h4>Explore</h4>
                <ul>
                    @foreach ($navItems as $item)
                        <li><a href="{{ $item['href'] }}">{{ $item['label'] }}</a></li>
                    @endforeach
                    <li><a href="{{ route('faq') }}">FAQ</a></li>
                </ul>
            </nav>

            <nav class="footer-column" aria-label="Owners and agents">
                <h4>Owners &amp; Agents</h4>
                <ul>
                    <li><a href="{{ $postUrl }}">Post a listing</a></li>
                    <li><a href="{{ route('pricing') }}">Plans &amp; pricing</a></li>
                </ul>
            </nav>

            <nav class="footer-column" aria-label="Account">
                <h4>Account</h4>
                <ul>
                    @auth
                        <li><a href="{{ $user->dashboardUrl() }}">My dashboard</a></li>
                    @else
                        <li><a href="{{ route('login') }}">Log in</a></li>
                        <li><a href="{{ route('register') }}">Register</a></li>
                    @endauth
                </ul>
            </nav>

            @if ($contact)
                <div class="footer-column">
                    <h4>Contact</h4>
                    <ul>
                        @isset($contact['email'])
                            <li><a href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a></li>
                        @endisset
                        @isset($contact['phone'])
                            <li><a
                                    href="tel:{{ preg_replace('/[^\d+]/', '', $contact['phone']) }}">{{ $contact['phone'] }}</a>
                            </li>
                        @endisset
                        @isset($contact['address'])
                            <li>{{ $contact['address'] }}</li>
                        @endisset
                    </ul>
                </div>
            @endif
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; {{ date('Y') }} {{ config('betgo.name') }}. All rights reserved.</p>
        @if ($legal)
            <div class="footer-legal">
                @foreach ($legal as $label => $slug)
                    <a href="{{ route('pages.show', $slug) }}">{{ $label }}</a>
                @endforeach
            </div>
        @endif
    </div>
</footer>
