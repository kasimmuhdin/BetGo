@extends('layouts.app')

@section('title', $listing->exists ? 'Edit Listing' : 'New Listing')

@section('content')



    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ $listing->exists ? 'Edit Listing' : 'New Listing' }}</h1>
        <a href="{{ route('owner.listings.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">← Back to
            listings</a>
    </div>

    <form action="{{ $listing->exists ? route('owner.listings.update', $listing) : route('owner.listings.store') }}"
        method="POST" enctype="multipart/form-data" x-data="{ submitting: false, status: 'draft' }" @submit="submitting = true">

        <input type="hidden" name="status" x-model="status">

        @include('owner.listings._form')
    </form>
@endsection
