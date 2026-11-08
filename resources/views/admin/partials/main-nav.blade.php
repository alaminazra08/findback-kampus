<nav class="fb-nav" aria-label="Menu utama administrator">
    <a class="fb-nav-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}"
        href="{{ route('admin.dashboard') }}" @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif><span
            class="fb-nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="m3 10 9-7 9 7v10H15v-7H9v7H3z" />
            </svg></span><span class="fb-nav-text">Dashboard</span></a>
    <a class="fb-nav-link {{ request()->routeIs('admin.reports.verification*') ? 'is-active' : '' }}"
        href="{{ route('admin.reports.verification') }}"
        @if (request()->routeIs('admin.reports.verification*')) aria-current="page" @endif><span class="fb-nav-icon"><svg viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"
                aria-hidden="true">
                <circle cx="12" cy="12" r="9" />
                <path d="m8 12 3 3 5-6" />
            </svg></span><span class="fb-nav-text">Verifikasi Laporan</span></a>
    <a class="fb-nav-link {{ request()->routeIs('admin.items.*', 'admin.reports.lost', 'admin.reports.found', 'admin.chat.*') ? 'is-active' : '' }}"
        href="{{ route('admin.items.index') }}" @if (request()->routeIs('admin.items.*', 'admin.reports.lost', 'admin.reports.found', 'admin.chat.*')) aria-current="page" @endif><span
            class="fb-nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="m12 3 9 5-9 5-9-5zM3 8v9l9 5 9-5V8M12 13v9" />
            </svg></span><span class="fb-nav-text">Laporan Barang</span></a>
    <a class="fb-nav-link {{ request()->routeIs('admin.matching.*') ? 'is-active' : '' }}"
        href="{{ route('admin.matching.index') }}" @if (request()->routeIs('admin.matching.*')) aria-current="page" @endif><span
            class="fb-nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="10" cy="10" r="7" />
                <path d="m15 15 6 6" />
            </svg></span><span class="fb-nav-text">Pencocokan Barang</span></a>
    <a class="fb-nav-link {{ request()->routeIs('admin.claims.*') ? 'is-active' : '' }}"
        href="{{ route('admin.claims.index') }}" @if (request()->routeIs('admin.claims.*')) aria-current="page" @endif><span
            class="fb-nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M4 7h16v14H4zM8 7V4h8v3m-8 7 3 3 5-6" />
            </svg></span><span class="fb-nav-text">Klaim & Pengembalian</span></a>
    <a class="fb-nav-link {{ request()->routeIs('admin.statistics.*') ? 'is-active' : '' }}"
        href="{{ route('admin.statistics.index') }}" @if (request()->routeIs('admin.statistics.*')) aria-current="page" @endif><span
            class="fb-nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M4 20V10m5 10V4m5 16v-7m5 7V8M2 20h20" />
            </svg></span><span class="fb-nav-text">Laporan & Statistik</span></a>
</nav>
