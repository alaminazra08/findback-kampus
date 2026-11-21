@if ($detailUrl)
    <a class="fb-admin-detail-action" href="{{ $detailUrl }}">
@else
    <button class="fb-admin-detail-action" type="submit">
@endif
    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor"
        stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M2 12s3.6-6 10-6 10 6 10 6-3.6 6-10 6S2 12 2 12Z" />
        <circle cx="12" cy="12" r="3" />
    </svg>
    <span>Lihat Detail</span>
@if ($detailUrl)
    </a>
@else
    </button>
@endif
