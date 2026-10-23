{{-- Pagination serveur aux styles du datatable Metronic : $paginator->links('partials.pagination-metronic') --}}
@if ($paginator->hasPages())
    <nav class="kt-datatable-pagination" aria-label="Pagination">
        @if ($paginator->onFirstPage())
            <button type="button" class="kt-datatable-pagination-button kt-datatable-pagination-prev" disabled>
                <i class="ki-filled ki-black-left rtl:rotate-180"></i>
            </button>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="kt-datatable-pagination-button kt-datatable-pagination-prev" rel="prev" title="Page précédente">
                <i class="ki-filled ki-black-left rtl:rotate-180"></i>
            </a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <button type="button" class="kt-datatable-pagination-button" disabled>{{ $element }}</button>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <button type="button" class="kt-datatable-pagination-button active" aria-current="page">{{ $page }}</button>
                    @else
                        <a href="{{ $url }}" class="kt-datatable-pagination-button">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="kt-datatable-pagination-button kt-datatable-pagination-next" rel="next" title="Page suivante">
                <i class="ki-filled ki-black-right rtl:rotate-180"></i>
            </a>
        @else
            <button type="button" class="kt-datatable-pagination-button kt-datatable-pagination-next" disabled>
                <i class="ki-filled ki-black-right rtl:rotate-180"></i>
            </button>
        @endif
    </nav>
@endif
