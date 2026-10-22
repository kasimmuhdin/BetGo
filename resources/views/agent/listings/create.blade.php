@extends('layouts.app')

@section('title', $listing->exists ? 'Edit Listing' : 'New Listing')

@section('content')



    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ $listing->exists ? 'Edit Listing' : 'New Listing' }}</h1>
        <a href="{{ route('agent.listings.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">← Back to
            listings</a>
    </div>

    <form action="{{ $listing->exists ? route('agent.listings.update', $listing) : route('agent.listings.store') }}"
        method="POST" enctype="multipart/form-data" x-data="{ submitting: false, status: 'draft' }" @submit="submitting = true">

        <input type="hidden" name="status" x-model="status">

        @include('agent.listings._form')
    </form>
@endsection
