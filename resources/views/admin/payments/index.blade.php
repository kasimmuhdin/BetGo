@extends('layouts.app')
@section('title', 'Payments')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-6 text-white space-y-6">

    <div>
        <h1 class="text-2xl font-bold tracking-tight">Payments</h1>
        <p class="text-sm text-gray-400 mt-1">All Chapa and Stripe transactions across the platform.</p>
    </div>

    @if (session('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition.opacity.duration.500ms
            class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition.opacity.duration.500ms
            class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-xl text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
        <div class="bg-gradient-to-br from-[#232020] to-[#1e1b1b] border border-[#3a3a3d] rounded-xl p-5">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Revenue (ETB)</p>
            <p class="text-xl font-extrabold text-emerald-400 mt-2">{{ number_format($stats['total_revenue_etb'], 0) }}</p>
        </div>
        <div class="bg-gradient-to-br from-[#232020] to-[#1e1b1b] border border-[#3a3a3d] rounded-xl p-5">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Revenue (USD)</p>
            <p class="text-xl font-extrabold text-indigo-400 mt-2">${{ number_format($stats['total_revenue_usd'], 2) }}</p>
        </div>
        <div class="bg-gradient-to-br from-[#232020] to-[#1e1b1b] border border-[#3a3a3d] rounded-xl p-5">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Successful</p>
            <p class="text-xl font-extrabold text-white mt-2">{{ $stats['success_count'] }}</p>
        </div>
        <div class="bg-gradient-to-br from-[#232020] to-[#1e1b1b] border border-[#3a3a3d] rounded-xl p-5">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Pending</p>
            <p class="text-xl font-extrabold text-amber-400 mt-2">{{ $stats['pending_count'] }}</p>
        </div>
        <div class="bg-gradient-to-br from-[#232020] to-[#1e1b1b] border border-[#3a3a3d] rounded-xl p-5">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Failed</p>
            <p class="text-xl font-extrabold text-red-400 mt-2">{{ $stats['failed_count'] }}</p>
        </div>
        <div class="bg-gradient-to-br from-[#232020] to-[#1e1b1b] border border-[#3a3a3d] rounded-xl p-5">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Paid Today</p>
            <p class="text-xl font-extrabold text-white mt-2">{{ $stats['today_count'] }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="bg-[#1e1b1b] border border-[#3a3a3d] rounded-xl p-4 flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"></path>
            </svg>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by tx_ref, name, or email…"
                class="w-full pl-9 pr-3 py-2.5 rounded-lg bg-[#161414] border border-[#3a3a3d] text-white text-sm placeholder:text-gray-500 focus:outline-none focus:border-indigo-500">
        </div>

        <select name="status" class="rounded-lg px-3 py-2.5 bg-[#161414] border border-[#3a3a3d] text-sm text-white focus:outline-none focus:border-indigo-500">
            <option value="" class="bg-[#1e1b1b]">All Statuses</option>
            <option value="success" class="bg-[#1e1b1b]" @selected(request('status') === 'success')>Success</option>
            <option value="pending" class="bg-[#1e1b1b]" @selected(request('status') === 'pending')>Pending</option>
            <option value="failed" class="bg-[#1e1b1b]" @selected(request('status') === 'failed')>Failed</option>
        </select>

        <select name="provider" class="rounded-lg px-3 py-2.5 bg-[#161414] border border-[#3a3a3d] text-sm text-white focus:outline-none focus:border-indigo-500">
            <option value="" class="bg-[#1e1b1b]">All Providers</option>
            <option value="chapa" class="bg-[#1e1b1b]" @selected(request('provider') === 'chapa')>Chapa</option>
            <option value="stripe" class="bg-[#1e1b1b]" @selected(request('provider') === 'stripe')>Stripe</option>
        </select>

        <button type="submit" class="px-5 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-sm font-medium transition">Filter</button>

        @if (request()->hasAny(['q', 'status', 'provider']))
            <a href="{{ route('admin.payments.index') }}" class="px-4 py-2.5 rounded-lg border border-[#3a3a3d] text-sm text-gray-300 hover:bg-[#2a2727] transition text-center">Clear</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="bg-[#1e1b1b] border border-[#3a3a3d] rounded-xl overflow-hidden">
        <div class="overflow-x-auto scrollbar-dark">
            <table class="w-full text-sm text-left">
                <thead class="bg-[#161414] text-xs uppercase font-semibold text-gray-400">
                    <tr>
                        <th class="px-6 py-4">Transaction</th>
                        <th class="px-6 py-4">User</th>
                        <th class="px-6 py-4">Plan</th>
                        <th class="px-6 py-4">Amount</th>
                        <th class="px-6 py-4">Provider</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Date</th>
                        <th class="px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#3a3a3d]">
                    @forelse ($payments as $payment)
                        @php
                            $badge = match ($payment->status) {
                                'success' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                'pending' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                'failed' => 'bg-red-500/10 text-red-400 border-red-500/20',
                                default => 'bg-gray-800 text-gray-300 border-[#3a3a3d]',
                            };
                        @endphp
                        <tr class="hover:bg-[#2a2727] transition-colors">
                            <td class="px-6 py-4">
                                <p class="font-mono text-xs text-gray-300">{{ $payment->tx_ref }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-200">{{ $payment->user->name ?? 'Unknown' }}</p>
                                <p class="text-xs text-gray-500">{{ $payment->user->email ?? '—' }}</p>
                            </td>
                            <td class="px-6 py-4 text-gray-300">{{ $payment->plan->name ?? '—' }}</td>
                            <td class="px-6 py-4 font-medium text-white whitespace-nowrap">
                                {{ number_format($payment->amount, 2) }} <span class="text-gray-500 text-xs">{{ $payment->currency }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="capitalize text-gray-300">{{ $payment->provider }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-md text-xs font-semibold border capitalize {{ $badge }}">{{ $payment->status }}</span>
                            </td>
                            <td class="px-6 py-4 text-gray-500 text-xs whitespace-nowrap">{{ $payment->created_at->diffForHumans() }}</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.payments.show', $payment) }}"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-indigo-600/10 border border-indigo-600/30 text-indigo-400 hover:bg-indigo-600 hover:text-white text-xs font-semibold transition">
                                    View
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center text-gray-500">No payments match these filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-2">{{ $payments->links('vendor.pagination.custom-dark') }}</div>
</div>
@endsection