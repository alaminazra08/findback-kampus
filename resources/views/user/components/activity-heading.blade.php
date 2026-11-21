{{-- HEADER AKTIVITAS SAYA --}}
<div class="fb-activity-heading">
    <div class="fb-activity-heading-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"
            aria-hidden="true">
            <path d="M3 7.5A2.5 2.5 0 0 1 5.5 5H10l2 2h6.5A2.5 2.5 0 0 1 21 9.5l-1.5 9A2.5 2.5 0 0 1 17 20.5H5a2.5 2.5 0 0 1-2.5-2.5z"/>
            <path d="M3.5 9h16.8"/>
        </svg>
    </div>

    <div class="fb-activity-heading-text">
        <h1>Aktivitas Saya</h1>
        <p>Pantau perkembangan laporan dan klaim barangmu.</p>
    </div>
</div>

{{-- TAB AKTIVITAS --}}
<nav class="fb-activity-tabs" aria-label="Navigasi aktivitas">
    <a @class(['active' => $activityTab === 'reports']) @if ($activityTab === 'reports') aria-current="page" @endif href="{{ route('user.reports.index') }}" >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
            aria-hidden="true">
            <path d="M6 3h9l4 4v14H6z"/>
            <path d="M14 3v5h5M9 12h7M9 16h7"/>
        </svg>
        <span>Riwayat Laporan</span>
    </a>

    <a @class(['active' => $activityTab === 'claims']) @if ($activityTab === 'claims') aria-current="page" @endif href="{{ route('user.claims.index') }}" >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
            aria-hidden="true">
            <path d="m20.5 13.5-7.5 7.5-10-10 7.5-7.5a2 2 0 0 1 2.8 0l7.2 7.2a2 2 0 0 1 0 2.8Z"/>
            <circle cx="8.5" cy="8.5" r="1.3"/>
        </svg>
        <span>Pengajuan Klaim</span>
    </a>
</nav>
