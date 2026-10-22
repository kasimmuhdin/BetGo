@props(['title', 'subtitle' => null, 'flush' => false])

<section {{ $attributes->class('min-w-0 rounded-xl border border-[#3a3a3d] bg-[#1e1b1b]') }}>
    <header class="flex items-start justify-between gap-3 border-b border-[#3a3a3d] px-5 py-4">
        <div class="min-w-0">
            <h2 class="text-base font-semibold text-white">{{ $title }}</h2>
            @if ($subtitle)
                <p class="mt-0.5 text-xs text-gray-500">{{ $subtitle }}</p>
            @endif
        </div>
        @isset($action)
            <div class="shrink-0">{{ $action }}</div>
        @endisset
    </header>
    <div @class(['p-5' => !$flush])>{{ $slot }}</div>
</section>
