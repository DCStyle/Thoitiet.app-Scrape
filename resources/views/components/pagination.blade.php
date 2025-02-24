@props(['paginator'])

@if ($paginator->hasPages())
    <div class="d-flex align-items-center gap-3">
        <div class="text-secondary">
            Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} results
        </div>

        <nav aria-label="Page navigation">
            <ul class="pagination mb-0">
                {{-- Previous Page Link --}}
                <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>

                @php
                    $start = 1;
                    $end = $paginator->lastPage();
                    $current = $paginator->currentPage();
                    $window = 2; // Number of pages to show before and after current page

                    if ($end > 10) {
                        if ($current <= 5) {
                            $start = 1;
                            $end = 8;
                        } elseif ($current > $paginator->lastPage() - 5) {
                            $start = $paginator->lastPage() - 7;
                            $end = $paginator->lastPage();
                        } else {
                            $start = $current - $window;
                            $end = $current + $window;
                        }
                    }
                @endphp

                {{-- First Page --}}
                @if($start > 1)
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->url(1) }}">1</a>
                    </li>
                    @if($start > 2)
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    @endif
                @endif

                {{-- Pagination Elements --}}
                @for ($i = $start; $i <= $end; $i++)
                    <li class="page-item {{ $paginator->currentPage() == $i ? 'active' : '' }}">
                        <a class="page-link" href="{{ $paginator->url($i) }}">{{ $i }}</a>
                    </li>
                @endfor

                {{-- Last Page --}}
                @if($end < $paginator->lastPage())
                    @if($end < $paginator->lastPage() - 1)
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    @endif
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->url($paginator->lastPage()) }}">
                            {{ $paginator->lastPage() }}
                        </a>
                    </li>
                @endif

                {{-- Next Page Link --}}
                <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <style>
        .pagination .page-link {
            color: #666;
            padding: 0.5rem 0.75rem;
            border: 1px solid #dee2e6;
            margin: 0 2px;
        }

        .pagination .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: white;
        }

        .pagination .page-link:hover {
            background-color: #e9ecef;
            border-color: #dee2e6;
            color: #0d6efd;
        }

        .pagination .page-item:first-child .page-link,
        .pagination .page-item:last-child .page-link {
            border-radius: 0;
        }
    </style>
@endif
