@extends('layouts.app')
@section('title', $listing->title)

@section('content')
    <style>
        .card {
            background-color: #1e1b1b;
            border-radius: 0.75rem;
            border: 1px solid #3a3a3d;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
            padding: 1.25rem;
        }

        .bgforall {
            background-color: #1e1b1b;
            border: 1px solid #3a3a3d;
            color: #ffffff;
        }
    </style>

    <div class="space-y-6 text-white max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

        {{-- Top Bar / Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bgforall p-6 rounded-xl shadow-sm">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-white">{{ $listing->title }}</h1>
                    <span
                        class="px-2.5 py-1 text-xs font-semibold rounded-full uppercase tracking-wider 
                    {{ $listing->status === 'published' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-amber-500/20 text-amber-400 border border-amber-500/30' }}">
                        {{ $listing->status }}
                    </span>
                </div>
                <p class="text-sm text-gray-400 mt-1">
                    Created on {{ $listing->created_at->format('M d, Y') }} • Last updated
                    {{ $listing->updated_at->diffForHumans() }}
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('agent.listings.index') }}"
                    class="px-4 py-2 rounded-lg border border-[#3a3a3d] text-gray-300 hover:bg-[#2a2727] text-sm font-medium transition">
                    ← Back to listings
                </a>
                <a href="{{ route('agent.listings.edit', $listing) }}"
                    class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 text-sm font-medium transition">
                    Edit Listing
                </a>
                <form action="{{ route('agent.listings.destroy', $listing) }}" method="POST"
                    onsubmit="return confirm('Are you sure you want to delete this listing permanently?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="px-4 py-2 rounded-lg bg-red-600/20 text-red-400 border border-red-600/30 hover:bg-red-600 hover:text-white text-sm font-medium transition">
                        Delete
                    </button>
                </form>
            </div>
        </div>

        {{-- Session Success Message with Auto-Dismiss --}}
        @if (session('success'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition.opacity.duration.500ms
                class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-xl text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Main Content Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Left Column: Photos & Details --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Photos Gallery Section --}}
                <div class="bgforall rounded-xl shadow-sm p-6 space-y-4">
                    <h3 class="font-semibold text-white text-lg">Property Photos</h3>

                    @if ($listing->media->count() > 0)
                        {{-- Cover Photo Highlight --}}
                        @php
                            $coverPhoto =
                                $listing->media->first(fn($m) => $m->pivot->is_cover) ?? $listing->media->first();
                        @endphp
                        <div class="relative h-72 w-full rounded-xl overflow-hidden border border-[#3a3a3d]">
                            <img src="{{ $coverPhoto->url }}" alt="{{ $listing->title }}"
                                class="w-full h-full object-cover">
                            <span
                                class="absolute bottom-3 left-3 bg-indigo-600 text-white text-xs px-2.5 py-1 rounded-md font-medium shadow">
                                Cover Photo
                            </span>
                        </div>

                        {{-- Thumbnail Gallery Grid --}}
                        @if ($listing->media->count() > 1)
                            <div class="grid grid-cols-3 sm:grid-cols-5 gap-3 pt-2">
                                @foreach ($listing->media as $media)
                                    <div class="relative h-20 rounded-lg overflow-hidden border border-[#3a3a3d] group">
                                        <img src="{{ $media->url }}" alt="" class="w-full h-full object-cover">
                                        @if ($media->pivot->is_cover)
                                            <div
                                                class="absolute inset-0 bg-indigo-900/40 ring-2 ring-indigo-500 pointer-events-none">
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @else
                        <div class="text-center py-10 bg-[#161414] rounded-xl border border-dashed border-[#3a3a3d]">
                            <p class="text-sm text-gray-400">No photos uploaded for this listing yet.</p>
                        </div>
                    @endif
                </div>

                {{-- Basic Information --}}
                <div class="bgforall rounded-xl shadow-sm p-6 space-y-4">
                    <h3 class="font-semibold text-white text-lg">Basic Information</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pb-4 border-b border-[#3a3a3d]">
                        <div>
                            <span class="block text-xs text-gray-400 uppercase tracking-wider">Property Type</span>
                            <span class="text-base font-medium capitalize">{{ $listing->property_type }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-gray-400 uppercase tracking-wider">Listing Type</span>
                            <span class="text-base font-medium uppercase">For {{ $listing->listing_type }}</span>
                        </div>
                    </div>

                    <div>
                        <span class="block text-xs text-gray-400 uppercase tracking-wider mb-1">Description</span>
                        <div
                            class="text-gray-300 text-sm leading-relaxed whitespace-pre-line bg-[#161414] p-4 rounded-lg border border-[#3a3a3d]">
                            {{ $listing->description ?: 'No description provided.' }}
                        </div>
                    </div>
                </div>

                {{-- Location Details --}}
                <div class="bgforall rounded-xl shadow-sm p-6 space-y-4">
                    <h3 class="font-semibold text-white text-lg">Location Information</h3>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div>
                            <span class="block text-xs text-gray-400 uppercase tracking-wider">City</span>
                            <span class="text-sm font-medium">{{ $listing->city ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-gray-400 uppercase tracking-wider">Sub-City</span>
                            <span class="text-sm font-medium">{{ $listing->sub_city ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-gray-400 uppercase tracking-wider">Woreda</span>
                            <span class="text-sm font-medium">{{ $listing->woreda ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-gray-400 uppercase tracking-wider">Neighborhood</span>
                            <span class="text-sm font-medium">{{ $listing->neighborhood ?? '—' }}</span>
                        </div>
                    </div>

                    @if ($listing->address)
                        <div class="pt-3 border-t border-[#3a3a3d]">
                            <span class="block text-xs text-gray-400 uppercase tracking-wider mb-1">Specific Address</span>
                            <p class="text-sm text-gray-300">{{ $listing->address }}</p>
                        </div>
                    @endif
                </div>

            </div>

            {{-- Right Column: Price, Specifications & Meta --}}
            <div class="space-y-6">

                {{-- Price Card --}}
                <div class="bgforall rounded-xl shadow-sm p-6 space-y-3">
                    <h3 class="font-semibold text-white text-lg">Pricing</h3>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-extrabold text-white">
                            {{ number_format($listing->price, 2) }}
                        </span>
                        <span class="text-sm text-gray-400 font-medium">{{ $listing->currency ?? 'ETB' }}</span>
                    </div>
                    <div>
                        <span
                            class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium 
                        {{ $listing->price_negotiable ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/30' : 'bg-gray-800 text-gray-400 border border-[#3a3a3d]' }}">
                            {{ $listing->price_negotiable ? '✓ Price is Negotiable' : 'Fixed Price' }}
                        </span>
                    </div>
                </div>

                {{-- Specifications Card --}}
                <div class="bgforall rounded-xl shadow-sm p-6 space-y-4">
                    <h3 class="font-semibold text-white text-lg">Specifications</h3>

                    <div class="grid grid-cols-3 gap-3 text-center">
                        <div class="bg-[#161414] p-3 rounded-lg border border-[#3a3a3d]">
                            <span class="block text-xs text-gray-400 uppercase">Beds</span>
                            <span class="text-lg font-bold text-white">{{ $listing->bedrooms ?? '—' }}</span>
                        </div>
                        <div class="bg-[#161414] p-3 rounded-lg border border-[#3a3a3d]">
                            <span class="block text-xs text-gray-400 uppercase">Baths</span>
                            <span class="text-lg font-bold text-white">{{ $listing->bathrooms ?? '—' }}</span>
                        </div>
                        <div class="bg-[#161414] p-3 rounded-lg border border-[#3a3a3d]">
                            <span class="block text-xs text-gray-400 uppercase">Parking</span>
                            <span class="text-lg font-bold text-white">{{ $listing->parking_spaces ?? '—' }}</span>
                        </div>
                    </div>

                    <div class="space-y-3 pt-2">
                        <div class="flex justify-between items-center text-sm py-2 border-b border-[#3a3a3d]">
                            <span class="text-gray-400">Total Area</span>
                            <span
                                class="font-medium text-white">{{ $listing->area ? $listing->area . ' ' . ($listing->area_unit ?? 'sqm') : '—' }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm py-2">
                            <span class="text-gray-400">Furnishing</span>
                            <span
                                class="font-medium text-white capitalize">{{ str_replace('_', ' ', $listing->furnishing_status) ?: '—' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Quick Metadata Card --}}
                <div class="bgforall rounded-xl shadow-sm p-6 space-y-3">
                    <h3 class="font-semibold text-white text-sm uppercase tracking-wider text-gray-400">System Info</h3>
                    <div class="text-xs space-y-2 text-gray-400">
                        <div class="flex justify-between">
                            <span>Listing ID:</span>
                            <span class="text-gray-200">#{{ $listing->id }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Published At:</span>
                            <span
                                class="text-gray-200">{{ $listing->published_at ? $listing->published_at->format('M d, Y H:i') : 'Not Published' }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
