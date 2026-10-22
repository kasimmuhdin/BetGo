@props(['property', 'saved' => false, 'variant' => 'icon'])

{{-- Owners can't save their own listing, so no button for them --}}
@unless (auth()->id() === $property->user_id)
    @php
        $isPill = $variant === 'pill';

        $shape = $isPill
            ? 'inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border text-sm font-semibold'
            : 'flex items-center justify-center w-9 h-9 rounded-full backdrop-blur-sm';

        $on = $isPill ? 'bg-rose-500/10 border-rose-500/40 text-rose-400' : 'bg-rose-500 text-white';
        $off = $isPill
            ? 'bg-transparent border-[#3a3a3d] text-gray-300 hover:bg-[#2a2727]'
            : 'bg-black/50 text-white hover:bg-black/70';
    @endphp

    @auth
        <button type="button" x-data="saveButton(@js(['url' => route('saved-listings.store', $property), 'saved' => (bool) $saved]))" @click="toggle()" :disabled="busy"
            :aria-pressed="saved ? 'true' : 'false'" :aria-label="saved ? 'Remove from saved listings' : 'Save listing'"
            :title="saved ? 'Remove from saved' : 'Save listing'"
            :class="saved ? @js($on) : @js($off)"
            class="{{ $shape }} transition-colors disabled:opacity-70">
            <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" :fill="saved ? 'currentColor' : 'none'">
                <path
                    d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
            </svg>
            @if ($isPill)
                <span x-text="saved ? 'Saved' : 'Save'"></span>
            @endif
        </button>
    @else
        <a href="{{ route('saved-listings.guest', $property) }}" title="Log in to save" aria-label="Log in to save this listing"
            class="{{ $shape }} {{ $off }} transition-colors">
            <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path
                    d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
            </svg>
            @if ($isPill)
                <span>Save</span>
            @endif
        </a>
    @endauth
@endunless
