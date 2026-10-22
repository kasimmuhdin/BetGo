@extends('layouts.app')
@section('title', 'Payment: ' . $payment->tx_ref)
@section('content')
    <div x-data="{ approveModal: false, isSubmitting: false }" class="max-w-3xl mx-auto px-4 py-8 text-gray-200 font-sans">

        <a href="{{ route('admin.payments.index') }}"
            class="inline-flex items-center text-sm text-indigo-400 hover:text-indigo-300 font-medium mb-6 transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Payments
        </a>

        @if (session('success'))
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-xl text-sm mb-6">
                {{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-xl text-sm mb-6">
                {{ session('error') }}</div>
        @endif

        <div class="bg-[#18181b] border border-gray-800 rounded-xl p-6 shadow-lg space-y-6">

            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Transaction Reference</p>
                    <h1 class="text-lg font-mono font-bold text-white mt-1">{{ $payment->tx_ref }}</h1>
                </div>
                @php
                    $badge = match ($payment->status) {
                        'success' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                        'pending' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                        'failed' => 'bg-red-500/10 text-red-400 border-red-500/20',
                        default => 'bg-gray-800 text-gray-300 border-gray-700',
                    };
                @endphp
                <span
                    class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide border capitalize {{ $badge }}">{{ $payment->status }}</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 border-t border-gray-800 pt-5">
                <div>
                    <p class="text-xs text-gray-500">User</p>
                    <p class="text-sm font-medium text-gray-200 mt-1">{{ $payment->user->name ?? 'Unknown' }}</p>
                    <p class="text-xs text-gray-500">{{ $payment->user->email ?? '' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Plan</p>
                    <p class="text-sm font-medium text-gray-200 mt-1">{{ $payment->plan->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Provider</p>
                    <p class="text-sm font-medium text-gray-200 mt-1 capitalize">{{ $payment->provider }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Amount</p>
                    <p class="text-sm font-medium text-gray-200 mt-1">{{ number_format($payment->amount, 2) }}
                        {{ $payment->currency }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Created</p>
                    <p class="text-sm font-medium text-gray-200 mt-1">{{ $payment->created_at->format('M d, Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Paid At</p>
                    <p class="text-sm font-medium text-gray-200 mt-1">{{ $payment->paid_at?->format('M d, Y H:i') ?? '—' }}
                    </p>
                </div>
            </div>

            @if ($payment->listingGrant)
                <div class="border-t border-gray-800 pt-5">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-2">Linked Listing Grant</p>
                    <div
                        class="bg-[#161414] border border-gray-800 rounded-lg p-4 flex items-center justify-between text-sm">
                        <span class="text-gray-300">{{ $payment->listingGrant->plan->name ?? '—' }}</span>
                        <span class="text-gray-400">
                            {{ $payment->listingGrant->plan->isUnlimited() ? 'Unlimited' : $payment->listingGrant->remaining() . ' left' }}
                            @if ($payment->listingGrant->expires_at)
                                · expires {{ $payment->listingGrant->expires_at->format('M d, Y') }}
                            @endif
                        </span>
                    </div>
                </div>
            @endif

            @if ($payment->meta)
                <div class="border-t border-gray-800 pt-5">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-2">Provider Response (raw)</p>
                    <pre
                        class="bg-[#111113] border border-gray-800 rounded-lg p-4 text-xs text-gray-400 overflow-x-auto scrollbar-dark max-h-64">{{ json_encode($payment->meta, JSON_PRETTY_PRINT) }}</pre>
                </div>
            @endif

            @if ($payment->status !== 'success')
                <div class="border-t border-gray-800 pt-5">
                    <div class="bg-amber-500/10 border border-amber-500/20 rounded-lg p-4 mb-4">
                        <p class="text-sm text-amber-400">
                            <strong>Manual approval</strong> should only be used if you've confirmed on
                            {{ ucfirst($payment->provider) }}'s own dashboard that this charge actually succeeded — not as
                            a routine fix for ordinary failures.
                        </p>
                    </div>
                    <button @click="approveModal = true"
                        class="px-5 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium transition">
                        Manually Approve & Grant Credit
                    </button>
                </div>
            @endif
        </div>

        {{-- Approve confirmation modal --}}
        <div x-show="approveModal" style="display: none;" class="fixed z-50 inset-0 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="approveModal" x-transition.opacity class="fixed inset-0 bg-black/80 backdrop-blur-sm"
                    @click="approveModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div x-show="approveModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    class="inline-block align-bottom bg-[#18181b] border border-gray-700 rounded-xl text-left overflow-hidden shadow-2xl z-10 relative inline-block sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    <form action="{{ route('admin.payments.approve', $payment) }}" method="POST"
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
                                    <h3 class="text-lg font-medium text-white">Confirm Manual Approval</h3>
                                    <p class="text-sm text-gray-400 mt-2">
                                        This will mark <strong class="text-gray-200">{{ $payment->tx_ref }}</strong> as
                                        successful and immediately grant
                                        <strong class="text-gray-200">{{ $payment->user->name ?? 'this user' }}</strong>
                                        the
                                        <strong class="text-gray-200">{{ $payment->plan->name ?? 'plan' }}</strong> listing
                                        credit. This cannot be undone from this screen.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-[#1f1f23] px-6 py-4 flex flex-row-reverse gap-3">
                            <button type="submit" :disabled="isSubmitting"
                                class="w-full inline-flex justify-center rounded-lg px-4 py-2 bg-emerald-600 text-white font-medium hover:bg-emerald-700 sm:w-auto sm:text-sm disabled:opacity-50 items-center">
                                <svg x-show="isSubmitting" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
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
    </div>
@endsection
