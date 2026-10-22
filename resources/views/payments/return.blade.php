@extends('layouts.app')
@section('title', 'Payment Result')
@section('content')
    <div class="max-w-md mx-auto px-4 py-16 text-center text-gray-200">

        @if ($payment->status === 'success')
            <div class="w-16 h-16 rounded-full bg-emerald-500/15 flex items-center justify-center mx-auto mb-5">
                <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h1 class="text-xl font-bold text-white">Payment successful</h1>
            <p class="text-sm text-gray-400 mt-2">
                {{ number_format($payment->amount, 0) }} {{ $payment->currency }} paid for {{ $payment->plan->name }}.
                You're all set to post.
            </p>
            <a href="{{ route(auth()->user()->role . '.listings.create') }}"
                class="inline-block mt-6 px-5 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium transition">
                Continue to post your listing
            </a>
        @elseif ($payment->status === 'pending')
            <div class="w-16 h-16 rounded-full bg-amber-500/15 flex items-center justify-center mx-auto mb-5">
                <svg class="w-8 h-8 text-amber-400 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                    </circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-white">Confirming your payment…</h1>
            <p class="text-sm text-gray-400 mt-2">This usually takes a few seconds. Refresh if this doesn't update shortly.
            </p>
            <button onclick="location.reload()"
                class="inline-block mt-6 px-5 py-2.5 rounded-lg border border-gray-700 hover:bg-gray-800 text-sm font-medium transition">
                Refresh
            </button>
        @else
            <div class="w-16 h-16 rounded-full bg-red-500/15 flex items-center justify-center mx-auto mb-5">
                <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>
            <h1 class="text-xl font-bold text-white">Payment failed</h1>
            <p class="text-sm text-gray-400 mt-2">No charge was completed. You can try again.</p>
            <a href="{{ route('payments.pricing') }}"
                class="inline-block mt-6 px-5 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium transition">
                Back to pricing
            </a>
        @endif

    </div>
@endsection
