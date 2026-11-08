@if ($paginator->hasPages())
    <nav class="cu-pages" aria-label="Halaman hasil">
        @if ($paginator->previousPageUrl())
            <a class="cu-btn light" href="{{ $paginator->previousPageUrl() }}">← Sebelumnya</a>
        @endif
        <span>
            Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}</span>
        @if ($paginator->nextPageUrl())
            <a class="cu-btn light" href="{{ $paginator->nextPageUrl() }}">Berikutnya →</a>
        @endif
    </nav>
@endif
