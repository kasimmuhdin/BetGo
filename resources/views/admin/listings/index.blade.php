@extends('layouts.app')
@section('title', 'Pending Listings')
@section('content')
    <div class="max-w-7xl mx-auto px-4 py-6 text-white space-y-6" x-data="{ toastVisible: {{ session('success') ? 'true' : 'false' }} }">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Listings Awaiting Review</h1>
                <p class="text-sm text-gray-400 mt-1">Approve or reject property submissions before they go live.</p>
            </div>
        </div>

        {{-- Success toast (auto-dismiss, matches your listing show page pattern) --}}
        @if (session('success'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition.opacity.duration.500ms
                class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-gradient-to-br from-[#232020] to-[#1e1b1b] border border-[#3a3a3d] rounded-xl p-5">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Pending Review</p>
                <p class="text-3xl font-extrabold text-amber-400 mt-2">{{ $stats['pending_count'] }}</p>
            </div>
            <div class="bg-gradient-to-br from-[#232020] to-[#1e1b1b] border border-[#3a3a3d] rounded-xl p-5">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Approved Today</p>
                <p class="text-3xl font-extrabold text-emerald-400 mt-2">{{ $stats['approved_today'] }}</p>
            </div>
            <div class="bg-gradient-to-br from-[#232020] to-[#1e1b1b] border border-[#3a3a3d] rounded-xl p-5">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Rejected Today</p>
                <p class="text-3xl font-extrabold text-red-400 mt-2">{{ $stats['rejected_today'] }}</p>
            </div>
            <div class="bg-gradient-to-br from-[#232020] to-[#1e1b1b] border border-[#3a3a3d] rounded-xl p-5">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Avg. Wait Time</p>
                <p class="text-3xl font-extrabold text-indigo-400 mt-2">{{ $stats['avg_wait_hours'] }}<span
                        class="text-base font-medium text-gray-500">h</span></p>
                @if ($stats['oldest_wait_hours'] > 48)
                    <p class="text-[11px] text-red-400 mt-1">Oldest waiting {{ $stats['oldest_wait_hours'] }}h</p>
                @endif
            </div>
        </div>

        {{-- Filters --}}
        <form method="GET" class="bg-[#1e1b1b] border border-[#3a3a3d] rounded-xl p-4 flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-500" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"></path>
                </svg>
                <input type="text" name="q" value="{{ request('q') }}"
                    placeholder="Search by title, city, or owner…"
                    class="w-full pl-9 pr-3 py-2.5 rounded-lg bg-[#161414] border border-[#3a3a3d] text-white text-sm placeholder:text-gray-500 focus:outline-none focus:border-indigo-500">
            </div>

            <select name="property_type"
                class="rounded-lg px-3 py-2.5 bg-[#161414] border border-[#3a3a3d] text-sm text-white focus:outline-none focus:border-indigo-500">
                <option value="" class="bg-[#1e1b1b]">All Property Types</option>
                @foreach ($propertyTypes as $value => $label)
                    <option value="{{ $value }}" class="bg-[#1e1b1b]" @selected(request('property_type') === $value)>
                        {{ $label }}</option>
                @endforeach
            </select>

            <select name="listing_type"
                class="rounded-lg px-3 py-2.5 bg-[#161414] border border-[#3a3a3d] text-sm text-white focus:outline-none focus:border-indigo-500">
                <option value="" class="bg-[#1e1b1b]">Rent & Sale</option>
                <option value="rent" class="bg-[#1e1b1b]" @selected(request('listing_type') === 'rent')>For Rent</option>
                <option value="sale" class="bg-[#1e1b1b]" @selected(request('listing_type') === 'sale')>For Sale</option>
            </select>

            <button type="submit"
                class="px-5 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-sm font-medium transition">Filter</button>

            @if (request()->hasAny(['q', 'property_type', 'listing_type']))
                <a href="{{ route('admin.listings.index') }}"
                    class="px-4 py-2.5 rounded-lg border border-[#3a3a3d] text-sm text-gray-300 hover:bg-[#2a2727] transition text-center">Clear</a>
            @endif
        </form>

        {{-- Table --}}
        <div class="bg-[#1e1b1b] border border-[#3a3a3d] rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-[#161414] text-xs uppercase font-semibold text-gray-400">
                        <tr>
                            <th class="px-6 py-4">Listing</th>
                            <th class="px-6 py-4">Owner</th>
                            <th class="px-6 py-4">Type</th>
                            <th class="px-6 py-4">Price</th>
                            <th class="px-6 py-4">Photos</th>
                            <th class="px-6 py-4">Waiting</th>
                            <th class="px-6 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#3a3a3d]">
                        @forelse ($listings as $listing)
                            @php
                                $waitHours = $listing->submitted_at ? now()->diffInHours($listing->submitted_at) : 0;
                                $waitColor =
                                    $waitHours >= 48
                                        ? 'text-red-400 bg-red-500/10 border-red-500/20'
                                        : ($waitHours >= 24
                                            ? 'text-amber-400 bg-amber-500/10 border-amber-500/20'
                                            : 'text-gray-400 bg-gray-800 border-[#3a3a3d]');
                            @endphp
                            <tr class="hover:bg-[#2a2727] transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @if ($listing->cover_url)
                                            <img src="{{ $listing->cover_url }}"
                                                class="h-11 w-14 object-cover rounded-md border border-[#3a3a3d] shrink-0">
                                        @else
                                            <div
                                                class="h-11 w-14 rounded-md bg-[#161414] border border-[#3a3a3d] flex items-center justify-center text-gray-600 text-[9px] shrink-0">
                                                No img</div>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="font-medium text-gray-200 truncate max-w-[220px]">
                                                {{ $listing->title }}</p>
                                            <p class="text-xs text-gray-500 truncate max-w-[220px]">
                                                {{ $listing->city ?? 'Location N/A' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-7 h-7 rounded-full bg-indigo-500/20 flex items-center justify-center text-indigo-400 text-xs font-bold shrink-0">
                                            {{ strtoupper(substr($listing->owner->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <span class="text-gray-300">{{ $listing->owner->name ?? 'Unknown' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-gray-300 capitalize">{{ $listing->property_type }}</span>
                                    <span class="block text-xs text-gray-500 uppercase">For
                                        {{ $listing->listing_type }}</span>
                                </td>
                                <td class="px-6 py-4 font-medium text-white whitespace-nowrap">
                                    {{ number_format($listing->price, 2) }} <span
                                        class="text-gray-500 text-xs">{{ $listing->currency }}</span>
                                </td>
                                <td class="px-6 py-4 text-gray-400">{{ $listing->media_count }}</td>
                                <td class="px-6 py-4">
                                    <span
                                        class="px-2.5 py-1 rounded-md text-xs font-semibold border whitespace-nowrap {{ $waitColor }}"
                                        title="Submitted {{ optional($listing->submitted_at)->format('M d, Y H:i') }}">
                                        {{ $listing->submitted_at ? $listing->submitted_at->diffForHumans(null, true) : '—' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.listings.show', $listing) }}"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-indigo-600/10 border border-indigo-600/30 text-indigo-400 hover:bg-indigo-600 hover:text-white text-xs font-semibold transition">
                                        Review
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <svg class="w-12 h-12 text-[#3a3a3d] mx-auto mb-3" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <p class="text-gray-400 font-medium">All caught up</p>
                                    <p class="text-sm text-gray-600 mt-1">Nothing is waiting for review right now.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-2">{{ $listings->links('vendor.pagination.custom-dark') }}</div>
    </div>
@endsection
