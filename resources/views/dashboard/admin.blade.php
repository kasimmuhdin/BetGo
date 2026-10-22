@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
    @php
        $statIcons = [
            'users' =>
                '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
            'building' =>
                '<path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/>',
            'calendar' =>
                '<path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="m9 16 2 2 4-4"/>',
            'wallet' =>
                '<path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/>',
        ];

        $statusColors = [
            'Active' => 'bg-emerald-50 text-emerald-600',
            'Pending' => 'bg-amber-50 text-amber-600',
            'Suspended' => 'bg-rose-50 text-rose-600',
            'Confirmed' => 'bg-emerald-50 text-emerald-600',
            'Cancelled' => 'bg-rose-50 text-rose-600',
        ];
    @endphp

    <!-- Page header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Admin Overview</h1>
            <p class="text-sm text-slate-500 mt-1">Platform-wide activity across all users, listings and bookings.</p>
        </div>
        <div class="flex gap-2">
            <a href="#"
                class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-slate-600 hover:bg-gray-50">Export
                report</a>
            <a href="#"
                class="px-4 py-2 rounded-lg bg-indigo-500 text-sm font-medium text-white hover:bg-indigo-600">Manage
                users</a>
        </div>
    </div>

    <!-- Stat cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @foreach ($stats as $stat)
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500">{{ $stat['label'] }}</p>
                    <p class="text-2xl font-bold text-slate-900 mt-1">{{ $stat['value'] }}</p>
                    <p class="text-xs mt-1 font-medium {{ $stat['up'] ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ $stat['up'] ? '↑' : '↓' }} {{ $stat['change'] }} <span class="text-slate-400 font-normal">this
                            month</span>
                    </p>
                </div>
                <div
                    class="w-12 h-12 rounded-lg bg-indigo-500/10 flex items-center justify-center text-indigo-500 flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        {!! $statIcons[$stat['icon']] !!}
                    </svg>
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent users -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-slate-900">Recent Users</h2>
                <a href="#" class="text-sm text-indigo-500 hover:underline">View all</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-400 border-b border-gray-100">
                            <th class="px-5 py-3 font-medium">Name</th>
                            <th class="px-5 py-3 font-medium">Role</th>
                            <th class="px-5 py-3 font-medium">Joined</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($recentUsers as $user)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3">
                                    <p class="font-medium text-slate-800">{{ $user['name'] }}</p>
                                    <p class="text-xs text-slate-400">{{ $user['email'] }}</p>
                                </td>
                                <td class="px-5 py-3 text-slate-600">{{ $user['role'] }}</td>
                                <td class="px-5 py-3 text-slate-500">{{ $user['joined'] }}</td>
                                <td class="px-5 py-3">
                                    <span
                                        class="px-2.5 py-1 rounded-full text-xs font-medium {{ $statusColors[$user['status']] ?? 'bg-gray-100 text-slate-600' }}">
                                        {{ $user['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pending listing approvals -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-slate-900">Pending Approvals</h2>
                <span
                    class="text-xs font-medium px-2 py-0.5 rounded-full bg-amber-50 text-amber-600">{{ count($pendingListings) }}
                    new</span>
            </div>
            <ul class="divide-y divide-gray-100">
                @foreach ($pendingListings as $listing)
                    <li class="px-5 py-3">
                        <p class="font-medium text-slate-800 text-sm">{{ $listing['title'] }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $listing['location'] }} · {{ $listing['price'] }}</p>
                        <div class="flex items-center justify-between mt-2">
                            <span class="text-xs text-slate-400">by {{ $listing['owner'] }} ·
                                {{ $listing['submitted'] }}</span>
                            <div class="flex gap-2">
                                <a href="#" class="text-xs font-medium text-emerald-600 hover:underline">Approve</a>
                                <a href="#" class="text-xs font-medium text-rose-500 hover:underline">Reject</a>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <!-- Recent bookings -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm mt-6">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-slate-900">Recent Bookings</h2>
            <a href="#" class="text-sm text-indigo-500 hover:underline">View all</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-400 border-b border-gray-100">
                        <th class="px-5 py-3 font-medium">Tenant</th>
                        <th class="px-5 py-3 font-medium">Listing</th>
                        <th class="px-5 py-3 font-medium">Date</th>
                        <th class="px-5 py-3 font-medium">Amount</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($recentBookings as $booking)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 font-medium text-slate-800">{{ $booking['tenant'] }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $booking['listing'] }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $booking['date'] }}</td>
                            <td class="px-5 py-3 font-medium text-slate-800">{{ $booking['amount'] }}</td>
                            <td class="px-5 py-3">
                                <span
                                    class="px-2.5 py-1 rounded-full text-xs font-medium {{ $statusColors[$booking['status']] ?? 'bg-gray-100 text-slate-600' }}">
                                    {{ $booking['status'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
