@extends('admin.layouts.items')

@section('breadcrumb', 'Dashboard')

@section('page-styles')
    <link rel="stylesheet" href="{{ asset('css/pages/admin/dashboard-charts.css') }}?v=3">
    <link rel="stylesheet" href="{{ asset('css/pages/admin/summary-emblems.css') }}?v=4">
    <link rel="stylesheet" href="{{ asset('css/admin/quick-actions-base.css') }}?v=17">
    <link rel="stylesheet" href="{{ asset('css/admin/quick-actions-compact.css') }}?v=18">
    <link rel="stylesheet" href="{{ asset('css/admin/quick-actions-layout.css') }}?v=19">
    <link rel="stylesheet" href="{{ asset('css/report-cycle.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('css/admin/findback-pm-detail-date.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/admin/header-consistency.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('css/pages/admin/dashboard-report-cards.css') }}?v=20261111">
    <link rel="stylesheet" href="{{ asset('css/pages/admin/dashboard-recent-consistency.css') }}?v=6">
    <link rel="stylesheet" href="{{ asset('css/pages/admin/dashboard-date.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/pages/admin/dashboard-greeting.css') }}?v=4">
@endsection

@section('content')
                <div class="fb-page-heading fb-dashboard-heading">
                    <p class="fb-eyebrow">ADMINISTRASI · FINDBACK KAMPUS</p>
                    <div class="fb-dashboard-title-row">
                        <h1>Dashboard <span>Admin</span></h1>
                        <span class="fb-date-label fb-date-premium fb-pm-date" id="fb-live-date"
                            aria-label="Tanggal hari ini">
                            <svg class="fb-date-premium-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="3" y="5" width="18" height="16" rx="2" />
                                <path d="M7 3v4M17 3v4M3 10h18" />
                            </svg>
                            <span
                                class="fb-date-text">{{ now()->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d F Y') }}</span>
                            <span class="fb-date-text-short">{{ now()->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j M Y') }}</span>
                        </span>
                    </div>
                    <p class="fb-dashboard-description">FindBack • Sistem Informasi Kehilangan dan Penemuan Barang</p>
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
                <section class="fb-analytics-section" aria-labelledby="fb-report-title">
                    <div class="fb-section-title fb-analytics-title">
                        <h2 id="fb-report-title">Ringkasan Laporan Barang</h2>
                        @unless ($analyticsReady)
                            <span class="fb-data-status">Menunggu modul laporan</span>
                        @endunless
                    </div>
                    <div class="fb-report-cards">
                        <article class="fb-report-card fb-summary-emblem-card is-lost">
                            <div class="fb-report-card-top"><span class="fb-summary-emblem"><svg viewBox="0 0 24 24"
                                        width="21" height="21" fill="none" stroke="currentColor"
                                        stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                                        aria-hidden="true">
                                        <circle cx="11" cy="11" r="7" />
                                        <path d="m16 16 5 5" />
                                    </svg></span><span class="fb-report-card-label">Barang Hilang</span></div>
                            <strong>{{ $analyticsReady ? number_format($analytics['lost'], 0, ',', '.') : '—' }}</strong><small>Laporan
                                barang hilang</small>
                        </article>
                        <article class="fb-report-card fb-summary-emblem-card is-found">
                            <div class="fb-report-card-top"><span class="fb-summary-emblem"><svg viewBox="0 0 24 24"
                                        width="21" height="21" fill="none" stroke="currentColor"
                                        stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                                        aria-hidden="true">
                                        <path d="M20 6 9 17l-5-5" />
                                    </svg></span><span class="fb-report-card-label">Barang Ditemukan</span></div>
                            <strong>{{ $analyticsReady ? number_format($analytics['found'], 0, ',', '.') : '—' }}</strong><small>Laporan
                                barang ditemukan</small>
                        </article>
                        <article class="fb-report-card fb-summary-emblem-card is-claim">
                            <div class="fb-report-card-top"><span class="fb-summary-emblem"><svg viewBox="0 0 24 24"
                                        width="21" height="21" fill="none" stroke="currentColor"
                                        stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                                        aria-hidden="true">
                                        <circle cx="12" cy="12" r="9" />
                                        <path d="M12 7v5l3 2" />
                                    </svg></span><span class="fb-report-card-label">Proses Klaim</span></div>
                            <strong>{{ $analyticsReady ? number_format($analytics['claim'], 0, ',', '.') : '—' }}</strong><small>Menunggu
                                penyelesaian klaim</small>
                        </article>
                        <article class="fb-report-card fb-summary-emblem-card is-returned">
                            <div class="fb-report-card-top"><span class="fb-summary-emblem"><svg viewBox="0 0 24 24"
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
                <div class="fb-trend-chart" id="fb-trend-chart" role="group"
                    aria-label="Grafik tren laporan hilang dan ditemukan"></div>
                <div class="fb-chart-legend"><span><i class="fb-legend-lost"></i>Barang hilang</span><span><i
                            class="fb-legend-found"></i>Barang ditemukan</span></div>
            </section>
            <section class="fb-panel fb-distribution-panel" aria-labelledby="fb-dist-title">
                <div class="fb-panel-head">
                    <div>
                        <h2 id="fb-dist-title">Distribusi Laporan</h2>
                        <p>Komposisi laporan barang</p>
                    </div><span class="fb-period-tag">Seluruh data</span>
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
                                Hilang</span><b>{{ $analyticsReady ? $analytics['lost'] : '—' }} @if ($analyticsReady && ($analytics['lost'] + $analytics['found']) > 0)
                                    <small>({{ number_format(100 * $analytics['lost'] / ($analytics['lost'] + $analytics['found']), 0, ',', '.') }}%)</small>
                                @endif</b></div>
                        <div><i class="fb-legend-found"></i><span>Barang
                                Ditemukan</span><b>{{ $analyticsReady ? $analytics['found'] : '—' }} @if ($analyticsReady && ($analytics['lost'] + $analytics['found']) > 0)
                                    <small>({{ number_format(100 * $analytics['found'] / ($analytics['lost'] + $analytics['found']), 0, ',', '.') }}%)</small>
                                @endif</b></div>
                    </div>
                </div>
            </section>
        </div>

        @include('admin.partials.recent-reports', [
            'showAllReportsLink' => true,
            'recentRows' => $analytics['recent'] ?? [],
            'recentTitle' => 'Laporan Terbaru',
            'recentCaption' => 'Sekilas hingga 5 laporan barang terbaru yang sudah lolos verifikasi.',
            'recentEmpty' => $analyticsReady
                ? 'Laporan akan muncul setelah lolos verifikasi.'
                : 'Hubungkan modul laporan barang untuk mengaktifkan statistik dan grafik.',
        ])

        <section class="fb-compact-users" aria-labelledby="fb-users-title">
            <div class="fb-section-title fb-users-heading">
                <div>
                    <h2 id="fb-users-title">Ringkasan Pengguna</h2>
                    <p>Monitoring akun yang terdaftar di {{ $adminSettings['site_name'] }}</p>
                </div>
                <span class="fb-users-tag"><span aria-hidden="true"></span> Data akun</span>
            </div>
            <div class="fb-user-summary">
                <article class="fb-user-metric fb-summary-emblem-card is-account-total">
                    <div class="fb-user-metric-head">
                        <span>Total Akun</span>
                        <span class="fb-summary-emblem" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                stroke-linejoin="round">
                                <circle cx="9" cy="8" r="3" />
                                <path d="M3 20v-2a6 6 0 0 1 12 0v2M17 6a3 3 0 0 1 0 6m1 4a5 5 0 0 1 3 4" />
                            </svg></span>
                    </div>
                    <strong>{{ number_format($totalUsers, 0, ',', '.') }}</strong>
                    <small>Seluruh akun terdaftar</small>
                </article>
                <article class="fb-user-metric fb-summary-emblem-card is-account-user">
                    <div class="fb-user-metric-head">
                        <span>Pengguna Biasa</span>
                        <span class="fb-summary-emblem" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                stroke-linejoin="round">
                                <circle cx="12" cy="8" r="3.5" />
                                <path d="M5 21v-2a7 7 0 0 1 14 0v2" />
                            </svg></span>
                    </div>
                    <strong>{{ number_format($totalRegularUsers, 0, ',', '.') }}</strong>
                    <small>Akun dengan role user</small>
                </article>
                <article class="fb-user-metric fb-summary-emblem-card is-account-admin">
                    <div class="fb-user-metric-head">
                        <span>Administrator</span>
                        <span class="fb-summary-emblem" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"
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
@endsection
