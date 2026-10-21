@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="pagination">
        <p>
            Showing <span class="font-semibold text-fg">{{ $paginator->firstItem() }}</span>–<span class="font-semibold text-fg">{{ $paginator->lastItem() }}</span>
            of <span class="font-semibold text-fg">{{ $paginator->total() }}</span>
        </p>

        <div class="pagination-pages">
            @if ($paginator->onFirstPage())
                <span class="page-link is-disabled" aria-disabled="true" aria-label="Previous page"><x-ui.icon name="chevron-left" /></span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="page-link" aria-label="Previous page"><x-ui.icon name="chevron-left" /></a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="page-link is-disabled" aria-disabled="true">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="page-link is-current" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="page-link" aria-label="Go to page {{ $page }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="page-link" aria-label="Next page"><x-ui.icon name="chevron-right" /></a>
            @else
                <span class="page-link is-disabled" aria-disabled="true" aria-label="Next page"><x-ui.icon name="chevron-right" /></span>
            @endif
        </div>
    </nav>
@endif
