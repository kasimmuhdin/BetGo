@extends('layouts.app')
@section('title', 'My Listings')
@section('content')

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-white">My Listings</h1>
        <a href="{{ route('agent.listings.create') }}"
            class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">+
            New Listing</a>
    </div>

    @if (session('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition:leave="transition-opacity duration-500"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" x-cloak
            class="mb-4 px-4 py-3 rounded-xl bg-emerald-500/10 text-emerald-400 text-sm border border-emerald-500/30">
            {{ session('success') }}
        </div>
    @endif

    <div class="bgforall rounded-xl shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-[#3a3a3d] text-sm">
            <thead class="bg-[#161414] text-left text-xs font-semibold text-gray-400 uppercase">
                <tr>
                    <th class="px-6 py-3">Listing</th>
                    <th class="px-6 py-3">Type</th>
                    <th class="px-6 py-3">Price</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#2a2727]">
                @forelse($listings as $listing)
                    <tr class="hover:bg-[#2a2727] cursor-pointer transition"
                        onclick="window.location='{{ route('agent.listings.show', $listing) }}'">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                @if ($listing->cover_url)
                                    <img src="{{ $listing->cover_url }}"
                                        class="h-12 w-16 object-cover rounded-lg border border-[#3a3a3d]">
                                @else
                                    <div
                                        class="h-12 w-16 rounded-lg bg-[#161414] border border-[#3a3a3d] flex items-center justify-center text-gray-500 text-xs">
                                        No photo</div>
                                @endif
                                <div>
                                    <span class="font-medium text-white">{{ $listing->title }}</span>
                                    <p class="text-xs text-gray-400">{{ $listing->city }} ·
                                        {{ $listing->media_count }} photo(s)</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-200 capitalize">{{ $listing->property_type }}<br><span
                                class="text-xs text-gray-400">for {{ $listing->listing_type }}</span></td>
                        <td class="px-6 py-4 font-medium text-white">
                            {{ number_format($listing->price, 2) }} {{ $listing->currency }}</td>
                        <td class="px-6 py-4">
                            @php $colors = ['draft' => 'bg-gray-800 text-gray-300 border border-[#3a3a3d]', 'pending' => 'bg-amber-500/20 text-amber-400 border border-amber-500/30', 'published' => 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30', 'archived' => 'bg-gray-800 text-gray-500 border border-[#3a3a3d]', 'rejected' => 'bg-red-500/20 text-red-400 border border-red-500/30']; @endphp
                            <span
                                class="px-2.5 py-1 rounded-full text-xs font-medium {{ $colors[$listing->status] ?? 'bg-gray-800 text-gray-300 border border-[#3a3a3d]' }}">
                                {{ ucfirst($listing->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <a href="{{ route('agent.listings.edit', $listing) }}" onclick="event.stopPropagation()"
                                class="text-indigo-400 hover:text-indigo-300 mr-3">Edit</a>
                            <form action="{{ route('agent.listings.destroy', $listing) }}" method="POST" class="inline"
                                onclick="event.stopPropagation()"
                                onsubmit="return confirm('Delete this listing? This cannot be undone.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-300">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            No listings yet. <a href="{{ route('agent.listings.create') }}"
                                class="text-indigo-400 hover:underline">Create your first one</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $listings->links() }}</div>
@endsection
