@extends('layouts.app')

@section('title', 'Agent Dashboard')

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
            <h1 class="text-2xl font-bold text-white tracking-tight">Agent Workspace</h1>
            <p class="text-sm text-gray-400 mt-1">Welcome back, {{ auth()->user()->name }}. Here are your leads and
                assignments.</p>
        </div>
        <a href="{{ route('agent.listings.create') ?? '#' }}"
            class="px-5 py-2.5 rounded-lg bg-indigo-600 text-sm font-semibold text-white hover:bg-indigo-700 shadow-lg shadow-indigo-600/20 transition-all flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add New Listing
        </a>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <div class="stat-card p-6 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">Assigned</p>
                <p class="text-3xl font-extrabold text-white mt-2">{{ $totalListings }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-500/10 flex items-center justify-center text-indigo-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                    </path>
                </svg>
            </div>
        </div>

        <div class="stat-card p-6 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">Active</p>
                <p class="text-3xl font-extrabold text-white mt-2">{{ $activeListings }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z">
                    </path>
                </svg>
            </div>
        </div>

        <div class="stat-card p-6 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">Viewings</p>
                <p class="text-3xl font-extrabold text-white mt-2">{{ $scheduledViewings }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z">
                    </path>
                </svg>
            </div>
        </div>

        <div class="stat-card p-6 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">Commission</p>
                <p class="text-3xl font-extrabold text-white mt-2">${{ number_format($commissionEarned) }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-cyan-500/10 flex items-center justify-center text-cyan-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                    </path>
                </svg>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Assigned Properties Table -->
        <div class="lg:col-span-2 bgforall rounded-xl shadow-sm overflow-hidden flex flex-col">
            <div class="flex items-center justify-between px-6 py-5 border-b border-[#3a3a3d] bg-[#1e1b1b]">
                <h2 class="text-lg font-bold text-white">My Assigned Properties</h2>
                <a href="{{ route('agent.listings.index') ?? '#' }}"
                    class="text-sm text-indigo-400 hover:text-indigo-300 font-medium transition">View all →</a>
            </div>
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-sm text-left">
                    <thead class="bg-[#161414] text-xs uppercase font-semibold text-gray-400">
                        <tr>
                            <th class="px-6 py-4">Property</th>
                            <th class="px-6 py-4">Price</th>
                            <th class="px-6 py-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#3a3a3d]">
                        @forelse ($assignedListings as $listing)
                            <tr class="cursor-pointer transition-colors"
                                onclick="window.location='{{ route('agent.listings.show', $listing) ?? '#' }}'">
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
                                            <p class="text-xs text-gray-500">{{ $listing->city ?? 'Location N/A' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-medium text-white">{{ number_format($listing->price, 2) }}
                                    {{ $listing->currency }}</td>
                                <td class="px-6 py-4">
                                    @php
                                        $colors = [
                                            'draft' => 'bg-gray-800 text-gray-300 border-[#3a3a3d]',
                                            'published' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
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
                                <td colspan="3" class="px-6 py-12 text-center text-gray-500">
                                    You have no assigned properties yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Leads -->
        <div class="bgforall rounded-xl shadow-sm flex flex-col">
            <div class="flex items-center justify-between px-6 py-5 border-b border-[#3a3a3d] bg-[#1e1b1b]">
                <h2 class="text-lg font-bold text-white">Recent Leads</h2>
                <a href="#" class="text-sm text-indigo-400 hover:text-indigo-300 font-medium transition">Open
                    Inbox</a>
            </div>
            <div class="p-2 flex-1">
                <ul class="divide-y divide-[#3a3a3d]">
                    @forelse ($recentLeads as $lead)
                        <li>
                            <a href="#" class="p-4 flex gap-4 hover:bg-[#2a2727] rounded-lg transition-colors group">
                                <div
                                    class="w-10 h-10 rounded-full bg-indigo-500/20 flex items-center justify-center text-indigo-400 font-bold shadow-inner flex-shrink-0">
                                    {{ strtoupper(substr($lead->initiator->name ?? 'U', 0, 1)) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-2 mb-1">
                                        <p
                                            class="font-semibold text-gray-200 text-sm truncate group-hover:text-indigo-400 transition-colors">
                                            {{ $lead->initiator->name ?? 'Unknown User' }}</p>
                                        <span
                                            class="text-[11px] text-gray-500 whitespace-nowrap">{{ $lead->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-xs text-gray-400 truncate">Follow up on property inquiry...</p>
                                </div>
                            </a>
                        </li>
                    @empty
                        <div class="py-10 text-center flex flex-col items-center">
                            <svg class="w-10 h-10 text-[#3a3a3d] mb-3" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                </path>
                            </svg>
                            <p class="text-sm text-gray-500">No new leads to display.</p>
                        </div>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
