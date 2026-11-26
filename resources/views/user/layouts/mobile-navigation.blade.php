<nav class="cu-mobile-nav" aria-label="Navigasi mobile">
    <a data-cu-tab="home" href="{{ route('dashboard') }}"
       @if (request()->routeIs('dashboard')) aria-current="page" @endif>
        <svg class="cu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" aria-hidden="true">
            <path d="m3 10 9-7 9 7v11h-6v-7H9v7H3z" />
        </svg>
        <span>Beranda</span>
    </a>
    <button type="button" data-cu-tab="report" id="cu-mobile-report-open"
            aria-haspopup="dialog" aria-controls="cu-mobile-report" aria-expanded="false"
            @if (request()->routeIs('user.reports.*create')) aria-current="true" @endif>
        <svg class="cu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true">
            <rect x="4" y="4" width="16" height="16" rx="4" />
            <path d="M8 12h8m-4-4v8" />
        </svg>
        <span>Lapor</span>
    </button>
    <a data-cu-tab="activity" href="{{ route('user.reports.index') }}"
       @if (request()->routeIs('user.reports.index', 'user.claims.*')) aria-current="page" @endif>
        @include('user.components.icon', ['icon' => 'clock'])
        <span>Aktivitas</span>
    </a>
    <a data-cu-tab="profile" href="{{ route('user.account.index') }}"
       @if (request()->routeIs('user.account.*', 'user.profile.*')) aria-current="page" @endif>
        <svg class="cu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="7" r="3.5" />
            <path d="M5 21v-2a7 7 0 0 1 14 0v2z" />
        </svg>
        <span>Akun</span>
    </a>
</nav>

<dialog id="cu-mobile-report" class="cu-mobile-report" aria-labelledby="cu-mobile-report-title">
    <section class="cu-mobile-report-content">
        <header>
            <h2 id="cu-mobile-report-title">Lapor barang</h2>
            <form method="dialog">
                <button type="submit" aria-label="Tutup pilihan laporan" autofocus><svg class="cu-sheet-symbol" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18" /></svg></button>
            </form>
        </header>
        <a href="{{ route('user.reports.lost.create') }}">
            <span class="cu-sheet-option-icon" aria-hidden="true">
                @include('user.components.icon', ['icon' => 'search'])
            </span>
            <span>Laporkan barang hilang</span>
        <span class="cu-sheet-arrow" aria-hidden="true"><svg class="cu-sheet-symbol" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 6 6 6-6 6" /></svg></span>
        </a>
        <a href="{{ route('user.reports.found.create') }}">
            <span class="cu-sheet-option-icon" aria-hidden="true">
                @include('user.components.icon', ['icon' => 'box'])
            </span>
            <span>Laporkan barang ditemukan</span>
        <span class="cu-sheet-arrow" aria-hidden="true"><svg class="cu-sheet-symbol" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 6 6 6-6 6" /></svg></span>
        </a>
    </section>
</dialog>
