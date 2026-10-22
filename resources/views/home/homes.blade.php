@extends('layouts.guest')

@section('title', 'Find Rentals & Homes in Addis Ababa')
@section('meta_description',
    'Browse reviewed apartments, houses, villas and offices for rent or sale in Addis Ababa,
    and contact owners and agents directly.')
@section('full_width', '1')

@push('styles')
    @vite('resources/css/home.css')
@endpush

@php
    $user = auth()->user();

    $typeIcons = [
        'apartment' => 'fa-building',
        'house' => 'fa-house',
        'villa' => 'fa-house-chimney',
        'office' => 'fa-briefcase',
        'land' => 'fa-tree',
        'commercial' => 'fa-store',
        'warehouse' => 'fa-warehouse',
    ];
    $periodShort = ['daily' => 'day', 'weekly' => 'week', 'monthly' => 'month', 'yearly' => 'year'];

    // Filter URL builder: keep current filters and sort, drop some, override others.
    $link = function (array $override = [], array $drop = []) use ($filters, $sort) {
        $params = Arr::except($filters + ['sort' => $sort === 'newest' ? null : $sort], $drop);

        return array_filter(array_merge($params, $override), fn($v) => $v !== null && $v !== '');
    };

    $chipLabels = [
        'q' => fn($v) => '“' . $v . '”',
        'listing_type' => fn($v) => $v === 'rent' ? 'For rent' : 'For sale',
        'property_type' => fn($v) => $propertyTypes[$v] ?? ucfirst($v),
        'min_price' => fn($v) => 'From ' . number_format($v) . ' ETB',
        'max_price' => fn($v) => 'Up to ' . number_format($v) . ' ETB',
        'bedrooms' => fn($v) => $v . '+ beds',
    ];

    // Decided once, used by the pricing cards and the closing banner.

    $postCta = match (true) {
        !$user => ['List your property', route('register')],
        $user->role === 'owner' => ['Post a listing', route('owner.listings.create')],
        $user->role === 'agent' => ['Post a listing', route('agent.listings.create')],
        $user->role === 'user' => ['Become an owner or agent', $user->dashboardUrl()],
        default => null,
    };

    $placeholder =
        "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 600 450'%3E%3Crect width='600' height='450' fill='%23161414'/%3E%3Cpath d='M300 170l-70 60v60h45v-40h50v40h45v-60z' fill='none' stroke='%2300c8ff' stroke-width='6' stroke-linejoin='round' opacity='.5'/%3E%3C/svg%3E";
@endphp

@section('content')

    {{-- ================= HERO ================= --}}
    <section class="hp-hero" aria-labelledby="hero-title">
        <div class="hp-wrap hp-hero-inner">
            <span class="hp-pill"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Every listing is reviewed before
                it goes live</span>
            <h1 id="hero-title" class="hp-h1">Find your next home in <span class="hp-grad">Addis Ababa</span></h1>
            <p class="hp-lead">Browse apartments, houses and offices for rent or sale, and contact the owner or agent
                directly.</p>

            <form action="{{ route('home') }}#listings" method="GET" class="hp-search" role="search" data-clean>
                <div class="hp-seg" role="radiogroup" aria-label="Listing type">
                    <label><input type="radio" name="listing_type" value=""
                            @checked(empty($filters['listing_type']))><span>All</span></label>
                    <label><input type="radio" name="listing_type" value="rent"
                            @checked(($filters['listing_type'] ?? '') === 'rent')><span>Rent</span></label>
                    <label><input type="radio" name="listing_type" value="sale"
                            @checked(($filters['listing_type'] ?? '') === 'sale')><span>Buy</span></label>
                </div>

                <div class="hp-search-row">
                    <label class="hp-field">
                        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="100"
                            autocomplete="off" aria-label="Search by area, neighborhood or title"
                            placeholder="Area, neighborhood or title">
                    </label>

                    <div class="hp-field">
                        <i class="fa-solid fa-house" aria-hidden="true"></i>
                        <select name="property_type" aria-label="Property type">
                            <option value="">All property types</option>
                            @foreach ($propertyTypes as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['property_type'] ?? '') === $value)>{{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <i class="fa-solid fa-chevron-down hp-caret" aria-hidden="true"></i>
                    </div>

                    <button type="submit" class="hp-btn hp-btn--primary">Search</button>
                </div>
            </form>

            @if (count($popularAreas))
                <div class="hp-areas">
                    <span>Popular areas:</span>
                    @foreach ($popularAreas as $area)
                        <a class="hp-area" href="{{ route('home', ['q' => $area]) }}#listings">{{ $area }}</a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <div class="hp-trust">
        <ul>
            <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i>Listings reviewed by our team</li>
            <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i>Owners and agents verified with ID</li>
            <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i>Free for renters and buyers</li>
        </ul>
    </div>

    {{-- ================= LISTINGS ================= --}}
    <section id="listings" class="hp-section" style="padding-top:56px" aria-labelledby="listings-title">
        <div class="hp-wrap">
            <div class="hp-head">
                <h2 id="listings-title" class="hp-h2">Available properties</h2>
                <p>
                    {{ number_format($properties->total()) }} {{ Str::plural('listing', $properties->total()) }}
                    {{ $filters ? 'match your search' : 'available now' }}
                </p>
            </div>

            @if (array_sum($typeCounts) > 0)
                <nav class="hp-types" aria-label="Property types">
                    <a href="{{ route('home', $link([], ['property_type'])) }}#listings" @class(['hp-type', 'is-active' => empty($filters['property_type'])])>
                        <i class="fa-solid fa-table-cells-large" aria-hidden="true"></i> All
                    </a>
                    @foreach ($propertyTypes as $value => $label)
                        @continue(empty($typeCounts[$value]))
                        <a href="{{ route('home', $link(['property_type' => $value])) }}#listings"
                            @class([
                                'hp-type',
                                'is-active' => ($filters['property_type'] ?? null) === $value,
                            ])>
                            <i class="fa-solid {{ $typeIcons[$value] ?? 'fa-house' }}" aria-hidden="true"></i>
                            {{ $label }} <small>{{ $typeCounts[$value] }}</small>
                        </a>
                    @endforeach
                </nav>
            @endif

            <form action="{{ route('home') }}#listings" method="GET" class="hp-filters" aria-label="Filter listings"
                data-clean>
                <input type="hidden" name="q" value="{{ $filters['q'] ?? '' }}">

                <select name="listing_type" data-autosubmit aria-label="Rent or sale">
                    <option value="">Rent &amp; sale</option>
                    <option value="rent" @selected(($filters['listing_type'] ?? '') === 'rent')>For rent</option>
                    <option value="sale" @selected(($filters['listing_type'] ?? '') === 'sale')>For sale</option>
                </select>

                <select name="property_type" data-autosubmit aria-label="Property type">
                    <option value="">All types</option>
                    @foreach ($propertyTypes as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['property_type'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>

                <select name="bedrooms" data-autosubmit aria-label="Bedrooms">
                    <option value="">Any bedrooms</option>
                    @foreach ([1, 2, 3, 4, 5] as $n)
                        <option value="{{ $n }}" @selected(($filters['bedrooms'] ?? '') == $n)>{{ $n }}+ beds
                        </option>
                    @endforeach
                </select>

                <div class="hp-price">
                    <input type="number" name="min_price" min="0" inputmode="numeric" placeholder="Min ETB"
                        aria-label="Minimum price" value="{{ $filters['min_price'] ?? '' }}">
                    <span aria-hidden="true">to</span>
                    <input type="number" name="max_price" min="0" inputmode="numeric" placeholder="Max ETB"
                        aria-label="Maximum price" value="{{ $filters['max_price'] ?? '' }}">
                </div>

                <select name="sort" data-autosubmit aria-label="Sort listings">
                    <option value="" @selected($sort === 'newest')>Newest first</option>
                    <option value="price_asc" @selected($sort === 'price_asc')>Price: low to high</option>
                    <option value="price_desc" @selected($sort === 'price_desc')>Price: high to low</option>
                </select>

                <button type="submit" class="hp-btn hp-btn--primary hp-btn--sm">Apply</button>
            </form>

            @if ($filters)
                <div class="hp-chips" aria-label="Applied filters">
                    @foreach ($filters as $key => $value)
                        @if (isset($chipLabels[$key]))
                            <a class="hp-chip" href="{{ route('home', $link([], [$key])) }}#listings"
                                title="Remove this filter">
                                {{ $chipLabels[$key]($value) }} <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                            </a>
                        @endif
                    @endforeach
                    <a href="{{ route('home') }}#listings" class="hp-clear">Clear all</a>
                </div>
            @endif

            @if ($properties->isEmpty())
                <div class="hp-empty">
                    <div class="hp-empty-icon"><i class="fa-solid fa-house-circle-xmark" aria-hidden="true"></i></div>
                    <h3>No properties match your search</h3>
                    <p>Try a wider price range, fewer filters, or a different area.</p>
                    <a href="{{ route('home') }}#listings" class="hp-btn hp-btn--primary hp-btn--sm">Clear all
                        filters</a>
                </div>
            @else
                <div class="hp-grid">
                    @foreach ($properties as $property)
                        <article class="hp-card-wrap">
                            <a href="{{ route('property.show', $property) }}" class="hp-card">
                                <div class="hp-card-media">
                                    <img src="{{ $property->cover_url ?: $placeholder }}" alt="{{ $property->title }}"
                                        width="600" height="450" loading="lazy" decoding="async">

                                    <div class="hp-badges">
                                        <span @class([
                                            'hp-tag',
                                            $property->listing_type === 'rent' ? 'hp-tag--rent' : 'hp-tag--sale',
                                        ])>
                                            {{ $property->listing_type === 'rent' ? 'For rent' : 'For sale' }}
                                        </span>
                                        @if ($property->is_verified)
                                            <span class="hp-tag hp-tag--ok"><i class="fa-solid fa-circle-check"
                                                    aria-hidden="true"></i> Verified</span>
                                        @endif
                                        @if ($property->published_at?->gt(now()->subDays(7)))
                                            <span class="hp-tag hp-tag--new">New</span>
                                        @endif
                                    </div>

                                    <div class="hp-price-bar">
                                        {{ number_format((float) $property->price) }}
                                        <small>{{ $property->currency ?: 'ETB' }}</small>
                                        @if ($property->listing_type === 'rent')
                                            <small>/ {{ $periodShort[$property->rent_period ?? ''] ?? 'month' }}</small>
                                        @endif
                                    </div>
                                </div>

                                <div class="hp-card-body">
                                    <p class="hp-card-type">{{ $propertyTypes[$property->property_type] ?? '' }}</p>
                                    <h3 class="hp-card-title">{{ $property->title }}</h3>
                                    <p class="hp-card-loc">
                                        <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                                        {{ collect([$property->neighborhood, $property->sub_city, $property->city])->filter()->implode(', ') }}
                                    </p>

                                    <div class="hp-card-meta">
                                        @if ($property->bedrooms)
                                            <span title="Bedrooms"><i class="fa-solid fa-bed"
                                                    aria-hidden="true"></i>{{ $property->bedrooms }}<span
                                                    class="sr-only"> bedrooms</span></span>
                                        @endif
                                        @if ($property->bathrooms)
                                            <span title="Bathrooms"><i class="fa-solid fa-bath"
                                                    aria-hidden="true"></i>{{ $property->bathrooms }}<span
                                                    class="sr-only"> bathrooms</span></span>
                                        @endif
                                        @if ($property->area)
                                            <span title="Area"><i class="fa-solid fa-vector-square"
                                                    aria-hidden="true"></i>{{ $property->area }}
                                                {{ $property->area_unit }}</span>
                                        @endif
                                    </div>
                                </div>
                            </a>

                            <div class="hp-save">
                                <x-save-button :property="$property" :saved="in_array($property->id, $savedIds)" />
                            </div>
                        </article>
                    @endforeach
                </div>

                {{ $properties->fragment('listings')->links('vendor.pagination.public') }}
            @endif
        </div>
    </section>

    {{-- ================= HOW IT WORKS ================= --}}
    <section id="how-it-works" class="hp-section hp-section--alt" aria-labelledby="how-title">
        <div class="hp-wrap">
            <div class="hp-center">
                <p class="hp-kicker">How it works</p>
                <h2 id="how-title" class="hp-h2">Simple for renters. Careful for owners.</h2>
                <p class="hp-sub">Everything that goes live on BetGo has been checked by our team first.</p>
            </div>

            <div class="hp-tracks">
                <div class="hp-track">
                    <h3>If you are looking for a place</h3>
                    <ol class="hp-steps">
                        <li><strong>Search and filter</strong><span>Narrow down by area, property type, price and
                                bedrooms.</span></li>
                        <li><strong>Save your favorites</strong><span>Tap the heart to build a shortlist you can come back
                                to.</span></li>
                        <li><strong>Contact the owner or agent</strong><span>Send a message from your account and get
                                notified when they reply.</span></li>
                    </ol>
                </div>

                <div class="hp-track">
                    <h3>If you own or manage property</h3>
                    <ol class="hp-steps">
                        <li><strong>Register and get verified</strong><span>Submit your national ID (agents also add a TIN).
                                We review it.</span></li>
                        <li><strong>Pick a plan</strong><span>Pay for a single listing or choose a monthly plan.</span></li>
                        <li><strong>Add your listing</strong><span>Upload photos, add the details and pin the exact location
                                on the map.</span></li>
                        <li><strong>We review, then it goes live</strong><span>Approved listings are published and show a
                                verified badge.</span></li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= FEATURES ================= --}}
    <section id="features" class="hp-section" aria-labelledby="features-title">
        <div class="hp-wrap">
            <div class="hp-center">
                <p class="hp-kicker">Why BetGo</p>
                <h2 id="features-title" class="hp-h2">Built to make property search less risky</h2>
            </div>

            <div class="hp-features">
                <div class="hp-feature">
                    <i class="fa-solid fa-clipboard-check" aria-hidden="true"></i>
                    <h3>Reviewed listings</h3>
                    <p>Nothing is published until our team approves it, so you see fewer fake or outdated ads.</p>
                </div>
                <div class="hp-feature">
                    <i class="fa-solid fa-id-card" aria-hidden="true"></i>
                    <h3>Verified owners and agents</h3>
                    <p>Everyone who posts has been checked with their national ID. Agents also provide a TIN.</p>
                </div>
                <div class="hp-feature">
                    <i class="fa-solid fa-tag" aria-hidden="true"></i>
                    <h3>Clear prices</h3>
                    <p>The price and currency are on every card, so you know the cost before you open a listing.</p>
                </div>
                <div class="hp-feature">
                    <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                    <h3>Exact map location</h3>
                    <p>Owners pin the property on a map, so you can see where it really is.</p>
                </div>
                <div class="hp-feature">
                    <i class="fa-solid fa-heart" aria-hidden="true"></i>
                    <h3>Save and compare</h3>
                    <p>Keep a shortlist of the places you like and return to it any time.</p>
                </div>
                <div class="hp-feature">
                    <i class="fa-solid fa-comments" aria-hidden="true"></i>
                    <h3>Direct messaging</h3>
                    <p>Talk to the owner or agent inside BetGo and get notified when they answer.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= PRICING TEASER ================= --}}
    <section id="pricing" class="hp-section hp-section--alt" aria-labelledby="pricing-title">
        <div class="hp-wrap">
            <div class="hp-teaser">
                <div>
                    <p class="hp-kicker">Pricing</p>
                    <h2 id="pricing-title" class="hp-h2">Free to search. Simple plans to publish.</h2>
                    <p class="hp-sub">
                        Searching, saving and messaging are always free.
                        @if ($startingPrice)
                            Owners and agents can start from {{ number_format($startingPrice) }} ETB for a single listing.
                        @endif
                    </p>
                </div>
                <a href="{{ route('pricing') }}" class="hp-btn hp-btn--primary">
                    See pricing <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </section>

    {{-- ================= FAQ ================= --}}
    @if ($faqs->isNotEmpty())
        <section id="faq" class="hp-section" aria-labelledby="faq-title">
            <div class="hp-wrap">
                <div class="hp-center">
                    <p class="hp-kicker">FAQ</p>
                    <h2 id="faq-title" class="hp-h2">Common questions</h2>
                </div>

                <div class="hp-faq">
                    @foreach ($faqs as $faq)
                        <details>
                            <summary>{{ $faq->question }}</summary>
                            <div class="md-body">{{ \App\Support\Markdown::render($faq->answer) }}</div>
                        </details>
                    @endforeach
                </div>

                <p class="hp-note"><a href="{{ route('faq') }}" style="color:var(--accent)">See all questions →</a></p>
            </div>
        </section>
    @endif

    {{-- ================= CLOSING BANNER ================= --}}
    @if ($postCta)
        <section class="hp-wrap hp-cta" aria-labelledby="cta-title">
            <h2 id="cta-title" class="hp-h2">Have a property to rent or sell?</h2>
            <p class="hp-sub">Create an account, get verified and publish your first listing.</p>
            <a href="{{ $postCta[1] }}" class="hp-btn hp-btn--primary">
                {{ $postCta[0] }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </a>
        </section>
    @endif

    <script type="application/ld+json">
        {!! json_encode(
            [
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => config('betgo.name'),
                'url' => route('home'),
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => route('home') . '?q={search_term_string}',
                    'query-input' => 'required name=search_term_string',
                ],
            ],
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG,
        ) !!}
    </script>
@endsection

@push('scripts')
    <script>
        (function() {
            'use strict';

            // Selects submit the filter form when changed.
            document.querySelectorAll('select[data-autosubmit]').forEach(function(s) {
                s.addEventListener('change', function() {
                    s.form.requestSubmit();
                });
            });

            // Keep URLs short and shareable: don't send empty fields.
            document.querySelectorAll('form[data-clean]').forEach(function(f) {
                f.addEventListener('submit', function() {
                    f.querySelectorAll('input, select').forEach(function(el) {
                        if (el.value === '') el.disabled = true;
                    });
                });
            });
            window.addEventListener('pageshow', function(e) {
                if (e.persisted) document.querySelectorAll('form[data-clean] [disabled]').forEach(function(el) {
                    el.disabled = false;
                });
            });

            // Highlight the nav link for the section on screen.
            var links = Array.prototype.slice.call(document.querySelectorAll('.home-nav-items a'));
            var homeLink = links.find(function(a) {
                return !a.dataset.section && a.classList.contains('active');
            });
            var tracked = links.filter(function(a) {
                return a.dataset.section;
            });
            if (!('IntersectionObserver' in window) || !tracked.length) return;

            var visible = new Set();

            function paint() {
                var current = tracked.find(function(a) {
                    return visible.has(a.dataset.section);
                });
                links.forEach(function(a) {
                    var on = current ? a === current : a === homeLink;
                    a.classList.toggle('active', on);
                    if (on) a.setAttribute('aria-current', 'page');
                    else a.removeAttribute('aria-current');
                });
            }

            var io = new IntersectionObserver(function(entries) {
                entries.forEach(function(e) {
                    if (e.isIntersecting) visible.add(e.target.id);
                    else visible.delete(e.target.id);
                });
                paint();
            }, {
                rootMargin: '-35% 0px -55% 0px'
            });

            tracked.forEach(function(a) {
                var el = document.getElementById(a.dataset.section);
                if (el) io.observe(el);
            });
        })();
    </script>
@endpush
