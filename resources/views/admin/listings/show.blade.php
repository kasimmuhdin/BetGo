@extends('layouts.app')
@section('title', 'Listing: ' . $listing->title)
@section('content')
    <div x-data="{
        approveModal: false,
        rejectModal: false,
        unpublishModal: false,
        isSubmitting: false,
        lightbox: false,
        activeIndex: 0,
        images: {{ $listing->media->map(fn($m) => ['url' => $m->url, 'cover' => (bool) $m->pivot->is_cover])->values()->toJson() }},
        next() { this.activeIndex = (this.activeIndex + 1) % this.images.length },
        prev() { this.activeIndex = (this.activeIndex - 1 + this.images.length) % this.images.length },
    }" @keydown.escape.window="lightbox = false" @keydown.arrow-right.window="if (lightbox) next()"
        @keydown.arrow-left.window="if (lightbox) prev()" class="max-w-7xl mx-auto px-4 py-8 text-gray-200 font-sans">

        @php
            $badge = match ($listing->status) {
                'published' => ['bg-emerald-500/10 text-emerald-400 border-emerald-500/20', 'Published'],
                'pending' => ['bg-amber-500/10 text-amber-400 border-amber-500/20', 'Pending Review'],
                'rejected' => ['bg-red-500/10 text-red-400 border-red-500/20', 'Rejected'],
                'archived' => ['bg-slate-500/10 text-slate-400 border-slate-500/20', 'Archived'],
                default => ['bg-gray-800 text-gray-300 border-[#3a3a3d]', 'Draft'],
            };
        @endphp

        <div class="flex items-center justify-between mb-6">
            <a href="{{ url()->previous() === url()->current() ? route('admin.listings.all') : url()->previous() }}"
                class="inline-flex items-center text-sm text-indigo-400 hover:text-indigo-300 font-medium transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
                Back
            </a>

            @if ($listing->status === 'pending' && $listing->submitted_at)
                @php $waitHours = now()->diffInHours($listing->submitted_at); @endphp
                <span
                    class="px-3 py-1.5 rounded-full text-xs font-semibold border {{ $waitHours >= 48 ? 'text-red-400 bg-red-500/10 border-red-500/20' : ($waitHours >= 24 ? 'text-amber-400 bg-amber-500/10 border-amber-500/20' : 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20') }}">
                    Waiting {{ $listing->submitted_at->diffForHumans(null, true) }}
                </span>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">

                <div class="bg-[#18181b] border border-gray-800 rounded-xl p-6 shadow-lg">
                    <div class="flex items-center justify-between mb-4 gap-3 flex-wrap">
                        <h1 class="text-2xl font-bold text-white">{{ $listing->title }}</h1>
                        <span
                            class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide border {{ $badge[0] }}">{{ $badge[1] }}</span>
                    </div>

                    @if ($listing->media->isNotEmpty())
                        <div class="space-y-3">
                            <button type="button" @click="lightbox = true"
                                class="relative aspect-video w-full rounded-lg overflow-hidden border border-gray-800 block group focus:outline-none">
                                <img :src="images[activeIndex].url"
                                    class="w-full h-full object-cover transition group-hover:brightness-90">
                                <span x-show="images[activeIndex].cover"
                                    class="absolute top-3 left-3 text-[11px] bg-amber-500 text-black font-semibold px-2 py-1 rounded shadow">★
                                    Cover Photo</span>
                                <span
                                    class="absolute bottom-3 right-3 bg-black/70 text-white text-xs px-2.5 py-1 rounded-md font-medium flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7">
                                        </path>
                                    </svg>
                                    View full size
                                </span>
                                <span
                                    class="absolute bottom-3 left-3 bg-black/70 text-white text-xs px-2 py-1 rounded-md font-medium"
                                    x-text="(activeIndex + 1) + ' / ' + images.length"></span>
                            </button>

                            @if ($listing->media->count() > 1)
                                <div class="grid grid-cols-4 md:grid-cols-6 gap-3">
                                    <template x-for="(img, i) in images" :key="i">
                                        <button type="button" @click="activeIndex = i"
                                            class="relative aspect-square w-full rounded-md overflow-hidden border-2 transition block"
                                            :class="i === activeIndex ? 'border-indigo-500 ring-2 ring-indigo-500/40' :
                                                'border-gray-800 hover:border-gray-600 opacity-80 hover:opacity-100'">
                                            <img :src="img.url" class="w-full h-full object-cover">
                                            <span x-show="img.cover"
                                                class="absolute top-1 left-1 text-[9px] bg-amber-500 text-black font-bold px-1 py-0.5 rounded">★</span>
                                        </button>
                                    </template>
                                </div>
                            @endif
                        </div>
                    @else
                        <div
                            class="aspect-video w-full bg-[#1f1f23] rounded-lg border border-gray-800 border-dashed flex items-center justify-center">
                            <span class="text-gray-500">No media attached</span>
                        </div>
                    @endif

                    <div class="mt-8">
                        <h3 class="text-lg font-semibold text-white mb-3">Description</h3>
                        <p class="text-gray-400 whitespace-pre-line leading-relaxed text-sm">
                            {{ $listing->description ?: 'No description provided.' }}</p>
                    </div>
                </div>

                <div class="bg-[#18181b] border border-gray-800 rounded-xl p-6 shadow-lg">
                    <h3
                        class="text-sm font-semibold text-gray-200 uppercase tracking-wider mb-4 border-b border-gray-800 pb-2">
                        Location</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                        <div>
                            <p class="text-xs text-gray-500">City</p>
                            <p class="font-medium text-gray-200">{{ $listing->city ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Sub-city</p>
                            <p class="font-medium text-gray-200">{{ $listing->sub_city ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Woreda</p>
                            <p class="font-medium text-gray-200">{{ $listing->woreda ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Neighborhood</p>
                            <p class="font-medium text-gray-200">{{ $listing->neighborhood ?? '—' }}</p>
                        </div>
                    </div>
                    @if ($listing->address)
                        <p class="text-xs text-gray-400 mt-4 pt-4 border-t border-gray-800">{{ $listing->address }}</p>
                    @endif
                    @if ($listing->latitude && $listing->longitude)
                        <a href="https://www.openstreetmap.org/?mlat={{ $listing->latitude }}&mlon={{ $listing->longitude }}#map=16/{{ $listing->latitude }}/{{ $listing->longitude }}"
                            target="_blank" rel="noopener"
                            class="inline-flex items-center gap-1.5 text-xs text-indigo-400 hover:text-indigo-300 mt-3">
                            View pinned location on map
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-[#18181b] border border-gray-800 rounded-xl p-6 shadow-lg">
                    <h3
                        class="text-sm font-semibold text-gray-200 uppercase tracking-wider mb-4 border-b border-gray-800 pb-2">
                        Overview</h3>
                    <div class="space-y-4">
                        <div>
                            <p class="text-xs text-gray-500">Price</p>
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="text-lg font-bold text-white">{{ number_format($listing->price, 2) }} <span
                                        class="text-sm text-gray-400">{{ $listing->currency }}</span></p>
                                @if ($listing->price_negotiable)
                                    <span
                                        class="px-2 py-0.5 rounded-md text-[11px] bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">Negotiable</span>
                                @endif
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 border-t border-gray-800 pt-4">
                            <div>
                                <p class="text-xs text-gray-500">Type</p>
                                <p class="text-sm font-medium text-gray-200 capitalize">{{ $listing->property_type }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Transaction</p>
                                <p class="text-sm font-medium text-gray-200 capitalize">{{ $listing->listing_type }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-4 border-t border-gray-800 pt-4">
                            <div>
                                <p class="text-xs text-gray-500">Beds</p>
                                <p class="text-sm font-medium text-gray-200">{{ $listing->bedrooms ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Baths</p>
                                <p class="text-sm font-medium text-gray-200">{{ $listing->bathrooms ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Parking</p>
                                <p class="text-sm font-medium text-gray-200">{{ $listing->parking_spaces ?? '—' }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 border-t border-gray-800 pt-4">
                            <div>
                                <p class="text-xs text-gray-500">Area</p>
                                <p class="text-sm font-medium text-gray-200">{{ $listing->area ?? '—' }} <span
                                        class="text-gray-500">{{ $listing->area_unit }}</span></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Furnishing</p>
                                <p class="text-sm font-medium text-gray-200 capitalize">
                                    {{ str_replace('_', ' ', $listing->furnishing_status) ?: '—' }}</p>
                            </div>
                        </div>
                        <div class="border-t border-gray-800 pt-4">
                            <p class="text-xs text-gray-500">Submitter</p>
                            <div class="flex items-center gap-2 mt-1">
                                <div
                                    class="w-7 h-7 rounded-full bg-indigo-500/20 flex items-center justify-center text-indigo-400 text-xs font-bold shrink-0">
                                    {{ strtoupper(substr($listing->owner->name ?? 'U', 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-200 truncate">
                                        {{ $listing->owner->name ?? 'Unknown' }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ $listing->owner->email ?? '—' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="border-t border-gray-800 pt-4 text-xs text-gray-500 space-y-1">
                            <p>Created {{ $listing->created_at->format('M d, Y H:i') }}</p>
                            @if ($listing->submitted_at)
                                <p>Submitted {{ $listing->submitted_at->format('M d, Y H:i') }}</p>
                            @endif
                            @if ($listing->reviewed_at)
                                <p>Reviewed {{ $listing->reviewed_at->format('M d, Y H:i') }} by
                                    {{ $listing->reviewer->name ?? '—' }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Status-specific action card --}}
                <div class="bg-[#18181b] border border-gray-800 rounded-xl p-6 shadow-lg">
                    <h3 class="text-sm font-semibold text-gray-200 uppercase tracking-wider mb-4">Moderation</h3>

                    @if ($listing->status === 'pending')
                        <div class="flex flex-col gap-3">
                            <button @click="approveModal = true"
                                class="w-full flex justify-center items-center px-4 py-3 rounded-lg text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition-colors font-medium">Approve
                                & Publish</button>
                            <button @click="rejectModal = true"
                                class="w-full flex justify-center items-center px-4 py-3 border border-red-500/30 text-sm font-medium rounded-lg text-red-400 bg-red-500/10 hover:bg-red-500 hover:text-white transition-colors">Reject
                                Listing</button>
                        </div>
                        <p class="text-[11px] text-gray-600 mt-3">Approving marks this listing as verified and makes it
                            live immediately. Rejecting notifies the owner with your reason.</p>
                    @elseif ($listing->status === 'published')
                        <p class="text-sm text-gray-400 mb-4">This listing is live and visible to the public.</p>
                        <button @click="unpublishModal = true"
                            class="w-full flex justify-center items-center px-4 py-3 border border-amber-500/30 text-sm font-medium rounded-lg text-amber-400 bg-amber-500/10 hover:bg-amber-500 hover:text-black transition-colors">Unpublish
                            Listing</button>
                    @elseif ($listing->status === 'rejected')
                        <div class="bg-red-500/10 border border-red-500/20 rounded-lg p-4">
                            <p class="text-xs text-red-400 font-semibold uppercase tracking-wide mb-1">Rejection Reason</p>
                            <p class="text-sm text-gray-300">{{ $listing->rejection_reason }}</p>
                        </div>
                        <p class="text-xs text-gray-500 mt-3">The owner has been notified and can edit and resubmit this
                            listing.</p>
                    @elseif ($listing->status === 'archived')
                        <p class="text-sm text-gray-400">This listing was unpublished by an admin and is no longer visible
                            to the public.</p>
                    @else
                        <p class="text-sm text-gray-400">This is a draft — the owner hasn't submitted it for review yet.
                        </p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Lightbox --}}
        <div x-show="lightbox" x-cloak style="display: none;"
            class="fixed inset-0 z-[60] bg-black/95 flex items-center justify-center" x-transition.opacity>
            <button type="button" @click="lightbox = false"
                class="absolute top-5 right-5 text-gray-300 hover:text-white p-2 rounded-full hover:bg-white/10 transition z-10">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
            <span class="absolute top-6 left-6 text-gray-300 text-sm font-medium bg-black/50 px-3 py-1.5 rounded-full"
                x-text="(activeIndex + 1) + ' / ' + images.length"></span>
            <button type="button" @click.stop="prev()" x-show="images.length > 1"
                class="absolute left-3 sm:left-6 text-gray-300 hover:text-white p-3 rounded-full hover:bg-white/10 transition">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
            <img :src="images[activeIndex]?.url" @click.stop
                class="max-h-[85vh] max-w-[88vw] object-contain rounded-lg shadow-2xl select-none">
            <button type="button" @click.stop="next()" x-show="images.length > 1"
                class="absolute right-3 sm:right-6 text-gray-300 hover:text-white p-3 rounded-full hover:bg-white/10 transition">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
            <div class="absolute inset-0 -z-10" @click="lightbox = false"></div>
            <div x-show="images.length > 1"
                class="absolute bottom-5 left-1/2 -translate-x-1/2 flex gap-2 max-w-[90vw] overflow-x-auto px-4">
                <template x-for="(img, i) in images" :key="'fs-' + i">
                    <button type="button" @click.stop="activeIndex = i"
                        class="w-14 h-14 shrink-0 rounded-md overflow-hidden border-2 transition"
                        :class="i === activeIndex ? 'border-indigo-500' : 'border-transparent opacity-60 hover:opacity-100'">
                        <img :src="img.url" class="w-full h-full object-cover">
                    </button>
                </template>
            </div>
        </div>

        {{-- Approve Modal --}}
        <div x-show="approveModal" style="display: none;" class="fixed z-50 inset-0 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="approveModal" x-transition.opacity class="fixed inset-0 bg-black/80 backdrop-blur-sm"
                    @click="approveModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div x-show="approveModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    class="inline-block align-bottom bg-[#18181b] border border-gray-700 rounded-xl text-left overflow-hidden shadow-2xl relative z-10 inline-block sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    <form action="{{ route('admin.listings.approve', $listing) }}" method="POST"
                        @submit="isSubmitting = true">
                        @csrf
                        <div class="px-6 pt-6 pb-4">
                            <div class="sm:flex sm:items-start">
                                <div
                                    class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-emerald-500/20 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="h-6 w-6 text-emerald-500" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg font-medium text-white">Approve Listing</h3>
                                    <p class="text-sm text-gray-400 mt-2">Approve <strong
                                            class="text-gray-200">{{ $listing->title }}</strong>? It will immediately be
                                        visible to the public and marked verified.</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-[#1f1f23] px-6 py-4 flex flex-row-reverse gap-3">
                            <button type="submit" :disabled="isSubmitting"
                                class="w-full inline-flex justify-center rounded-lg px-4 py-2 bg-emerald-600 text-white font-medium hover:bg-emerald-700 sm:w-auto sm:text-sm disabled:opacity-50 items-center">
                                <svg x-show="isSubmitting" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <span x-text="isSubmitting ? 'Approving...' : 'Confirm Approval'"></span>
                            </button>
                            <button type="button" @click="approveModal = false" :disabled="isSubmitting"
                                class="w-full inline-flex justify-center rounded-lg border border-gray-600 px-4 py-2 text-gray-300 hover:bg-gray-800 sm:w-auto sm:text-sm">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Reject Modal --}}
        <div x-show="rejectModal" style="display: none;" class="fixed z-50 inset-0 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="rejectModal" x-transition.opacity class="fixed inset-0 bg-black/80 backdrop-blur-sm"
                    @click="rejectModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div x-show="rejectModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-data="{ reason: '' }"
                    class="inline-block align-bottom bg-[#18181b] border border-gray-700 rounded-xl text-left overflow-hidden shadow-2xl relative z-10 inline-block sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    <form action="{{ route('admin.listings.reject', $listing) }}" method="POST"
                        @submit="isSubmitting = true">
                        @csrf
                        <div class="px-6 pt-6 pb-4">
                            <div class="sm:flex sm:items-start">
                                <div
                                    class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-500/20 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg font-medium text-white">Reject Listing</h3>
                                    <p class="text-sm text-gray-400 mb-3 mt-2">Explain what needs to change — the owner
                                        will see this exact message.</p>
                                    <textarea x-model="reason" name="rejection_reason" rows="3" required minlength="10" maxlength="1000"
                                        placeholder="e.g., The images are blurry, please upload better quality photos."
                                        class="w-full rounded-lg bg-[#111113] border border-gray-700 text-gray-200 p-3 text-sm focus:border-red-500 focus:ring-1 focus:ring-red-500 outline-none transition-colors shadow-inner"></textarea>
                                    <div class="flex items-center justify-between mt-1.5">
                                        <span class="text-xs"
                                            :class="reason.trim().length > 0 && reason.trim().length < 10 ? 'text-red-400' :
                                                'text-gray-600'"><span
                                                x-show="reason.trim().length < 10">Minimum 10 characters</span></span>
                                        <span class="text-xs text-gray-600" x-text="reason.length + ' / 1000'"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-[#1f1f23] px-6 py-4 flex flex-row-reverse gap-3">
                            <button type="submit" :disabled="isSubmitting || reason.trim().length < 10"
                                class="w-full inline-flex justify-center rounded-lg px-4 py-2 bg-red-600 text-white font-medium hover:bg-red-700 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed items-center">
                                <svg x-show="isSubmitting" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <span x-text="isSubmitting ? 'Processing...' : 'Confirm Rejection'"></span>
                            </button>
                            <button type="button" @click="rejectModal = false" :disabled="isSubmitting"
                                class="w-full inline-flex justify-center rounded-lg border border-gray-600 px-4 py-2 text-gray-300 hover:bg-gray-800 sm:w-auto sm:text-sm">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Unpublish Modal --}}
        <div x-show="unpublishModal" style="display: none;" class="fixed z-50 inset-0 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="unpublishModal" x-transition.opacity class="fixed inset-0 bg-black/80 backdrop-blur-sm"
                    @click="unpublishModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div x-show="unpublishModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    class="inline-block align-bottom bg-[#18181b] border border-gray-700 rounded-xl text-left overflow-hidden shadow-2xl relative z-10 inline-block sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    <form action="{{ route('admin.listings.unpublish', $listing) }}" method="POST"
                        @submit="isSubmitting = true">
                        @csrf
                        <div class="px-6 pt-6 pb-4">
                            <div class="sm:flex sm:items-start">
                                <div
                                    class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-amber-500/20 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="h-6 w-6 text-amber-500" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.99 9.99 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg font-medium text-white">Unpublish Listing</h3>
                                    <p class="text-sm text-gray-400 mt-2">This removes <strong
                                            class="text-gray-200">{{ $listing->title }}</strong> from public view
                                        immediately. The owner is not notified automatically.</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-[#1f1f23] px-6 py-4 flex flex-row-reverse gap-3">
                            <button type="submit" :disabled="isSubmitting"
                                class="w-full inline-flex justify-center rounded-lg px-4 py-2 bg-amber-500 text-black font-medium hover:bg-amber-600 sm:w-auto sm:text-sm disabled:opacity-50 items-center">
                                <svg x-show="isSubmitting" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <span x-text="isSubmitting ? 'Processing...' : 'Confirm Unpublish'"></span>
                            </button>
                            <button type="button" @click="unpublishModal = false" :disabled="isSubmitting"
                                class="w-full inline-flex justify-center rounded-lg border border-gray-600 px-4 py-2 text-gray-300 hover:bg-gray-800 sm:w-auto sm:text-sm">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
