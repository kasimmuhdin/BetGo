@if ($paginator->hasPages())
    <nav class="hp-pagination" aria-label="Pagination">
        <p>Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of
            {{ number_format($paginator->total()) }}</p>

        <ul>
            <li>
                @if ($paginator->onFirstPage())
                    <span class="is-disabled" aria-disabled="true">‹ Prev</span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev">‹ Prev</a>
                @endif
            </li>

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="is-disabled">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li>
                            @if ($page == $paginator->currentPage())
                                <span class="is-current" aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}"
                                    aria-label="Page {{ $page }}">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach

            <li>
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next">Next ›</a>
                @else
                    <span class="is-disabled" aria-disabled="true">Next ›</span>
                @endif
            </li>
        </ul>
    </nav>
@endif
