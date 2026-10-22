@extends('layouts.app')
@section('title', 'Pricing')

@section('content')
    @php $featuredKey = config('betgo.featured_plan'); @endphp

    <div x-data="pricingPage(@js(['preselected' => $preselected]))" @keydown.escape.window="openPlan = null" @pageshow.window="reset()">

        <div class="mx-auto max-w-6xl px-4 py-8 text-gray-200">

            <div class="mb-8 text-center">
                <h1 class="text-2xl font-bold text-white">Choose how you post</h1>
                <p class="mt-2 text-sm text-gray-400">Pay per listing, or subscribe monthly for better value.</p>
            </div>

            @if (request()->boolean('cancelled'))
                <div role="status"
                    class="mx-auto mb-6 max-w-2xl rounded-xl border border-sky-500/30 bg-sky-500/10 px-4 py-3 text-center text-sm text-sky-300">
                    Payment cancelled. You have not been charged.
                </div>
            @endif
            @if (session('warning'))
                <div role="status"
                    class="mx-auto mb-6 max-w-2xl rounded-xl border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-center text-sm text-amber-400">
                    {{ session('warning') }}
                </div>
            @endif
            @if (session('error'))
                <div role="alert"
                    class="mx-auto mb-6 max-w-2xl rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-center text-sm text-red-400">
                    {{ session('error') }}
                </div>
            @endif

            @if ($activeSubscriptions->isNotEmpty())
                <div class="mx-auto mb-8 max-w-2xl rounded-xl border border-gray-800 bg-[#18181b] p-5">
                    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-300">Your active plans</h3>
                    <div class="space-y-2">
                        @foreach ($activeSubscriptions as $sub)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-300">{{ $sub->plan->name }}</span>
                                <span class="text-gray-400">
                                    {{ $sub->plan->isUnlimited() ? 'Unlimited' : $sub->remaining() . ' listings left' }}
                                    @if ($sub->expires_at)
                                        · expires {{ $sub->expires_at->format('M d, Y') }}
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($plans as $plan)
                    @php
                        $featured = $plan->key === $featuredKey;
                        $colors = match ($plan->key) {
                            'single' => 'border-gray-700',
                            'basic' => 'border-indigo-600/40',
                            'pro' => 'border-purple-500',
                            'unlimited' => 'border-emerald-500/40',
                            default => 'border-gray-700',
                        };
                    @endphp

                    <div
                        class="relative flex flex-col rounded-xl border-2 bg-[#18181b] p-6 py-11 {{ $colors }} {{ $featured ? 'scale-[1.02] shadow-xl shadow-purple-500/10' : '' }}">
                        @if ($featured)
                            <span
                                class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-purple-500 px-3 py-1 text-[10px] font-bold uppercase text-white">Most
                                popular</span>
                        @endif

                        <h3 class="py-5 text-lg font-semibold text-white">{{ $plan->name }}</h3>
                        <p class="mt-3 text-3xl font-extrabold text-white">
                            {{ number_format($plan->price, 0) }} <span class="text-sm font-medium text-gray-400">ETB</span>
                        </p>
                        <p class="mt-1 text-xs text-gray-500">
                            {{ $plan->duration_days ? 'per ' . $plan->duration_days . ' days' : 'one-time' }}</p>

                        <ul class="mt-5 flex-1 space-y-2 text-sm text-gray-300">
                            <li class="flex items-center gap-2">
                                <svg class="h-4 w-4 shrink-0 text-emerald-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                {{ $plan->isUnlimited() ? 'Unlimited listings' : number_format($plan->listings_limit) . ' listings' }}
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="h-4 w-4 shrink-0 text-emerald-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                {{ $plan->duration_days ? 'Valid for ' . $plan->duration_days . ' days' : 'Never expires' }}
                            </li>
                        </ul>

                        <button type="button" @click="choose(@js(['key' => $plan->key, 'name' => $plan->name, 'priceEtb' => number_format($plan->price, 0), 'priceUsd' => $plan->price_usd ? number_format($plan->price_usd, 2) : '']))"
                            class="mt-6 flex w-full items-center justify-center gap-2 rounded-lg py-2.5 text-sm font-medium text-white transition {{ $featured ? 'bg-purple-600 hover:bg-purple-700' : 'bg-indigo-600 hover:bg-indigo-700' }}">
                            Choose payment method
                        </button>
                    </div>
                @endforeach
            </div>

            <p class="mt-8 text-center text-xs text-gray-600">
                Payments are processed securely by Chapa (telebirr, CBE Birr, Dashen Bank and other channels) or by card
                through Stripe.
            </p>
        </div>

        {{-- ============ Payment method modal ============ --}}
        <div x-show="openPlan" x-cloak class="fixed inset-0 z-[90] flex items-center justify-center p-4" role="dialog"
            aria-modal="true" aria-labelledby="pay-title">
            <div x-show="openPlan" x-transition.opacity @click="openPlan = null" class="absolute inset-0 bg-black/70"></div>

            <div x-show="openPlan" x-transition
                class="relative w-full max-w-sm rounded-xl border border-gray-800 bg-[#18181b] shadow-2xl">
                <div class="flex items-center justify-between border-b border-gray-800 px-6 py-4">
                    <h2 id="pay-title" class="font-semibold text-white">Choose how to pay</h2>
                    <button type="button" @click="openPlan = null" aria-label="Close"
                        class="text-gray-500 transition-colors hover:text-gray-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18 6 6 18M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-3 px-6 py-5">
                    <p class="text-sm text-gray-400" x-text="openPlan ? openPlan.name : ''"></p>

                    {{-- Chapa --}}
                    <form action="{{ route('payments.initiate') }}" method="POST" @submit="begin('chapa', $event)">
                        @csrf
                        <input type="hidden" name="plan_key" :value="openPlan?.key">
                        <button type="submit" :disabled="submitting" :aria-busy="(submitting === 'chapa').toString()"
                            class="flex w-full items-center justify-between gap-3 rounded-lg border border-gray-700 p-4 text-left transition hover:border-emerald-500/50 hover:bg-emerald-500/5 disabled:opacity-60">
                            <div>
                                <p class="text-sm font-medium text-white">Pay with Chapa</p>
                                <p class="mt-0.5 text-xs text-gray-500">telebirr, CBE Birr, Dashen &amp; more — <span
                                        x-text="openPlan?.priceEtb"></span> ETB</p>
                            </div>
                            <svg x-show="submitting === 'chapa'" x-cloak
                                class="h-5 w-5 shrink-0 animate-spin text-emerald-400" fill="none" viewBox="0 0 24 24"
                                aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                            <svg x-show="submitting !== 'chapa'" class="h-5 w-5 shrink-0 text-gray-600" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </form>

                    {{-- Stripe --}}
                    <template x-if="openPlan?.priceUsd">
                        <form action="{{ route('payments.stripe.initiate') }}" method="POST"
                            @submit="begin('stripe', $event)">
                            @csrf
                            <input type="hidden" name="plan_key" :value="openPlan?.key">
                            <button type="submit" :disabled="submitting"
                                :aria-busy="(submitting === 'stripe').toString()"
                                class="flex w-full items-center justify-between gap-3 rounded-lg border border-gray-700 p-4 text-left transition hover:border-indigo-500/50 hover:bg-indigo-500/5 disabled:opacity-60">
                                <div>
                                    <p class="text-sm font-medium text-white">Pay with card (Stripe)</p>
                                    <p class="mt-0.5 text-xs text-gray-500">Visa, Mastercard — $<span
                                            x-text="openPlan?.priceUsd"></span> USD</p>
                                </div>
                                <svg x-show="submitting === 'stripe'" x-cloak
                                    class="h-5 w-5 shrink-0 animate-spin text-indigo-400" fill="none"
                                    viewBox="0 0 24 24" aria-hidden="true">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                                    </path>
                                </svg>
                                <svg x-show="submitting !== 'stripe'" class="h-5 w-5 shrink-0 text-gray-600"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </form>
                    </template>

                    <p class="pt-1 text-center text-xs text-gray-600">You will be taken to a secure payment page.</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            window.Alpine.data('pricingPage', ({
                preselected
            }) => ({
                openPlan: preselected,
                submitting: null,
                timer: null,

                init() {
                    // Closing the modal in any way unlocks the buttons.
                    this.$watch('openPlan', (plan) => {
                        if (!plan) this.reset();
                    });
                },

                choose(plan) {
                    this.reset();
                    this.openPlan = plan;
                },

                begin(provider, event) {
                    if (this.submitting) {
                        event.preventDefault(); // already sending: ignore a second click
                        return;
                    }

                    this.submitting = provider;
                    // Still on this page after 20 seconds? The request is stuck or was stopped: unlock.
                    this.timer = setTimeout(() => this.reset(), 20000);
                },

                reset() {
                    clearTimeout(this.timer);
                    this.submitting = null;
                },
            }));
        });
    </script>
@endpush
