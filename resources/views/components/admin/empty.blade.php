@props(['message'])

<div class="flex min-h-[10rem] flex-col items-center justify-center px-6 py-10 text-center">
    <span class="mb-3 flex h-11 w-11 items-center justify-center rounded-full bg-[#2a2727] text-gray-500">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18M7 14l4-4 4 4 5-6" />
        </svg>
    </span>
    <p class="text-sm text-gray-400">{{ $message }}</p>
</div>
