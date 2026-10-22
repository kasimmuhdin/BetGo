@extends('layouts.app')
@section('title', 'New Message')
@section('content')
    <div class="max-w-xl mx-auto text-white">
        <div class="mb-6">
            <a href="{{ route('messages.index') }}"
                class="inline-flex items-center text-sm text-indigo-400 hover:text-indigo-300 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor"
                    stroke-width="2" class="mr-1">
                    <path d="m15 18-6-6 6-6" />
                </svg>
                Back to inbox
            </a>
            <h1 class="text-xl font-semibold">New message</h1>
            <p class="text-sm text-gray-400 mt-1">
                @if ($mode === 'admin')
                    Send a direct message to any user on the platform.
                @elseif ($mode === 'support')
                    Contact platform support (admin team).
                @else
                    Choose a listing, then write to its owner or agent.
                @endif
            </p>
        </div>

        @if ($mode === 'support' && ! $admin)
            <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-4 text-sm text-amber-200">
                No admin account is available yet. Please try again later or email support.
            </div>
        @else
            <form action="{{ route('messages.store') }}" method="POST"
                class="bg-[#1e1b1b] border border-[#3a3a3d] rounded-xl p-6 space-y-5">
                @csrf

                @if ($mode === 'admin')
                    <div>
                        <label for="recipient_id" class="block text-sm font-medium text-gray-300 mb-1">To</label>
                        <select id="recipient_id" name="recipient_id" required
                            class="w-full bg-[#161414] border border-[#3a3a3d] rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-indigo-500">
                            <option value="">Select a user…</option>
                            @foreach ($recipients as $recipient)
                                <option value="{{ $recipient->id }}" @selected(old('recipient_id', $preselectedRecipientId) == $recipient->id)>
                                    {{ $recipient->name }} ({{ ucfirst($recipient->role) }}) — {{ $recipient->email }}
                                </option>
                            @endforeach
                        </select>
                        @error('recipient_id')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                @elseif ($mode === 'support')
                    <input type="hidden" name="recipient_id" value="{{ $admin->id }}">
                    <div class="rounded-lg bg-[#161414] border border-[#3a3a3d] px-4 py-3 text-sm">
                        <p class="text-gray-400 text-xs uppercase tracking-wide mb-1">To</p>
                        <p class="font-medium text-white">{{ $admin->name }} <span
                                class="text-gray-500 font-normal">(Support)</span></p>
                    </div>
                @else
                    <div>
                        <label for="property_id" class="block text-sm font-medium text-gray-300 mb-1">Property</label>
                        <select id="property_id" name="property_id" required
                            class="w-full bg-[#161414] border border-[#3a3a3d] rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-indigo-500">
                            <option value="">Select a published listing…</option>
                            @foreach ($properties as $property)
                                <option value="{{ $property->id }}" @selected(old('property_id') == $property->id)>
                                    {{ $property->title }} — {{ $property->owner->name ?? 'Owner' }}
                                </option>
                            @endforeach
                        </select>
                        @error('property_id')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                        @if ($properties->isEmpty())
                            <p class="mt-2 text-xs text-gray-500">No published listings are available right now. Browse
                                <a href="{{ route('home') }}" class="text-indigo-400 hover:underline">properties</a> on the
                                home page.</p>
                        @endif
                    </div>
                @endif

                <div>
                    <label for="body" class="block text-sm font-medium text-gray-300 mb-1">Message</label>
                    <textarea id="body" name="body" rows="5" required maxlength="2000"
                        placeholder="Write your message…"
                        class="w-full bg-[#161414] border border-[#3a3a3d] rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-indigo-500 resize-none">{{ old('body') }}</textarea>
                    @error('body')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="w-full py-2.5 rounded-lg bg-indigo-600 text-sm font-semibold text-white hover:bg-indigo-700 transition">
                    Send message
                </button>
            </form>
        @endif
    </div>
@endsection
