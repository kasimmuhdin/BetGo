@if ($paginator->hasPages())
    <nav class="flex items-center justify-between px-1">
        <p class="text-sm text-gray-400">
            Showing <span class="font-medium text-gray-200">{{ $paginator->firstItem() }}</span>
            to <span class="font-medium text-gray-200">{{ $paginator->lastItem() }}</span>
            of <span class="font-medium text-gray-200">{{ $paginator->total() }}</span> results
        </p>

        <div class="flex items-center gap-1">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span class="px-3 py-2 rounded-lg text-gray-600 border border-[#3a3a3d] cursor-not-allowed">‹</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}"
                    class="px-3 py-2 rounded-lg text-gray-300 border border-[#3a3a3d] hover:bg-[#2a2727] hover:text-white transition">‹</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-3 py-2 text-gray-600">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span
                                class="px-3.5 py-2 rounded-lg bg-indigo-600 text-white font-medium">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}"
                                class="px-3.5 py-2 rounded-lg text-gray-300 border border-[#3a3a3d] hover:bg-[#2a2727] hover:text-white transition">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}"
                    class="px-3 py-2 rounded-lg text-gray-300 border border-[#3a3a3d] hover:bg-[#2a2727] hover:text-white transition">›</a>
            @else
                <span class="px-3 py-2 rounded-lg text-gray-600 border border-[#3a3a3d] cursor-not-allowed">›</span>
            @endif
        </div>
    </nav>
@endif
