@php
    $message = session('error') ?? session('success');
    $isError = session()->has('error');
@endphp

@if ($message)
    <div x-data="{ show: true }" x-show="show" x-transition.opacity x-init="{{ $isError ? '' : 'setTimeout(() => show = false, 6000)' }}" role="status"
        @class([
            'flex items-start gap-2.5 rounded-xl border px-4 py-3 text-sm',
            'border-red-500/30 bg-red-500/10 text-red-300' => $isError,
            'border-emerald-500/30 bg-emerald-500/10 text-emerald-300' => !$isError,
        ])>
        <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
            aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="{{ $isError ? 'M12 9v2m0 4h.01M10.3 3.9L2.4 17.5A2 2 0 004.1 20.5h15.8a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z' : 'M5 13l4 4L19 7' }}" />
        </svg>
        <span class="flex-1">{{ $message }}</span>
        <button type="button" @click="show = false" aria-label="Dismiss"
            class="text-current opacity-60 transition hover:opacity-100">✕</button>
    </div>
@endif
