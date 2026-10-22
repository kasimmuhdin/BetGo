@extends('layouts.app')
@section('title', 'Saved Listings')
@section('content')
    <div class="max-w-7xl mx-auto text-white space-y-6" x-data="{ total: {{ $saved->total() }}, onPage: {{ $saved->count() }} }" @listing-removed="total--; onPage--">

        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Saved Listings</h1>
                <p class="text-sm text-gray-400 mt-1">
                    <span x-text="total"></span>
                    <span x-text="total === 1 ? 'property saved' : 'properties saved'"></span>
                </p>
            </div>

            <form method="GET" x-show="total > 1" x-cloak>
                <select name="sort" onchange="this.form.submit()"
                    class="rounded-lg px-3 py-2.5 bg-[#161414] border border-[#3a3a3d] text-sm text-white focus:outline-none focus:border-indigo-500">
                    <option value="recent" class="bg-[#1e1b1b]" @selected($sort === 'recent')>Recently saved</option>
                    <option value="price_asc" class="bg-[#1e1b1b]" @selected($sort === 'price_asc')>Price: low to high</option>
                    <option value="price_desc" class="bg-[#1e1b1b]" @selected($sort === 'price_desc')>Price: high to low</option>
                </select>
            </form>
        </div>

        {{-- Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6" x-show="onPage > 0" x-cloak>
            @foreach ($saved as $property)
                @php $available = $property->isPublic(); @endphp

                <article x-data="unsaveButton(@js(['url' => route('saved-listings.destroy', $property)]))" x-show="!removed" x-transition.opacity.duration.300ms
                    class="bg-[#1e1b1b] border border-[#3a3a3d] rounded-2xl overflow-hidden flex flex-col">

                    <div class="relative aspect-[4/3] bg-[#161414]">
                        <img src="{{ $property->cover_url ?? 'https://placehold.co/600x400/1e1b1b/6366f1?text=No+Photo' }}"
                            alt="{{ $property->title }}" loading="lazy"
                            class="w-full h-full object-cover {{ $available ? '' : 'grayscale opacity-50' }}">

                        <span
                            class="absolute top-3 left-3 bg-black/60 backdrop-blur-sm px-2.5 py-1 rounded-lg text-[11px] font-bold uppercase tracking-wide">
                            {{ $property->listing_type === 'rent' ? 'For Rent' : 'For Sale' }}
                        </span>

                        @unless ($available)
                            <span
                                class="absolute top-3 right-3 bg-red-500/90 px-2.5 py-1 rounded-lg text-[11px] font-bold uppercase tracking-wide">
                                No longer available
                            </span>
                        @endunless
                    </div>

                    <div class="p-5 flex-1 flex flex-col">
                        <p class="text-xl font-extrabold">
                            {{ number_format($property->price) }}
                            <span class="text-sm font-medium text-gray-400">{{ $property->currency ?? 'ETB' }}</span>
                            @if ($property->listing_type === 'rent')
                                <span class="text-sm font-medium text-gray-400">/{{ $property->rent_period ?? 'mo' }}</span>
                            @endif
                        </p>

                        <h3 class="text-base font-semibold text-gray-100 line-clamp-1 mt-1">{{ $property->title }}</h3>
                        <p class="text-sm text-gray-400 line-clamp-1 mt-1">
                            {{ collect([$property->neighborhood, $property->sub_city, $property->city])->filter()->implode(', ') }}
                        </p>

                        <div class="flex items-center gap-4 mt-4 pt-4 border-t border-[#3a3a3d] text-sm text-gray-400">
                            @if ($property->bedrooms)
                                <span>{{ $property->bedrooms }} bed</span>
                            @endif
                            @if ($property->bathrooms)
                                <span>{{ $property->bathrooms }} bath</span>
                            @endif
                            @if ($property->area)
                                <span>{{ $property->area }} {{ $property->area_unit }}</span>
                            @endif
                        </div>

                        <p class="text-xs text-gray-600 mt-3">Saved {{ $property->pivot->created_at->diffForHumans() }}</p>

                        <div class="flex gap-2 mt-4">
                            @if ($available)
                                <a href="{{ route('property.show', $property) }}"
                                    class="flex-1 text-center py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-sm font-semibold transition">
                                    View listing
                                </a>
                            @else
                                <span
                                    class="flex-1 text-center py-2.5 rounded-lg bg-[#161414] border border-[#3a3a3d] text-sm text-gray-500 cursor-not-allowed">
                                    Unavailable
                                </span>
                            @endif

                            <button type="button" @click="remove()" :disabled="busy"
                                class="px-4 py-2.5 rounded-lg border border-red-600/30 text-red-400 hover:bg-red-600 hover:text-white text-sm font-medium transition flex items-center gap-2 disabled:opacity-60">
                                <svg x-show="busy" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                                    </path>
                                </svg>
                                <span x-text="busy ? 'Removing…' : 'Remove'"></span>
                            </button>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- Empty: nothing saved at all --}}
        <div x-show="total === 0" x-cloak class="bg-[#1e1b1b] border border-[#3a3a3d] rounded-2xl py-20 px-6 text-center">
            <div class="w-16 h-16 rounded-full bg-[#2a2727] flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path
                        d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                </svg>
            </div>
            <h3 class="text-lg font-bold">No saved listings yet</h3>
            <p class="text-gray-400 text-sm mt-1">Tap the heart on any property to keep it here.</p>
            <a href="{{ route('home') }}"
                class="inline-block mt-6 px-6 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-sm font-semibold transition">
                Browse properties
            </a>
        </div>

        {{-- Empty: this page was cleared but other pages still have items --}}
        <div x-show="total > 0 && onPage === 0" x-cloak
            class="bg-[#1e1b1b] border border-[#3a3a3d] rounded-2xl py-12 px-6 text-center">
            <p class="text-gray-300">You removed everything on this page.</p>
            <a href="{{ route('saved-listings.index') }}"
                class="inline-block mt-4 text-indigo-400 hover:text-indigo-300 text-sm font-medium">
                Back to your saved list →
            </a>
        </div>

        <div>{{ $saved->links('vendor.pagination.custom-dark') }}</div>
    </div>
@endsection
