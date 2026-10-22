@extends('layouts.app')
@section('title', 'Review: ' . $user->name)
@section('content')
    <div x-data="{ approveModal: false, rejectModal: false, isSubmitting: false }" class="max-w-3xl mx-auto px-4 py-8 text-gray-200 font-sans">

        <a href="{{ route('admin.users.all') }}"
            class="inline-flex items-center text-sm text-indigo-400 hover:text-indigo-300 font-medium mb-6 transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Queue
        </a>

        <div class="bg-[#18181b] border border-gray-800 rounded-xl p-6 shadow-lg space-y-6">
            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div class="flex items-center gap-4">
                    <div
                        class="w-14 h-14 rounded-full bg-indigo-500/20 flex items-center justify-center text-indigo-400 font-bold text-xl shrink-0">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-white">{{ $user->name }}</h1>
                        <p class="text-sm text-gray-400">{{ $user->email }}</p>
                    </div>
                </div>

                @if ($user->status === 'pending')
                    <span
                        class="px-3 py-1 bg-amber-500/10 text-amber-500 border border-amber-500/20 rounded-full text-xs font-bold uppercase tracking-wide">Pending
                        Review</span>
                @elseif ($user->status === 'approved')
                    <span
                        class="px-3 py-1 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-full text-xs font-bold uppercase tracking-wide">Approved</span>
                @else
                    <span
                        class="px-3 py-1 bg-red-500/10 text-red-400 border border-red-500/20 rounded-full text-xs font-bold uppercase tracking-wide">Rejected</span>
                @endif
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 border-t border-gray-800 pt-5">
                <div>
                    <p class="text-xs text-gray-500">Requested Role</p>
                    <p class="text-sm font-medium text-gray-200 mt-1">{{ ucfirst($user->role) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Phone</p>
                    <p class="text-sm font-medium text-gray-200 mt-1">{{ $user->phone ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Requested On</p>
                    <p class="text-sm font-medium text-gray-200 mt-1">{{ $user->updated_at->format('M d, Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">FAN / National ID</p>
                    <p class="text-sm font-medium text-gray-200 mt-1 font-mono">{{ $user->national_id ?? '—' }}</p>
                </div>
                @if ($user->role === 'agent')
                    <div>
                        <p class="text-xs text-gray-500">TIN Number</p>
                        <p class="text-sm font-medium text-gray-200 mt-1 font-mono">{{ $user->tin ?? '—' }}</p>
                    </div>
                @endif
                <div>
                    <p class="text-xs text-gray-500">Member Since</p>
                    <p class="text-sm font-medium text-gray-200 mt-1">{{ $user->created_at->format('M d, Y') }}</p>
                </div>
            </div>

            @if ($user->status !== 'pending')
                <div class="border-t border-gray-800 pt-5">
                    <p class="text-xs text-gray-500">Reviewed By</p>
                    <p class="text-sm font-medium text-gray-200 mt-1">{{ $user->verifier->name ?? '—' }} ·
                        {{ optional($user->verified_at)->format('M d, Y H:i') }}</p>
                    @if ($user->status === 'rejected' && $user->verification_rejection_reason)
                        <div class="bg-red-500/10 border border-red-500/20 rounded-lg p-4 mt-3">
                            <p class="text-xs text-red-400 font-semibold uppercase tracking-wide mb-1">Rejection Reason</p>
                            <p class="text-sm text-gray-300">{{ $user->verification_rejection_reason }}</p>
                        </div>
                    @endif
                </div>
            @endif

            @if (auth()->user()->role === 'admin' && auth()->id() !== $user->id)
                <div class="border-t border-gray-800 pt-5">
                    <a href="{{ route('messages.compose', ['recipient' => $user->id]) }}"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-indigo-600 text-sm font-medium text-white hover:bg-indigo-700 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                        </svg>
                        Send message
                    </a>
                </div>
            @endif

            @if ($user->status === 'pending')
                <div class="border-t border-gray-800 pt-5 flex gap-3">
                    <button @click="approveModal = true"
                        class="flex-1 flex justify-center items-center px-4 py-3 text-sm font-medium rounded-lg text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition-colors">
                        Approve as {{ ucfirst($user->role) }}
                    </button>
                    <button @click="rejectModal = true"
                        class="flex-1 flex justify-center items-center px-4 py-3 border border-red-500/30 text-sm font-medium rounded-lg text-red-400 bg-red-500/10 hover:bg-red-500 hover:text-white transition-colors">
                        Reject
                    </button>
                </div>
            @endif
        </div>

        <!-- Approve Modal -->
        <div x-show="approveModal" style="display: none;" class="fixed z-50 inset-0 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="approveModal" x-transition.opacity class="fixed inset-0 bg-black/80 backdrop-blur-sm"
                    @click="approveModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div x-show="approveModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    class="inline-block align-bottom bg-[#18181b] border border-gray-700 rounded-xl text-left overflow-hidden shadow-2xl relative z-10 inline-block sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    <form action="{{ route('admin.users.approve', $user) }}" method="POST" @submit="isSubmitting = true">
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
                                    <h3 class="text-lg font-medium text-white">Approve {{ ucfirst($user->role) }}</h3>
                                    <p class="text-sm text-gray-400 mt-2">Approve <strong
                                            class="text-gray-200">{{ $user->name }}</strong> as
                                        a{{ $user->role === 'agent' ? 'n' : '' }} {{ ucfirst($user->role) }}? They'll
                                        immediately be able to post listings.</p>
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

        <!-- Reject Modal -->
        <div x-show="rejectModal" style="display: none;" class="fixed z-50 inset-0 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="rejectModal" x-transition.opacity class="fixed inset-0 bg-black/80 backdrop-blur-sm"
                    @click="rejectModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div x-show="rejectModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-data="{ reason: '' }"
                    class="inline-block align-bottom bg-[#18181b] border border-gray-700 rounded-xl text-left overflow-hidden shadow-2xl relative z-10 inline-block sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    <form action="{{ route('admin.users.reject', $user) }}" method="POST"
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
                                    <h3 class="text-lg font-medium text-white">Reject Request</h3>
                                    <p class="text-sm text-gray-400 mb-3 mt-2">Explain why — {{ $user->name }} will see
                                        this exact message.</p>
                                    <textarea x-model="reason" name="verification_rejection_reason" rows="3" required minlength="10"
                                        maxlength="1000" placeholder="e.g., The National ID number provided doesn't match our records."
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
    </div>
@endsection
