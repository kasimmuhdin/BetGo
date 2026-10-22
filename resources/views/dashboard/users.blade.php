@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php
        $statIcons = [
            'heart' =>
                '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>',
            'home' =>
                '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
            'calendar' =>
                '<path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="m9 16 2 2 4-4"/>',
            'wallet' =>
                '<path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/>',
            'trending' => '<polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/>',
        ];
        $user = auth()->user();
    @endphp

    <style>
        .card {
            background-color: #1e1b1b;
            border-radius: 0.75rem;
            border: 1px solid #3a3a3d;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
            padding: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card:hover {
            transform: translateY(-4px);
            transition: all 0.5s ease-in;
        }

        .bgforall {
            background: #1e1b1b;
            border: 1px solid #3a3a3d;
        }

        .bgforall-prompt {
            background: #1e1b1b;
            border: 1px solid #3a3a3d;
            border-radius: 0.75rem;
            padding: 1.5rem;
        }

        .divide-y>*+* {
            border-color: #3a3a3d;
        }

        .border-b {
            border-color: #3a3a3d;
        }

        .border-t {
            border-color: #3a3a3d;
        }

        .modal-content {
            background: #1e1b1b;
            border: 1px solid #3a3a3d;
        }

        .modal-header {
            border-color: #3a3a3d;
        }

        .modal-input {
            background: #2a2727;
            border: 1px solid #3a3a3d;
            color: #e5e7eb;
        }

        .modal-input:focus {
            border-color: #6366f1;
            ring-color: #6366f1;
        }

        .modal-input::placeholder {
            color: #6b7280;
        }

        .role-option {
            background: #2a2727;
            border: 1px solid #3a3a3d;
        }

        .role-option:hover {
            background: #333030;
        }

        .role-option.selected {
            border-color: #6366f1;
            background: rgba(99, 102, 241, 0.1);
        }

        .text-muted {
            color: #9ca3af;
        }

        .text-muted-light {
            color: #6b7280;
        }

        .badge-pending {
            background: rgba(251, 191, 36, 0.2);
            color: #fbbf24;
        }

        .badge-verified {
            background: rgba(52, 211, 153, 0.2);
            color: #34d399;
        }
    </style>

    <div x-data="{ modalOpen: false, step: 1, selectedRole: null, submitting: false }" class="space-y-6">
        <!-- Page header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-100">Welcome back, {{ explode(' ', $user->name)[0] }}</h1>
                <p class="text-sm text-gray-400 mt-1">Here's a quick look at your account.</p>
            </div>
            @if ($user->is_active && $user->email_verified_at)
                <a href="#" @click.prevent="modalOpen = true; step = 1; selectedRole = null"
                    class="px-4 py-2 rounded-lg bg-indigo-600 text-sm font-medium text-white hover:bg-indigo-700 transition-colors flex-shrink-0">
                    + Post a Listing
                </a>
            @else
                <div class="bg-rose-900/20 border border-rose-500 text-rose-400 px-4 py-2 rounded-lg text-sm font-medium">
                    <p class="mb-1">You cannot post listings yet.</p>
                    <div class="">
                        @if (!$user->is_active)
                            <div>Your account is inactive. Please contact support.</div>
                        @endif
                        @if (!$user->email_verified_at)
                            <div>
                                @php $status = session('status'); @endphp

                                <form method="POST" action="{{ route('verification.send') }}" data-loading
                                    id="send-verification">
                                    @csrf
                                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                                        <div>
                                            <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                                                {{ __('Your email address is unverified.') }}

                                                <button form="send-verification" type="submit"
                                                    class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                                    {{ __('Click here to re-send the verification email.') }}
                                                </button>
                                            </p>

                                            @if (session('status') === 'verification-link-sent')
                                                <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                                                    {{ __('A new verification link has been sent to your email address.') }}
                                                </p>
                                            @endif
                                        </div>
                                    @endif
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Stat cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            @foreach ($stats as $stat)
                <div class="card">
                    <div>
                        <p class="text-sm text-gray-400">{{ $stat['label'] }}</p>
                        <p class="text-2xl font-bold text-gray-100 mt-1">{{ $stat['value'] }}</p>
                        <p class="text-xs mt-1 font-medium {{ $stat['up'] ? 'text-emerald-400' : 'text-rose-400' }}">
                            {{ $stat['up'] ? '↑' : '↓' }} {{ $stat['change'] }} <span class="text-gray-500 font-normal">this
                                month</span>
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 rounded-lg bg-indigo-500/20 flex items-center justify-center text-indigo-400 flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            {!! $statIcons[$stat['icon']] !!}
                        </svg>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Message a property owner -->
        @if ($user->role === 'user')
            <div class="bgforall-prompt flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <p class="font-semibold text-gray-200">Questions about a listing?</p>
                    <p class="text-sm text-gray-400 mt-0.5">Pick a published property and send a message directly to its
                        owner or agent.</p>
                </div>
                <a href="{{ route('messages.compose') }}"
                    class="px-4 py-2 rounded-lg bg-indigo-600 text-sm font-medium text-white hover:bg-indigo-700 transition-colors flex-shrink-0 text-center">
                    Message about a property
                </a>
            </div>
        @endif



        <!-- Modal -->
        <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div x-show="modalOpen" x-transition.opacity @click="modalOpen = false" class="absolute inset-0 bg-black/70">
            </div>

            <div x-show="modalOpen" x-transition class="relative modal-content rounded-xl shadow-xl w-full max-w-md">
                <form action="{{ route('role-requests.store') }}" method="POST" @submit="submitting = true">
                    @csrf

                    <!-- Header -->
                    <div class="flex items-center justify-between px-6 py-4 border-b modal-header">
                        <h2 class="font-semibold text-gray-200"
                            x-text="step === 1 ? 'Choose an option' : 'Verify your identity'"></h2>
                        <button type="button" @click="modalOpen = false"
                            class="text-gray-500 hover:text-gray-300 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M18 6 6 18" />
                                <path d="m6 6 12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Step 1: choose role -->
                    <div x-show="step === 1" class="px-6 py-5 space-y-3">
                        <label class="role-option flex items-center gap-3 rounded-lg p-4 cursor-pointer transition-colors"
                            :class="selectedRole === 'owner' ? 'selected' : ''">
                            <input type="radio" name="role" value="owner" x-model="selectedRole"
                                class="text-indigo-500 focus:ring-indigo-500 accent-indigo-500">
                            <div>
                                <p class="font-medium text-gray-200 text-sm">I'm a Property Owner</p>
                                <p class="text-xs text-gray-400">I want to list properties I own.</p>
                            </div>
                        </label>
                        <label class="role-option flex items-center gap-3 rounded-lg p-4 cursor-pointer transition-colors"
                            :class="selectedRole === 'agent' ? 'selected' : ''">
                            <input type="radio" name="role" value="agent" x-model="selectedRole"
                                class="text-indigo-500 focus:ring-indigo-500 accent-indigo-500">
                            <div>
                                <p class="font-medium text-gray-200 text-sm">I'm an Agent</p>
                                <p class="text-xs text-gray-400">I list and manage properties on behalf of owners.</p>
                            </div>
                        </label>

                        <button type="button" @click="if (selectedRole) step = 2" :disabled="!selectedRole"
                            :class="selectedRole ? 'bg-indigo-600 hover:bg-indigo-700' :
                                'bg-gray-700 cursor-not-allowed text-gray-500'"
                            class="w-full mt-2 px-4 py-2.5 rounded-lg text-sm font-medium text-white transition-colors">
                            Continue
                        </button>
                    </div>

                    <!-- Step 2: FAN / National ID (+ TIN for agents) -->
                    <div x-show="step === 2" class="px-6 py-5 space-y-4">
                        <div>
                            <label for="national_id" class="block text-sm font-medium text-gray-300 mb-1">FAN / National
                                ID Number</label>
                            <input type="text" id="national_id" name="national_id" required inputmode="numeric"
                                pattern="\d{12}" maxlength="12"
                                class="modal-input w-full rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="12-digit Fayda number">
                            <p class="text-xs text-gray-500 mt-1">Enter your 12-digit Fayda (FAN) number. We'll verify it
                                properly once our Fayda integration is live.</p>
                        </div>

                        <div x-show="selectedRole === 'agent'" x-transition>
                            <label for="tin" class="block text-sm font-medium text-gray-300 mb-1">TIN Number</label>
                            <input type="text" id="tin" name="tin" :required="selectedRole === 'agent'"
                                maxlength="50"
                                class="modal-input w-full rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="Taxpayer Identification Number">
                            <p class="text-xs text-gray-500 mt-1">Agents must provide a valid TIN to verify they're
                                licensed to
                                operate on behalf of property owners.</p>
                        </div>

                        <div class="flex gap-2 pt-1">
                            <button type="button" @click="step = 1" :disabled="submitting"
                                class="px-4 py-2.5 rounded-lg border border-gray-700 text-sm font-medium text-gray-400 hover:bg-gray-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                Back
                            </button>
                            <button type="submit" :disabled="submitting"
                                class="flex-1 px-4 py-2.5 rounded-lg bg-indigo-600 text-sm font-medium text-white hover:bg-indigo-700 transition-colors disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                                <svg x-show="submitting" class="w-4 h-4 animate-spin" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                                    </path>
                                </svg>
                                <span x-text="submitting ? 'Submitting…' : 'Submit Request'"></span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
