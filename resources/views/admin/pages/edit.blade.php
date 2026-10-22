@extends('layouts.app')
@section('title', 'Edit ' . $page->title)

@section('content')
    @php
        $socialLabels = [
            'facebook' => 'Facebook',
            'instagram' => 'Instagram',
            'x' => 'X (Twitter)',
            'youtube' => 'YouTube',
            'telegram' => 'Telegram',
        ];
        $spinner =
            '<svg x-show="submitting" x-cloak class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>';
    @endphp

    <div class="mx-auto max-w-4xl space-y-6">
        <a href="{{ route('admin.pages.index') }}"
            class="inline-flex items-center gap-1 text-sm text-indigo-400 hover:text-indigo-300">← All pages</a>

        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-white">Edit {{ $page->title }}</h1>
                <p class="mt-1 text-xs text-gray-500">
                    <span class="font-mono">/{{ $page->slug }}</span> · last saved {{ $page->updated_at->diffForHumans() }}
                    @if ($page->editor)
                        by {{ $page->editor->name }}
                    @endif
                </p>
            </div>
            <a href="{{ route('pages.show', $page) }}" target="_blank" rel="noopener"
                class="rounded-lg border border-[#3a3a3d] px-3 py-2 text-sm text-gray-300 transition hover:bg-[#2a2727] hover:text-white">View
                live page ↗</a>
        </div>

        <x-admin.flash />

        @if ($errors->any())
            <div role="alert" class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">
                Please fix the highlighted fields below.</div>
        @endif
        @if ($errors->hasBag('contact'))
            <div role="alert" class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">
                Some contact details are not valid. <a href="#contact-details" class="font-semibold underline">Review
                    them</a>.
            </div>
        @endif

        {{-- ============ Page text ============ --}}
        <form method="POST" action="{{ route('admin.pages.update', $page) }}" x-data="{ dirty: false, submitting: false, meta: @js(old('meta_description', $page->meta_description ?? '')) }" @input="dirty = true"
            @submit="submitting = true; dirty = false" @pageshow.window="submitting = false"
            @beforeunload.window="if (dirty) { $event.preventDefault(); $event.returnValue = '' }"
            class="space-y-5 rounded-xl border border-[#3a3a3d] bg-[#1e1b1b] p-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="version" value="{{ $page->updated_at->getTimestamp() }}">

            <x-admin.input name="title" label="Page title" :value="$page->title" maxlength="120" required />

            <div>
                <x-admin.input name="meta_description" label="Short description" :value="$page->meta_description" maxlength="160"
                    x-model="meta" hint="Shown by search engines under the page title. Keep it under 160 characters." />
                <p class="mt-1 text-right text-xs text-gray-600"><span x-text="meta.length"></span>/160</p>
            </div>

            <x-admin.markdown-field name="content" label="Page text" :value="$page->content" rows="22" />

            <div class="flex items-center justify-end gap-3 border-t border-[#3a3a3d] pt-5">
                <a href="{{ route('admin.pages.index') }}"
                    class="rounded-lg px-4 py-2 text-sm text-gray-400 transition hover:text-white">Cancel</a>
                <button type="submit" :disabled="submitting"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60">
                    {!! $spinner !!}
                    <span x-text="submitting ? 'Saving…' : 'Save page'">Save page</span>
                </button>
            </div>
        </form>

        {{-- ============ Contact details (Contact page only) ============ --}}
        @if ($page->slug === 'contact')
            <form id="contact-details" method="POST" action="{{ route('admin.settings.contact.update') }}"
                x-data="{ submitting: false }" @submit="submitting = true" @pageshow.window="submitting = false"
                class="scroll-mt-24 space-y-5 rounded-xl border border-[#3a3a3d] bg-[#1e1b1b] p-6">
                @csrf
                @method('PUT')

                <div>
                    <h2 class="text-lg font-semibold text-white">Contact details</h2>
                    <p class="mt-1 text-sm text-gray-400">
                        Shown under the text on this page and in the website footer. Leave a field empty to hide it.
                        Values saved here replace the defaults in your .env file.
                    </p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-admin.input name="email" bag="contact" type="email" label="Email" :value="$contact['email'] ?? ''"
                        placeholder="support@yourdomain.com" />
                    <x-admin.input name="phone" bag="contact" type="tel" label="Phone" :value="$contact['phone'] ?? ''"
                        placeholder="+251 911 000 000" />
                </div>

                <x-admin.input name="address" bag="contact" label="Address" :value="$contact['address'] ?? ''"
                    placeholder="Bole, Addis Ababa, Ethiopia" />

                <div>
                    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-gray-400">Social links</h3>
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach ($socialLabels as $key => $label)
                            <x-admin.input :name="$key" bag="contact" type="url" :label="$label"
                                :value="$social[$key] ?? ''" placeholder="https://" />
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end border-t border-[#3a3a3d] pt-5">
                    <button type="submit" :disabled="submitting"
                        class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60">
                        {!! $spinner !!}
                        <span x-text="submitting ? 'Saving…' : 'Save contact details'">Save contact details</span>
                    </button>
                </div>
            </form>
        @endif
    </div>
@endsection
