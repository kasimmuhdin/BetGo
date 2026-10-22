@extends('layouts.app')
@section('title', 'Pages')

@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-white">Pages</h1>
            <p class="mt-1 text-sm text-gray-400">Edit the text on your public pages. Changes go live as soon as you save.
            </p>
        </div>

        <x-admin.flash />

        <div class="grid gap-4 sm:grid-cols-2">
            @foreach ($pages as $page)
                <div class="flex flex-col rounded-xl border border-[#3a3a3d] bg-[#1e1b1b] p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="truncate text-lg font-semibold text-white">{{ $page->title }}</h2>
                            <p class="mt-0.5 font-mono text-xs text-gray-500">/{{ $page->slug }}</p>
                        </div>
                        <a href="{{ route('pages.show', $page) }}" target="_blank" rel="noopener"
                            class="shrink-0 text-xs font-medium text-gray-400 transition hover:text-white">View ↗</a>
                    </div>

                    <p class="mt-3 flex-1 text-xs text-gray-500">
                        Last saved {{ $page->updated_at->diffForHumans() }}@if ($page->editor)
                            by {{ $page->editor->name }}
                        @endif
                    </p>

                    <div class="mt-4 border-t border-[#3a3a3d] pt-4">
                        <a href="{{ route('admin.pages.edit', $page) }}"
                            class="inline-flex rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">Edit
                            page</a>
                        @if ($page->slug === 'contact')
                            <span class="ml-3 text-xs text-gray-500">Also holds your contact details</span>
                        @endif
                    </div>
                </div>
            @endforeach

            <div class="flex flex-col rounded-xl border border-[#3a3a3d] bg-[#1e1b1b] p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-white">FAQ</h2>
                        <p class="mt-0.5 font-mono text-xs text-gray-500">/faq</p>
                    </div>
                    <a href="{{ route('faq') }}" target="_blank" rel="noopener"
                        class="shrink-0 text-xs font-medium text-gray-400 transition hover:text-white">View ↗</a>
                </div>

                <p class="mt-3 flex-1 text-xs text-gray-500">{{ $faqTotal }} {{ Str::plural('question', $faqTotal) }},
                    {{ $faqLive }} published</p>

                <div class="mt-4 border-t border-[#3a3a3d] pt-4">
                    <a href="{{ route('admin.faqs.index') }}"
                        class="inline-flex rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">Manage
                        questions</a>
                </div>
            </div>
        </div>
    </div>
@endsection
