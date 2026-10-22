@extends('layouts.guest')

@section('title', 'Pricing')
@section('meta_description', 'Searching is free. Owners and agents pay for a single listing or choose a monthly plan.
    Pay with telebirr, CBE Birr, Dashen Bank or card.')
@section('full_width', '1')

@push('styles')
    @vite('resources/css/home.css')
@endpush

@php
    // 1000.00 -> "1,000", 2.50 -> "2.5"
    $money = fn($v) => rtrim(rtrim(number_format((float) $v, 2), '0'), '.');
    $yes = new \Illuminate\Support\HtmlString('<i class="fa-solid fa-check" aria-label="Included"></i>');

    $cta = fn($plan) => match (true) {
        !$user => ['Get started', route('register')],
        in_array($user->role, ['owner', 'agent'], true) => [
            'Choose plan',
            route('payments.pricing', ['plan' => $plan->key]),
        ],
        $user->role === 'user' => ['Become an owner or agent', $user->dashboardUrl()],
        default => null,
    };

    $rows = [
        'Price' => fn($p) => $money($p->price) . ' ETB',
        'Billing' => fn($p) => $p->isRecurring() ? 'Every ' . $p->duration_days . ' days' : 'One time',
        'Listings included' => fn($p) => $p->isUnlimited() ? 'Unlimited' : number_format($p->listings_limit),
        'Price per listing' => fn($p) => $p->pricePerListing()
            ? ($p->listings_limit > 1 ? '≈ ' : '') . $money($p->pricePerListing()) . ' ETB'
            : '—',
        'Valid for' => fn($p) => $p->isRecurring() ? $p->duration_days . ' days' : 'Never expires',
        'Photos and map pin' => fn($p) => $yes,
        'Verified badge after approval' => fn($p) => $yes,
        'Messages from interested renters' => fn($p) => $yes,
        'Automatic renewal' => fn($p) => $p->isRecurring() && $p->price_usd ? 'With card (Stripe)' : '—',
    ];
@endphp

@section('content')

    {{-- ================= HEADER ================= --}}
    <section class="hp-page-hero" aria-labelledby="pricing-title">
        <div class="hp-wrap">
            <p class="hp-kicker">Pricing</p>
            <h1 id="pricing-title" class="hp-h1">Simple pricing for owners and agents</h1>
            <p class="hp-lead">Searching, saving and messaging are always free. You only pay when you publish listings.</p>
            <div style="margin-top:28px; display:flex; gap:12px; justify-content:center; flex-wrap:wrap">
                <a href="#plans" class="hp-btn hp-btn--primary">See plans</a>
                <a href="{{ route('home') }}#listings" class="hp-btn hp-btn--ghost">Browse listings</a>
            </div>
        </div>
    </section>

    {{-- ================= PLANS ================= --}}
    <section id="plans" class="hp-section" style="padding-top:48px" aria-label="Plans">
        <div class="hp-wrap">
            @if ($plans->isEmpty())
                <p class="hp-note">Plans will be announced soon.</p>
            @else
                <div class="hp-plans">
                    @foreach ($plans as $plan)
                        @php
                            $popular = $plan->key === $featuredKey;
                            $perListing = ($plan->listings_limit ?? 0) > 1 ? $plan->pricePerListing() : null;
                            $action = $cta($plan);
                        @endphp

                        <div @class(['hp-plan', 'hp-plan--pop' => $popular])>
                            @if ($popular)
                                <span class="hp-plan-badge">Most popular</span>
                            @endif

                            <p class="hp-plan-kind">
                                {{ $plan->isRecurring() ? $plan->duration_days . '-day plan' : 'Pay as you go' }}</p>
                            <h3>{{ $plan->name }}</h3>

                            <p class="hp-plan-price">
                                {{ $money($plan->price) }}
                                <small>{{ $plan->isRecurring() ? 'ETB every ' . $plan->duration_days . ' days' : 'ETB, one time' }}</small>
                            </p>
                            @if ($perListing)
                                <p class="hp-plan-per">≈ {{ $money($perListing) }} ETB per listing</p>
                            @endif
                            @if ($plan->price_usd)
                                <p class="hp-plan-usd">or ${{ $money($plan->price_usd) }} by card</p>
                            @endif

                            <ul>
                                <li><i class="fa-solid fa-check"
                                        aria-hidden="true"></i>{{ $plan->isUnlimited() ? 'Unlimited listings' : number_format($plan->listings_limit) . ' ' . \Illuminate\Support\Str::plural('listing', $plan->listings_limit) }}
                                </li>
                                <li><i class="fa-solid fa-check"
                                        aria-hidden="true"></i>{{ $plan->isRecurring() ? 'Valid for ' . $plan->duration_days . ' days' : 'Never expires' }}
                                </li>
                                <li><i class="fa-solid fa-check" aria-hidden="true"></i>Photos and a map pin on every
                                    listing</li>
                                <li><i class="fa-solid fa-check" aria-hidden="true"></i>Verified badge once approved</li>
                            </ul>

                            @if ($action)
                                <a href="{{ $action[1] }}" @class([
                                    'hp-btn hp-btn--block',
                                    $popular ? 'hp-btn--primary' : 'hp-btn--ghost',
                                ])>{{ $action[0] }}</a>
                            @endif
                        </div>
                    @endforeach
                </div>

                @guest
                    <p class="hp-note">New here? Create an account, get verified as an owner or agent, then pick a plan.</p>
                @endguest
            @endif
        </div>
    </section>

    {{-- ================= COMPARISON ================= --}}
    @if ($plans->count() > 1)
        <section class="hp-section hp-section--alt" aria-labelledby="compare-title">
            <div class="hp-wrap">
                <div class="hp-center">
                    <p class="hp-kicker">Compare</p>
                    <h2 id="compare-title" class="hp-h2">Every plan, side by side</h2>
                </div>

                <div class="hp-table-wrap" tabindex="0" role="region" aria-label="Plan comparison">
                    <table class="hp-table">
                        <thead>
                            <tr>
                                <th scope="col"><span class="sr-only">Feature</span></th>
                                @foreach ($plans as $plan)
                                    <th scope="col" @class(['is-pop' => $plan->key === $featuredKey])>{{ $plan->name }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $label => $value)
                                <tr>
                                    <th scope="row">{{ $label }}</th>
                                    @foreach ($plans as $plan)
                                        <td>{{ $value($plan) }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    @endif

    {{-- ================= PAYMENT METHODS ================= --}}
    <section class="hp-section" aria-labelledby="pay-title">
        <div class="hp-wrap">
            <div class="hp-center">
                <p class="hp-kicker">Payments</p>
                <h2 id="pay-title" class="hp-h2">Pay the way you already do</h2>
            </div>

            <div class="hp-pay">
                <div class="hp-pay-card">
                    <h3><i class="fa-solid fa-mobile-screen-button" aria-hidden="true"></i> Chapa</h3>
                    <p>Pay in ETB with telebirr, CBE Birr, Dashen Bank and other supported channels. When a plan ends, you
                        pay again to continue.</p>
                </div>

                @if ($cardPayments)
                    <div class="hp-pay-card">
                        <h3><i class="fa-solid fa-credit-card" aria-hidden="true"></i> Card (Stripe)</h3>
                        <p>Pay in USD with Visa or Mastercard. Monthly plans renew automatically, and you can cancel any
                            time from your billing page.</p>
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- ================= FAQ ================= --}}
    <section class="hp-section hp-section--alt" aria-labelledby="faq-title">
        <div class="hp-wrap">
            <div class="hp-center">
                <p class="hp-kicker">FAQ</p>
                <h2 id="faq-title" class="hp-h2">Billing questions</h2>
            </div>

            <div class="hp-faq">
                <details>
                    <summary>Do renters and buyers pay anything?</summary>
                    <p>No. Searching, saving listings and messaging owners and agents are free.</p>
                </details>
                <details>
                    <summary>Can I have more than one plan at the same time?</summary>
                    <p>Yes. Each purchase adds its own listings. When you post, listings are used from the plan that expires
                        first.</p>
                </details>
                <details>
                    <summary>Do unused listings carry over?</summary>
                    <p>Single listings never expire. Listings in a time-limited plan end with that plan, so unused ones do
                        not move to the next period.</p>
                </details>
                <details>
                    <summary>Can I cancel?</summary>
                    <p>Card subscriptions can be cancelled from your billing page and stay active until the end of the paid
                        period. Plans paid with Chapa do not renew by themselves, so there is nothing to cancel.</p>
                </details>
                <details>
                    <summary>I paid but my plan is not showing.</summary>
                    <p>Payments are normally confirmed within seconds. If yours is not, contact us with your transaction
                        reference and we will check it.</p>
                </details>
            </div>
        </div>
    </section>

    @guest
        <section class="hp-wrap hp-cta" aria-labelledby="cta-title">
            <h2 id="cta-title" class="hp-h2">Ready to publish your first listing?</h2>
            <p class="hp-sub">Create an account, get verified and choose a plan.</p>
            <a href="{{ route('register') }}" class="hp-btn hp-btn--primary">
                Create your account <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </a>
        </section>
    @endguest
@endsection
