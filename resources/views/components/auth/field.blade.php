@props(['name', 'label', 'type' => 'text', 'icon' => null, 'value' => null, 'hint' => null, 'meter' => false])

@php
    $id = $attributes->get('id', $name);
    $hasError = $errors->has($name);
    $describedBy = $hasError ? $id . '-err' : ($hint ? $id . '-hint' : null);
@endphp

<div class="au-field">
    {{-- The extra link sits beside the label, not inside it. Inside, screen readers
         read "Password Forgot password?" as the name of the input. --}}
    <div class="au-label-row">
        <label for="{{ $id }}" class="au-label">{{ $label }}</label>
        @if (isset($link))
            {{ $link }}
        @endif
    </div>

    <div @class(['au-input', 'has-error' => $hasError])>
        @if ($icon)
            <i class="{{ $icon }} au-ico" aria-hidden="true"></i>
        @endif

        <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}"
            @if ($type !== 'password') value="{{ old($name, $value) }}" @endif
            @if ($type === 'email') autocapitalize="none" autocorrect="off" spellcheck="false" @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            @if ($hasError) aria-invalid="true" @endif
            @if ($meter) data-strength @endif {{ $attributes->except('id') }}>

        @if ($type === 'password')
            <button type="button" class="au-eye" data-toggle-password aria-label="Show password" aria-pressed="false">
                <i class="fa-regular fa-eye" aria-hidden="true"></i>
            </button>
        @endif
    </div>

    @if ($meter)
        <div class="au-meter" data-meter data-level="0" aria-hidden="true">
            <span></span><span></span><span></span><span></span></div>
    @endif

    @if ($hasError)
        <p class="au-err" id="{{ $id }}-err" role="alert">
            <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> {{ $errors->first($name) }}
        </p>
    @elseif ($hint)
        <p class="au-hint" id="{{ $id }}-hint">{{ $hint }}</p>
    @endif
</div>
