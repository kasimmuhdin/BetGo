@extends('layouts.app')
@section('title', 'Add question')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <a href="{{ route('admin.faqs.index') }}"
            class="inline-flex items-center gap-1 text-sm text-indigo-400 hover:text-indigo-300">← All questions</a>
        <h1 class="text-2xl font-bold tracking-tight text-white">Add a question</h1>

        @include('admin.faqs._form', ['faq' => $faq, 'action' => route('admin.faqs.store')])
    </div>
@endsection
