@extends('layouts.app')
@section('title', 'My Listings')
@section('content')

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-white">My Listings</h1>
        <a href="{{ route('owner.listings.create') }}"
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
                        onclick="window.location='{{ route('owner.listings.show', $listing) }}'">
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
                        <td class="px-6 py-4 text-right whitespace-nowrap flex flex-row">
                            <a href="{{ route('owner.listings.edit', $listing) }}" onclick="event.stopPropagation()"
                                class="text-indigo-400 hover:text-indigo-300 mr-3">Edit</a>
                            <button @click="deleteModal = true" onclick="event.stopPropagation()"
                                class="text-red-400 hover:text-red-300">Delete</button>

                            <div x-data="deleteModal: false, isSubmitting: false">

                                <!-- Delet Modal -->
                                <div x-show="deleteModal" style="display: none;" class="fixed z-50 inset-0 overflow-y-auto">
                                    <div
                                        class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                                        <div x-show="deleteModal" x-transition.opacity
                                            class="fixed inset-0 bg-black/80 backdrop-blur-sm" @click="deleteModal = false">
                                        </div>
                                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                                        <div x-show="deleteModal" x-transition:enter="ease-out duration-300"
                                            x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
                                            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                            class="inline-block align-bottom bg-[#18181b] border border-gray-700 rounded-xl text-left overflow-hidden shadow-2xl transform sm:my-8 sm:align-middle sm:max-w-lg w-full">
                                            <form action="{{ route('owner.listings.destroy', $listing) }}" method="POST"
                                                @submit="isSubmitting = true">
                                                @csrf
                                                @method('DELETE')
                                                <div class="px-6 pt-6 pb-4">
                                                    <div class="sm:flex sm:items-start">
                                                        <div
                                                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-emerald-500/20 sm:mx-0 sm:h-10 sm:w-10">
                                                            <svg class="h-6 w-6 text-emerald-500" fill="none"
                                                                viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M5 13l4 4L19 7" />
                                                            </svg>
                                                        </div>
                                                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                                            <h3 class="text-lg font-medium text-white">This action is
                                                                undone. Are you sure you want
                                                                to delete {{ $listing->title }} </h3>

                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="bg-[#1f1f23] px-6 py-4 flex flex-row-reverse gap-3">
                                                    <button type="submit" :disabled="isSubmitting"
                                                        class="w-full inline-flex justify-center rounded-lg px-4 py-2 bg-emerald-600 text-white font-medium hover:bg-emerald-700 sm:w-auto sm:text-sm disabled:opacity-50 items-center">
                                                        <svg x-show="isSubmitting" class="animate-spin -ml-1 mr-2 h-4 w-4"
                                                            fill="none" viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                                stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor"
                                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                            </path>
                                                        </svg>
                                                        <span x-text="isSubmitting ? 'Deleting...' : 'Delete'"></span>
                                                    </button>
                                                    <button type="button" @click="deleteModal = false"
                                                        :disabled="isSubmitting"
                                                        class="w-full inline-flex justify-center rounded-lg border border-gray-600 px-4 py-2 text-gray-300 hover:bg-gray-800 sm:w-auto sm:text-sm">Cancel</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            No listings yet. <a href="{{ route('owner.listings.create') }}"
                                class="text-indigo-400 hover:underline">Create your first one</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $listings->links() }}</div>
@endsection
