@extends('layouts.app')

@section('title', 'Owner Dashboard')

@section('content')
    <style>
        .stat-card {
            background: linear-gradient(145deg, #232020 0%, #1e1b1b 100%);
            border: 1px solid #3a3a3d;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease, border-color 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            border-color: #4f46e5;
            /* Indigo highlight on hover */
        }

        .bgforall {
            background-color: #1e1b1b;
            border: 1px solid #3a3a3d;
        }

        table tbody tr:hover {
            background-color: #2a2727;
        }
    </style>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Welcome back, {{ auth()->user()->name }}</h1>
            <p class="text-sm text-gray-400 mt-1">Here is a summary of your property portfolio.</p>
        </div>
        @if (auth()->user()->email_verified_at && auth()->user()->status === 'approved')
            <a href="{{ route('owner.listings.create') }}"
                class="px-5 py-2.5 rounded-lg bg-indigo-600 text-sm font-semibold text-white hover:bg-indigo-700 shadow-lg shadow-indigo-600/20 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add New Listing
            </a>
        @endif

    </div>


    @if (!auth()->user()->email_verified_at)
        <div class="text-white  justify-center  ">
            Please first verify your email
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf

                <div>
                    <button class="text-blue-500 cursor-pointer">
                        {{ __('Resend Verification Email') }}
                    </button>
                </div>
            </form>
        </div>
    @else
        @if (auth()->user()->status !== 'approved')
            <div class="text-white">
                Please wait untill you approved by the admin
            </div>
        @endif

    @endif
    @if (auth()->user()->email_verified_at && auth()->user()->status === 'approved')

        <div>


            <!-- Real Stat Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
                <!-- Total Listings -->
                <div class="stat-card p-6 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">Total Listings</p>
                        <p class="text-3xl font-extrabold text-white mt-2">{{ $totalListings }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-indigo-500/10 flex items-center justify-center text-indigo-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Published Listings -->
                <div class="stat-card p-6 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">Published</p>
                        <p class="text-3xl font-extrabold text-white mt-2">{{ $publishedListings }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>

                <!-- Draft Listings -->
                <div class="stat-card p-6 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">In Draft</p>
                        <p class="text-3xl font-extrabold text-white mt-2">{{ $draftListings }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                            </path>
                        </svg>
                    </div>
                </div>

                <!-- Total Media -->
                <div class="stat-card p-6 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">Media Uploaded</p>
                        <p class="text-3xl font-extrabold text-white mt-2">{{ $totalMedia }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-cyan-500/10 flex items-center justify-center text-cyan-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Recent Properties Table -->
                <div class="lg:col-span-2 bgforall rounded-xl shadow-sm overflow-hidden flex flex-col">
                    <div class="flex items-center justify-between px-6 py-5 border-b border-[#3a3a3d] bg-[#1e1b1b]">
                        <h2 class="text-lg font-bold text-white">Recently Added Properties</h2>
                        <a href="{{ route('owner.listings.index') }}"
                            class="text-sm text-indigo-400 hover:text-indigo-300 font-medium transition">View all →</a>
                    </div>
                    <div class="overflow-x-auto flex-1">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-[#161414] text-xs uppercase font-semibold text-gray-400">
                                <tr>
                                    <th class="px-6 py-4">Property</th>
                                    <th class="px-6 py-4">Price</th>
                                    <th class="px-6 py-4">Type</th>
                                    <th class="px-6 py-4">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#3a3a3d]">
                                @forelse ($recentListings as $listing)
                                    <tr class="cursor-pointer transition-colors"
                                        onclick="window.location='{{ route('owner.listings.show', $listing) }}'">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                @if ($listing->cover_url)
                                                    <img src="{{ $listing->cover_url }}"
                                                        class="h-10 w-14 object-cover rounded-md border border-[#3a3a3d]">
                                                @else
                                                    <div
                                                        class="h-10 w-14 rounded-md bg-[#161414] border border-[#3a3a3d] flex items-center justify-center text-gray-500 text-[10px]">
                                                        No img</div>
                                                @endif
                                                <div>
                                                    <p class="font-medium text-gray-200">{{ $listing->title }}</p>
                                                    <p class="text-xs text-gray-500">{{ $listing->city ?? 'Location N/A' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 font-medium text-white">
                                            {{ number_format($listing->price, 2) }}
                                            {{ $listing->currency }}</td>
                                        <td class="px-6 py-4 text-gray-300 capitalize">{{ $listing->property_type }}</td>
                                        <td class="px-6 py-4">
                                            @php
                                                $colors = [
                                                    'draft' => 'bg-gray-800 text-gray-300 border-[#3a3a3d]',
                                                    'published' =>
                                                        'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                                    'pending' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                                ];
                                            @endphp
                                            <span
                                                class="px-2.5 py-1 rounded-md text-xs font-semibold border {{ $colors[$listing->status] ?? 'bg-gray-800 text-gray-300 border-[#3a3a3d]' }}">
                                                {{ ucfirst($listing->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                            You haven't added any properties yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Messages / Inquiries -->
                <div class="bgforall rounded-xl shadow-sm flex flex-col">
                    <div class="flex items-center justify-between px-6 py-5 border-b border-[#3a3a3d] bg-[#1e1b1b]">
                        <h2 class="text-lg font-bold text-white">Recent Inquiries</h2>
                        <a href="{{ route('messages.compose') }}"
                            class="text-sm text-indigo-400 hover:text-indigo-300 font-medium transition">Contact support</a>
                        <a href="{{ route('messages.index') }}"
                            class="text-sm text-indigo-400 hover:text-indigo-300 font-medium transition ml-3">Inbox</a>
                    </div>
                    <div class="p-2 flex-1">
                        <ul class="divide-y divide-[#3a3a3d]">
                            @forelse ($recentConversations as $conversation)
                                @php
                                    $other = $conversation->otherUser(auth()->id());
                                @endphp
                                <li>
                                    <a href="{{ route('messages.show', $conversation) }}"
                                        class="p-4 flex gap-4 hover:bg-[#2a2727] rounded-lg transition-colors group">
                                        <div
                                            class="w-10 h-10 rounded-full bg-indigo-500/20 flex items-center justify-center text-indigo-400 font-bold shadow-inner flex-shrink-0">
                                            {{ strtoupper(substr($other->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center justify-between gap-2 mb-1">
                                                <p
                                                    class="font-semibold text-gray-200 text-sm truncate group-hover:text-indigo-400 transition-colors">
                                                    {{ $other->name ?? 'Unknown User' }}</p>
                                                <span
                                                    class="text-[11px] text-gray-500 whitespace-nowrap">{{ $conversation->last_message_at?->diffForHumans() ?? $conversation->updated_at->diffForHumans() }}</span>
                                            </div>
                                            @if ($conversation->property)
                                                <p class="text-[11px] text-indigo-400 truncate mb-0.5">
                                                    {{ $conversation->property->title }}</p>
                                            @endif
                                            <p class="text-xs text-gray-400 truncate">
                                                {{ $conversation->latestMessage?->body ?? 'No messages yet' }}</p>
                                        </div>
                                    </a>
                                </li>
                            @empty
                                <div class="py-10 text-center flex flex-col items-center">
                                    <svg class="w-10 h-10 text-[#3a3a3d] mb-3" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                                        </path>
                                    </svg>
                                    <p class="text-sm text-gray-500">No new messages yet.</p>
                                </div>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
