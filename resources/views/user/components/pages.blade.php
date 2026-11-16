
@if ($paginator->hasPages())
    <nav class="cu-pages fb-number-pages" aria-label="Navigasi halaman">
        <div class="fb-pages-info">
            Menampilkan
            <strong>{{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }}</strong>
            dari <strong>{{ $paginator->total() }}</strong> laporan
        </div>

        <div class="fb-pages-navigation">
            @if ($paginator->onFirstPage())
                <span class="fb-page-arrow disabled">‹ Sebelumnya</span>
            @else
                <a class="fb-page-arrow" href="{{ $paginator->previousPageUrl() }}">
                    ‹ Sebelumnya
                </a>
            @endif

            @php
                $current = $paginator->currentPage();
                $last = $paginator->lastPage();
                $start = max(1, $current - 1);
                $end = min($last, $current + 1);

                if ($current === 1) {
                    $end = min($last, 3);
                } elseif ($current === $last) {
                    $start = max(1, $last - 2);
                }
            @endphp

            @if ($start > 1)
                <a class="fb-page-number" href="{{ $paginator->url(1) }}">1</a>
                @if ($start > 2)
                    <span class="fb-page-dots">...</span>
                @endif
            @endif

            @for ($page = $start; $page <= $end; $page++)
                @if ($page === $current)
                    <span class="fb-page-number active" aria-current="page">
                        {{ $page }}
                    </span>
                @else
                    <a class="fb-page-number" href="{{ $paginator->url($page) }}">
                        {{ $page }}
                    </a>
                @endif
            @endfor

            @if ($end < $last)
                @if ($end < $last - 1)
                    <span class="fb-page-dots">...</span>
                @endif
                <a class="fb-page-number" href="{{ $paginator->url($last) }}">
                    {{ $last }}
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a class="fb-page-arrow next" href="{{ $paginator->nextPageUrl() }}">
                    Berikutnya ›
                </a>
            @else
                <span class="fb-page-arrow disabled">Berikutnya ›</span>
            @endif
        </div>
    </nav>
@endif
