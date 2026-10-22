@props(['name', 'label', 'type' => 'text', 'value' => null, 'hint' => null, 'bag' => 'default'])

@php
    $id = $attributes->get('id', $name);
    $error = $errors->getBag($bag)->first($name);
@endphp

<div>
    <label for="{{ $id }}" class="mb-1.5 block text-sm font-medium text-gray-200">{{ $label }}</label>

    <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value) }}"
        @if ($error) aria-invalid="true" aria-describedby="{{ $id }}-msg" @elseif ($hint) aria-describedby="{{ $id }}-msg" @endif
        {{ $attributes->except('id')->class([
                'w-full rounded-lg border bg-[#161414] px-3 py-2.5 text-sm text-white placeholder:text-gray-600 focus:outline-none focus:ring-1',
                'border-red-500 focus:border-red-500 focus:ring-red-500' => $error,
                'border-[#3a3a3d] focus:border-indigo-500 focus:ring-indigo-500' => !$error,
            ]) }}>

    @if ($error)
        <p id="{{ $id }}-msg" role="alert" class="mt-1.5 text-xs text-red-400">{{ $error }}</p>
    @elseif ($hint)
        <p id="{{ $id }}-msg" class="mt-1.5 text-xs text-gray-500">{{ $hint }}</p>
    @endif
</div>
