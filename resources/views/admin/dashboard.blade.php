<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $adminSettings['site_description'] }}">
    <meta name="theme-color" content="#121114">
    <title>FindBack Kampus</title>
    <script src="{{ asset('js/admin/sidebar-prepaint.js') }}"></script>
    <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png">
    <link rel="stylesheet" href="{{ asset('css/admin/admin-dashboard.css') }}?v=40">
    <link rel="stylesheet" href="{{ asset('css/admin/quick-actions-base.css') }}?v=17">
    <link rel="stylesheet" href="{{ asset('css/admin/quick-actions-compact.css') }}?v=18">
    <link rel="stylesheet" href="{{ asset('css/admin/quick-actions-layout.css') }}?v=19">
    {{-- Sidebar visual: ukuran, posisi, dan ritme identik di semua halaman Admin. --}}
    <link rel="stylesheet" href="{{ asset('css/pages/admin/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/admin-workflow.css') }}?v=1">
    {{-- FINDBACK_SIDEBAR_V43 --}}
    <link rel="stylesheet" href="{{ asset('css/admin/sidebar-theme.css') }}?v=43">
    {{-- FINDBACK_SIDEBAR_V44 --}}
    <link rel="stylesheet" href="{{ asset('css/admin/sidebar-layout.css') }}?v=44">
    <link rel="stylesheet" href="{{ asset('css/admin/sidebar-prepaint.css') }}?v=48">
    <link rel="stylesheet" href="{{ asset('css/report-cycle.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('css/shared/view-utilities.css') }}">
    <script src="{{ asset('js/shared/view-interactions.js') }}" defer></script>
    <link rel="stylesheet" href="{{ asset('css/admin/findback-pm-detail-date.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/admin/header-consistency.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('css/shared/responsive-all-devices.css') }}?v=20261111">
    <link rel="stylesheet" href="{{ asset('css/pages/admin/dashboard-report-cards.css') }}?v=20261111">
    <link rel="stylesheet" href="{{ asset('css/pages/admin/dashboard-recent-consistency.css') }}?v=1">
    <!-- FINDBACK_UI_TEXT_KONSISTEN_V1 -->
    <link rel="stylesheet" href="{{ asset('css/shared/ui-text-consistency.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/admin/brand-polish.css') }}?v=1">
<!-- FINDBACK_TYPOGRAPHY_V1 -->
<link rel="stylesheet" href="{{ asset('css/shared/typography.css') }}">
</head>

<body class="fb-responsive fb-admin-body">
    <div class="fb-admin-shell">
        <aside class="fb-sidebar" id="fb-sidebar" aria-label="Navigasi administrator">
            <button type="button" class="fb-v43-close" aria-label="Tutup navigasi" data-close-admin-nav>&#215;</button>
            @include('admin.partials.brand')

            <div class="fb-sidebar-nav-scroll" aria-label="Daftar menu administrator">
                <div class="fb-sidebar-divider" aria-hidden="true"></div>
                <p class="fb-nav-label">MENU UTAMA</p>
                @include('admin.partials.main-nav')

                <p class="fb-nav-label fb-nav-label-secondary">PENGELOLAAN</p>
                <nav class="fb-nav" aria-label="Pengelolaan administrator">
                    {{-- Manajemen Akun — menu aktif bila route tersedia --}}
                    @if (\Illuminate\Support\Facades\Route::has('admin.users.index'))
                        <a href="{{ route('admin.users.index') }}"
                            class="fb-nav-link {{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}"
                            @if (request()->routeIs('admin.users.*')) aria-current="page" @endif>
                            <span class="fb-nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"
                                    aria-hidden="true">
                                    <circle cx="9" cy="8" r="3" />
                                    <path d="M3 20v-2a6 6 0 0 1 12 0v2M17 6a3 3 0 0 1 0 6m1 4a5 5 0 0 1 3 4" />
                                </svg></span>
                            <span class="fb-nav-text">Manajemen Akun</span>
                        </a>
                    @else
                        <span class="fb-nav-link is-disabled" aria-disabled="true" title="Fitur segera hadir"><span
                                class="fb-nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"
                                    aria-hidden="true">
                                    <circle cx="9" cy="8" r="3" />
                                    <path d="M3 20v-2a6 6 0 0 1 12 0v2M17 6a3 3 0 0 1 0 6m1 4a5 5 0 0 1 3 4" />
                                </svg></span><span class="fb-nav-text">Manajemen Akun</span></span>
                    @endif
                    {{-- Pengaturan — aktif apabila route terdaftar --}}
                    @if (\Illuminate\Support\Facades\Route::has('admin.settings.index'))
                        <a href="{{ route('admin.settings.index') }}"
                            class="fb-nav-link {{ request()->routeIs('admin.settings.*') ? 'is-active' : '' }}"
                            @if (request()->routeIs('admin.settings.*')) aria-current="page" @endif>
                            <span class="fb-nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"
                                    aria-hidden="true">
                                    <circle cx="12" cy="12" r="3" />
                                    <path
                                        d="M19 13.7 21 12l-2-1.7-.7-2.5-2.5-.7L14 5l-2 1-2-1-1.8 2.1-2.5.7L5 10.3 3 12l2 1.7.7 2.5 2.5.7L10 19l2-1 2 1 1.8-2.1 2.5-.7z" />
                                </svg></span>
                            <span class="fb-nav-text">Pengaturan</span>
                        </a>
                    @else
                        <span class="fb-nav-link is-disabled" aria-disabled="true" title="Fitur segera hadir"><span
                                class="fb-nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"
                                    aria-hidden="true">
                                    <circle cx="12" cy="12" r="3" />
                                    <path
                                        d="M19 13.7 21 12l-2-1.7-.7-2.5-2.5-.7L14 5l-2 1-2-1-1.8 2.1-2.5.7L5 10.3 3 12l2 1.7.7 2.5 2.5.7L10 19l2-1 2 1 1.8-2.1 2.5-.7z" />
                                </svg></span><span class="fb-nav-text">Pengaturan</span></span>
                    @endif
                </nav>

            </div> {{-- Akhir area scroll menu --}}

            <div class="fb-sidebar-bottom">

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="fb-sidebar-logout" type="submit"><svg viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"
                            aria-hidden="true">
                            <path d="M10 4H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h5M14 8l4 4-4 4m-8-4h12" />
                        </svg><span>Keluar dari Akun</span></button>
                </form>
            </div>

        </aside>

        <div class="fb-sidebar-backdrop" id="fb-sidebar-backdrop" hidden></div>

        <div class="fb-workspace">
            <header class="fb-topbar">
                <div class="fb-topbar-left">
                    <button class="fb-menu-toggle" type="button" aria-label="Buka menu" aria-controls="fb-sidebar"
                        aria-expanded="false" id="fb-menu-toggle"><svg class="fb-hamburger-svg" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M4 6h16M4 12h16M4 18h16"/></svg></button>
                    <div class="fb-mobile-brand" aria-label="{{ $adminSettings['site_name'] }}, panel admin">
                        <strong>FindBack</strong>
                        <small>ADMIN PANEL</small>
                    </div>
                    <div class="fb-breadcrumb">Administrasi <span>/</span> <strong>Dashboard</strong></div>
                </div>
                <div class="fb-topbar-right">
                    @include('admin.partials.notification-bell')
                    @include('admin.partials.account')
                </div>
            </header>

            <main class="fb-main" data-fb-nav-page="dashboard">
                <div class="fb-page-heading">
                    <div>
                        <p class="fb-eyebrow">ADMINISTRASI · FINDBACK KAMPUS</p>
                        <h1>Dashboard <span>Admin</span></h1>
                        {{-- Deskripsi dinamis dari Admin > Pengaturan Sistem --}}
                        <p class="fb-heading-text fb-wrap-anywhere">{{ trim((string) ($adminSettings['site_description'] ?? '')) ?: 'Pusat pengelolaan laporan dan aktivitas barang kampus.' }}</p>
                    </div>
                    <span class="fb-date-label fb-date-premium fb-pm-date" id="fb-live-date"
                        aria-label="Tanggal hari ini">
                        <svg class="fb-date-premium-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="5" width="18" height="16" rx="2" />
                            <path d="M7 3v4M17 3v4M3 10h18" />
                        </svg>
                        <span
                            class="fb-date-text">{{ now()->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d F Y') }}</span>
                    </span>
                </div>

                @php
                    // Controller dapat memasok $reportAnalytics melalui FindBackReportAnalytics::snapshot().
                    // Tanpa modul laporan, UI tetap aman dan tidak menampilkan angka fiktif.
                    $analytics = $reportAnalytics ?? [
                        'available' => false,
                        'total' => 0,
                        'lost' => 0,
                        'found' => 0,
                        'claim' => 0,
                        'returned' => 0,
                        'labels' => [],
                        'lostTrend' => [],
                        'foundTrend' => [],
                        'recent' => [],
                        'updatedAt' => null,
                    ];
                    $analyticsReady = (bool) ($analytics['available'] ?? false);
                @endphp
                {{-- FINDBACK_DASHBOARD_V40 --}}
                <section class="fb-analytics-intro" aria-label="Ringkasan dashboard">
                    <div><span class="fb-analytics-kicker">✦ PUSAT KENDALI FINDBACK</span>
                        <h2>Selamat datang kembali!</h2>
                        <p>Kelola pekerjaan hari ini dengan lebih mudah dan terarah.</p>
                    </div>

                </section>

                <section class="fb-analytics-section" aria-labelledby="fb-report-title">
                    <div class="fb-section-title fb-analytics-title">
                        <div>
                            <h2 id="fb-report-title">Ringkasan Laporan Barang</h2>
                            <p>Statistik operasional berdasarkan data tersimpan</p>
                        </div><span
                            class="fb-data-status {{ $analyticsReady ? 'is-ready' : '' }}"><i></i>{{ $analyticsReady ? 'Data database' : 'Menunggu modul laporan' }}</span>
                    </div>
                    <div class="fb-report-cards">
                        <article class="fb-report-card is-lost">
                            <div class="fb-report-card-top"><span class="fb-report-icon"><svg viewBox="0 0 24 24"
                                        width="21" height="21" fill="none" stroke="currentColor"
                                        stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                                        aria-hidden="true">
                                        <circle cx="11" cy="11" r="7" />
                                        <path d="m16 16 5 5" />
                                    </svg></span><span class="fb-report-card-label">Barang Hilang</span></div>
                            <strong>{{ $analyticsReady ? number_format($analytics['lost'], 0, ',', '.') : '—' }}</strong><small>Laporan
                                barang hilang</small>
                        </article>
                        <article class="fb-report-card is-found">
                            <div class="fb-report-card-top"><span class="fb-report-icon"><svg viewBox="0 0 24 24"
                                        width="21" height="21" fill="none" stroke="currentColor"
                                        stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                                        aria-hidden="true">
                                        <path d="M20 6 9 17l-5-5" />
                                    </svg></span><span class="fb-report-card-label">Barang Ditemukan</span></div>
                            <strong>{{ $analyticsReady ? number_format($analytics['found'], 0, ',', '.') : '—' }}</strong><small>Laporan
                                barang ditemukan</small>
                        </article>
                        <article class="fb-report-card is-claim">
                            <div class="fb-report-card-top"><span class="fb-report-icon"><svg viewBox="0 0 24 24"
                                        width="21" height="21" fill="none" stroke="currentColor"
                                        stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                                        aria-hidden="true">
                                        <circle cx="12" cy="12" r="9" />
                                        <path d="M12 7v5l3 2" />
                                    </svg></span><span class="fb-report-card-label">Proses Klaim</span></div>
                            <strong>{{ $analyticsReady ? number_format($analytics['claim'], 0, ',', '.') : '—' }}</strong><small>Menunggu
                                penyelesaian klaim</small>
                        </article>
                        <article class="fb-report-card is-returned">
                            <div class="fb-report-card-top"><span class="fb-report-icon"><svg viewBox="0 0 24 24"
                                        width="21" height="21" fill="none" stroke="currentColor"
                                        stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                                        aria-hidden="true">
                                        <path d="M20 6 9 17l-5-5" />
                                        <path d="M4 21h16" />
                                    </svg></span><span class="fb-report-card-label">Berhasil Dikembalikan</span></div>
                            <strong>{{ $analyticsReady ? number_format($analytics['returned'], 0, ',', '.') : '—' }}</strong><small>Laporan
                                berstatus dikembalikan</small>
                        </article>
                    </div>
                </section>

                {{-- Aksi cepat: hanya aktif jika route modul terkait telah tersedia. --}}
                <section class="fb-quick-section" aria-labelledby="fb-quick-title">
                    <div class="fb-section-title fb-quick-heading">
                        <div>
                            <h2 id="fb-quick-title">Aksi Cepat</h2>
                            <p>Akses pekerjaan prioritas administrator</p>
                        </div>
                        <span class="fb-quick-caption">PINTASAN ADMINISTRASI</span>
                    </div>
                    @php
                        $quickActions = [
                            [
                                'title' => 'Verifikasi Laporan',
                                'description' => 'Tinjau laporan barang yang menunggu persetujuan.',
                                'icon' => 'check',
                                'route' => 'admin.reports.verification',
                            ],
                            [
                                'title' => 'Klaim & Pengembalian',
                                'description' => 'Tinjau klaim, bukti kepemilikan, dan serah terima barang.',
                                'icon' => 'shield',
                                'route' => 'admin.claims.index',
                            ],
                            [
                                'title' => 'Laporan Barang',
                                'description' => 'Telusuri laporan barang hilang dan ditemukan.',
                                'icon' => 'package',
                                'route' => 'admin.items.index',
                            ],
                        ];
                    @endphp
                    <div class="fb-quick-grid">
                        @foreach ($quickActions as $action)
                            @php $actionReady = \Illuminate\Support\Facades\Route::has($action['route']); @endphp
                            @if ($actionReady)
                                <a class="fb-quick-card" href="{{ route($action['route']) }}">
                                @else
                                    <div class="fb-quick-card is-unavailable" aria-disabled="true">
                            @endif
                            <span class="fb-quick-icon" aria-hidden="true">
                                @if ($action['icon'] === 'check')
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3.5" y="3.5" width="17" height="17" rx="5" />
                                        <path d="m8 12 2.7 2.7L16.5 9" />
                                    </svg>
                                @elseif ($action['icon'] === 'shield')
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m12 2 8 4v6c0 5-3.4 8.3-8 10-4.6-1.7-8-5-8-10V6z" />
                                        <path d="m8.5 12 2.3 2.3 4.8-5" />
                                    </svg>
                                @else
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m3 7 9-4 9 4v10l-9 4-9-4z" />
                                        <path d="m3 7 9 4 9-4M12 11v10" />
                                    </svg>
                                @endif
                            </span>
                            <span class="fb-quick-copy">
                                <strong>{{ $action['title'] }}</strong>
                                <small>{{ $action['description'] }}</small>

                            </span>
                            @if ($actionReady)
                                <span class="fb-quick-arrow fb-quick-arrow-general" aria-hidden="true"><svg
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"
                                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M5 12h14M12 5l7 7-7 7" />
                                    </svg></span>
                            @endif
                            @if ($actionReady)
                                </a>
                            @else
                    </div>
                    @endif
                    @endforeach
        </div>
        </section>

        <div class="fb-analytics-grid">
            <section class="fb-panel fb-trend-panel" aria-labelledby="fb-trend-title">
                <div class="fb-panel-head">
                    <div>
                        <h2 id="fb-trend-title">Tren Laporan Barang</h2>
                        <p>Perkembangan laporan selama 7 hari terakhir</p>
                    </div><span class="fb-period-tag">7 hari terakhir</span>
                </div>
                <div class="fb-trend-chart" id="fb-trend-chart" role="img"
                    aria-label="Grafik tren laporan hilang dan ditemukan"></div>
                <div class="fb-chart-legend"><span><i class="fb-legend-lost"></i>Barang hilang</span><span><i
                            class="fb-legend-found"></i>Barang ditemukan</span></div>
            </section>
            <section class="fb-panel fb-distribution-panel" aria-labelledby="fb-dist-title">
                <div class="fb-panel-head">
                    <div>
                        <h2 id="fb-dist-title">Distribusi Laporan</h2>
                        <p>Komposisi seluruh laporan barang</p>
                    </div>
                </div>
                <div class="fb-distribution-body">
                    <div class="fb-donut" id="fb-report-donut">
                        <div class="fb-donut-center">
                            <strong>{{ $analyticsReady ? number_format($analytics['total'], 0, ',', '.') : '—' }}</strong><small>Total
                                laporan</small>
                        </div>
                    </div>
                    <div class="fb-dist-legend">
                        <div><i class="fb-legend-lost"></i><span>Barang
                                Hilang</span><b>{{ $analyticsReady ? $analytics['lost'] : '—' }}</b></div>
                        <div><i class="fb-legend-found"></i><span>Barang
                                Ditemukan</span><b>{{ $analyticsReady ? $analytics['found'] : '—' }}</b></div>
                    </div>
                </div>
            </section>
        </div>

        <section class="fb-panel fb-recent-reports" aria-labelledby="fb-recent-reports-title">
            <div class="fb-panel-head">
                <div>
                    <h2 id="fb-recent-reports-title">Laporan Terbaru</h2>
                    <p>Aktivitas pelaporan barang terkini</p>
                </div><span class="fb-panel-pill">{{ count($analytics['recent'] ?? []) }} laporan</span>
            </div>
            {{-- Mobile card layout follows the Statistics page; desktop table stays intact. --}}
            <div class="fb-dashboard-mobile-reports" aria-label="Daftar laporan terbaru">
                @forelse(($analytics['recent'] ?? []) as $report)
                    <article class="fb-dashboard-mobile-card">
                        <div class="fb-dashboard-mobile-top">
                            <span class="fb-dashboard-mobile-photo">
                                @if (!empty($report['photo']))
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($report['photo']) }}"
                                        alt="Foto barang {{ $report['name'] ?? '' }}" loading="lazy" decoding="async"
                                        data-image-fallback="hidden">
                                @endif
                                <span class="fb-dashboard-mobile-placeholder" @if (!empty($report['photo'])) hidden @endif aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8" cy="8" r="1.5"/><path d="m3 17 6-6 4 4 3-3 5 5"/></svg>
                                </span>
                            </span>
                            <div class="fb-dashboard-mobile-title">
                                <a href="{{ route('admin.items.show', $report['id']) }}"><strong>{{ $report['name'] ?? '—' }}</strong></a>
                                <span class="fb-report-type {{ ($report['type'] ?? '') === 'found' ? 'is-found' : 'is-lost' }}">{{ ($report['type'] ?? '') === 'found' ? 'Ditemukan' : 'Hilang' }}</span>
                            </div>
                        </div>
                        <div class="fb-dashboard-mobile-details">
                            <span>Lokasi: <strong>{{ $report['location'] ?? '—' }}</strong></span>
                            <span>{{ $report['date'] ?? '—' }}</span>
                        </div>
                        <div class="fb-dashboard-mobile-footer">
                            <span class="fb-report-status status-{{ $report['status'] ?? '' }}">{{ \App\Support\WorkflowStatus::reports()[$report['status'] ?? ''] ?? ($report['status'] ?? '—') }}</span>
                        </div>
                    </article>
                @empty
                    <p class="fb-dashboard-mobile-empty">Belum ada laporan untuk ditampilkan.</p>
                @endforelse
            </div>
            <div class="fb-table-scroll">
                <table class="fb-user-table fb-report-table">
                    <thead>
                        <tr>
                            <th>Nama Barang</th>
                            <th>Jenis</th>
                            <th>Lokasi</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($analytics['recent'] ?? []) as $report)
                            <tr class="fb-v35-report-row">
                                <td data-label="Nama Barang">
                                    <div class="fb-v37-item"><span class="fb-v37-photo">
                                            @if (!empty($report['photo']))
                                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($report['photo']) }}"
                                                    alt="Foto barang {{ $report['name'] ?? '' }}" loading="lazy"
                                                    decoding="async" data-image-fallback="hidden">
                                            @endif
                                            <span class="fb-v37-photo-fallback"
                                                @if (!empty($report['photo'])) hidden @endif
                                                aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="1.6">
                                                    <rect x="3" y="3" width="18" height="18" rx="3" />
                                                    <circle cx="8" cy="8" r="1.5" />
                                                    <path d="m3 17 6-6 4 4 3-3 5 5" />
                                                </svg></span>
                                        </span><a
                                            href="{{ route('admin.items.show', $report['id']) }}"><strong>{{ $report['name'] ?? '—' }}</strong></a>
                                    </div>
                                </td>
                                <td data-label="Jenis"><span
                                        class="fb-report-type {{ ($report['type'] ?? '') === 'found' ? 'is-found' : 'is-lost' }}">{{ ($report['type'] ?? '') === 'found' ? 'Ditemukan' : 'Hilang' }}</span>
                                </td>
                                <td data-label="Lokasi">{{ $report['location'] ?? '—' }}</td>
                                <td data-label="Tanggal">{{ $report['date'] ?? '—' }}</td>
                                <td data-label="Status"><span
                                        class="fb-report-status status-{{ $report['status'] ?? '' }}">{{ \App\Support\WorkflowStatus::reports()[$report['status'] ?? ''] ?? ($report['status'] ?? '—') }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="fb-empty">
                                    <div class="fb-empty-state"><span>▤</span><strong>Belum ada laporan untuk
                                            ditampilkan</strong>
                                        <p>{{ $analyticsReady ? 'Laporan baru akan muncul secara otomatis setelah tercatat.' : 'Hubungkan modul laporan barang untuk mengaktifkan statistik dan grafik.' }}
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="fb-dashboard-more-wrap">
                <a href="{{ route('admin.statistics.index') }}" class="fb-dashboard-more-link" aria-label="Lihat seluruh laporan di halaman Laporan dan Statistik">
                    Lihat Statistik Laporan <svg class="fb-dashboard-more-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
                </a>
            </div>
        </section>

        <section class="fb-compact-users" aria-labelledby="fb-users-title">
            <div class="fb-section-title fb-users-heading">
                <div>
                    <h2 id="fb-users-title">Ringkasan Pengguna</h2>
                    <p>Monitoring akun yang terdaftar di {{ $adminSettings['site_name'] }}</p>
                </div>
                <span class="fb-users-tag"><span aria-hidden="true"></span> Data akun</span>
            </div>
            <div class="fb-user-summary">
                <article class="fb-user-metric">
                    <div class="fb-user-metric-head">
                        <span>Total Akun</span>
                        <span class="fb-user-metric-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                stroke-linejoin="round">
                                <circle cx="9" cy="8" r="3" />
                                <path d="M3 20v-2a6 6 0 0 1 12 0v2M17 6a3 3 0 0 1 0 6m1 4a5 5 0 0 1 3 4" />
                            </svg></span>
                    </div>
                    <strong>{{ number_format($totalUsers, 0, ',', '.') }}</strong>
                    <small>Seluruh akun terdaftar</small>
                </article>
                <article class="fb-user-metric">
                    <div class="fb-user-metric-head">
                        <span>Pengguna Biasa</span>
                        <span class="fb-user-metric-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                stroke-linejoin="round">
                                <circle cx="12" cy="8" r="3.5" />
                                <path d="M5 21v-2a7 7 0 0 1 14 0v2" />
                            </svg></span>
                    </div>
                    <strong>{{ number_format($totalRegularUsers, 0, ',', '.') }}</strong>
                    <small>Akun dengan role user</small>
                </article>
                <article class="fb-user-metric">
                    <div class="fb-user-metric-head">
                        <span>Administrator</span>
                        <span class="fb-user-metric-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="m12 2 8 4v6c0 5-3.4 8.3-8 10-4.6-1.7-8-5-8-10V6z" />
                                <path d="m9 12 2 2 4-4" />
                            </svg></span>
                    </div>
                    <strong>{{ number_format($totalAdmins, 0, ',', '.') }}</strong>
                    <small>Akun pengelola sistem</small>
                </article>
            </div>
        </section>
        {{-- Siapkan payload sebelum @json agar parser Blade tidak salah membaca array bertingkat. --}}
        @php
            $analyticsPayload = [
                'available' => $analyticsReady,
                'labels' => $analytics['labels'] ?? [],
                'lostTrend' => $analytics['lostTrend'] ?? [],
                'foundTrend' => $analytics['foundTrend'] ?? [],
                'lost' => $analytics['lost'] ?? 0,
                'found' => $analytics['found'] ?? 0,
            ];
        @endphp
        <script type="application/json" id="fb-analytics-json">@json($analyticsPayload)</script>
        <footer class="fb-footer">© {{ date('Y') }} {{ $adminSettings['site_name'] }} <span>·</span>
            {{ $adminSettings['institution_name'] }}</footer>
        </main>
    </div>
    </div>
    <script src="{{ asset('js/admin/admin-notifications.js') }}?v=2" defer></script>
    <script src="{{ asset('js/admin/sidebar-mobile.js') }}?v=20261111" defer></script>
    {{-- Animasi klik logo saja; menu lain tetap menggunakan navigasi normal. --}}
    <script src="{{ asset('js/admin/sidebar-branding.js') }}?v=49" defer></script>
    <script src="{{ asset('js/shared/responsive-viewport.js') }}?v=20261111" defer></script>
    {{-- Smart Navigation: sama dengan layout Verifikasi dan Laporan Barang. --}}
    <script src="{{ asset('js/admin/smart-navigation-session.js') }}?v=1" defer></script>
    <script src="{{ asset('js/admin/admin-analytics.js') }}?v=2" defer></script>
    <script src="{{ asset('js/admin/dashboard-date.js') }}?v=2" defer></script>
    <script src="{{ asset('js/pages/admin/reports/verification/fast-tabs.js') }}?v=3" defer></script>
    <script src="{{ asset('js/pages/admin/reports/verification/return-context.js') }}?v=2" defer></script>
    <script src="{{ asset('js/admin/admin-items-upload.js') }}?v=2" defer></script>
    <script src="{{ asset('js/admin/items-filter-ajax-scroll.js') }}?v=2" defer></script>
    <script src="{{ asset('js/pages/admin/matching/filter-navigation.js') }}?v=3" defer></script>
    <script src="{{ asset('js/admin/claims-filter-ajax-scroll.js') }}?v=2" defer></script>
    <script src="{{ asset('js/pages/admin/statistics/index.js') }}?v=2" defer></script>
    <script src="{{ asset('js/admin/users-filter-navigation.js') }}?v=2" defer></script>
    <script src="{{ asset('js/admin/settings-score.js') }}?v=2" defer></script>
    <script src="{{ asset('js/admin/dashboard-verification-navigation.js') }}?v=7" defer
        data-dashboard="{{ route('admin.dashboard') }}"
        data-verification="{{ route('admin.reports.verification') }}"
        data-items="{{ route('admin.items.index') }}"
        data-matching="{{ route('admin.matching.index') }}"
        data-claims="{{ route('admin.claims.index') }}"
        data-statistics="{{ route('admin.statistics.index') }}"
        data-users="{{ route('admin.users.index') }}"
        data-settings="{{ route('admin.settings.index') }}"></script>
    {{-- Smart Save harus sudah siap sebelum navigasi AJAX menuju Pengaturan. --}}
    <script src="{{ asset('js/admin/settings-smart-save.js') }}?v=10" defer></script>
</body>

</html>
