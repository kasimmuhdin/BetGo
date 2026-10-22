@extends('layouts.app')
@section('title', 'All Listings')
@section('content')
    <div class="max-w-7xl mx-auto px-4 py-6 text-white space-y-6">

        <div>
            <h1 class="text-2xl font-bold tracking-tight">All Listings</h1>
            <p class="text-sm text-gray-400 mt-1">Every property on the platform, regardless of status.</p>
        </div>

        @if (session('success'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition.opacity.duration.500ms
                class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-xl text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
            <a href="{{ route('admin.listings.all') }}"
                class="bg-gradient-to-br from-[#232020] to-[#1e1b1b] border border-[#3a3a3d] rounded-xl p-5 hover:border-indigo-500/50 transition">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Total</p>
                <p class="text-2xl font-extrabold text-white mt-1">{{ $stats['total'] }}</p>
            </a>
            <a href="{{ route('admin.listings.all', ['status' => 'published']) }}"
                class="bg-gradient-to-br from-[#232020] to-[#1e1b1b] border border-[#3a3a3d] rounded-xl p-5 hover:border-emerald-500/50 transition">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Published</p>
                <p class="text-2xl font-extrabold text-emerald-400 mt-1">{{ $stats['published'] }}</p>
            </a>
            <a href="{{ route('admin.listings.pending') }}"
                class="bg-gradient-to-br from-[#232020] to-[#1e1b1b] border border-[#3a3a3d] rounded-xl p-5 hover:border-amber-500/50 transition">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Pending</p>
                <p class="text-2xl font-extrabold text-amber-400 mt-1">{{ $stats['pending'] }}</p>
            </a>
            <a href="{{ route('admin.listings.all', ['status' => 'rejected']) }}"
                class="bg-gradient-to-br from-[#232020] to-[#1e1b1b] border border-[#3a3a3d] rounded-xl p-5 hover:border-red-500/50 transition">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Rejected</p>
                <p class="text-2xl font-extrabold text-red-400 mt-1">{{ $stats['rejected'] }}</p>
            </a>
            <a href="{{ route('admin.listings.all', ['status' => 'draft']) }}"
                class="bg-gradient-to-br from-[#232020] to-[#1e1b1b] border border-[#3a3a3d] rounded-xl p-5 hover:border-gray-500/50 transition">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Draft</p>
                <p class="text-2xl font-extrabold text-gray-300 mt-1">{{ $stats['draft'] }}</p>
            </a>
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

            <select name="status"
                class="rounded-lg px-3 py-2.5 bg-[#161414] border border-[#3a3a3d] text-sm text-white focus:outline-none focus:border-indigo-500">
                <option value="" class="bg-[#1e1b1b]">All Statuses</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" class="bg-[#1e1b1b]" @selected(request('status') === $value)>
                        {{ $label }}</option>
                @endforeach
            </select>

            <select name="property_type"
                class="rounded-lg px-3 py-2.5 bg-[#161414] border border-[#3a3a3d] text-sm text-white focus:outline-none focus:border-indigo-500">
                <option value="" class="bg-[#1e1b1b]">All Property Types</option>
                @foreach ($propertyTypes as $value => $label)
                    <option value="{{ $value }}" class="bg-[#1e1b1b]" @selected(request('property_type') === $value)>
                        {{ $label }}</option>
                @endforeach
            </select>

            <button type="submit"
                class="px-5 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-sm font-medium transition">Filter</button>

            @if (request()->hasAny(['q', 'status', 'property_type', 'listing_type']))
                <a href="{{ route('admin.listings.all') }}"
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
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Updated</th>
                            <th class="px-6 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#3a3a3d]">
                        @forelse ($listings as $listing)
                            @php
                                $badge = match ($listing->status) {
                                    'published' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                    'pending' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                    'rejected' => 'bg-red-500/10 text-red-400 border-red-500/20',
                                    'archived' => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
                                    default => 'bg-gray-800 text-gray-300 border-[#3a3a3d]',
                                };
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
                                            <p class="font-medium text-gray-200 truncate max-w-[200px]">
                                                {{ $listing->title }}</p>
                                            <p class="text-xs text-gray-500 truncate max-w-[200px]">
                                                {{ $listing->city ?? 'Location N/A' }} · {{ $listing->media_count }}
                                                photos</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-300">{{ $listing->owner->name ?? 'Unknown' }}</td>
                                <td class="px-6 py-4">
                                    <span class="text-gray-300 capitalize">{{ $listing->property_type }}</span>
                                    <span class="block text-xs text-gray-500 uppercase">For
                                        {{ $listing->listing_type }}</span>
                                </td>
                                <td class="px-6 py-4 font-medium text-white whitespace-nowrap">
                                    {{ number_format($listing->price, 2) }} <span
                                        class="text-gray-500 text-xs">{{ $listing->currency }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="px-2.5 py-1 rounded-md text-xs font-semibold border capitalize {{ $badge }}">{{ $listing->status }}</span>
                                </td>
                                <td class="px-6 py-4 text-gray-500 text-xs whitespace-nowrap">
                                    {{ $listing->updated_at->diffForHumans() }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.listings.show', $listing) }}"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-indigo-600/10 border border-indigo-600/30 text-indigo-400 hover:bg-indigo-600 hover:text-white text-xs font-semibold transition">
                                        View
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center text-gray-500">No listings match these
                                    filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-2">{{ $listings->links('vendor.pagination.custom-dark') }}</div>
    </div>
@endsection
