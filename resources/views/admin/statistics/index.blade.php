@extends('admin.layouts.items')

@section('title', 'Laporan & Statistik')
@section('breadcrumb', 'Laporan & Statistik')

@section('page-styles')
    <link rel="stylesheet" href="{{ asset('css/admin/header-consistency.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('css/admin/admin-statistics.css') }}?v=33">
    <link rel="stylesheet" href="{{ asset('css/admin/findback-statistics-premium.css') }}?v=4">
    <link rel="stylesheet" href="{{ asset('css/pages/admin/statistics/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/statistics-download-menu-v1.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/admin/statistics-download-menu-v2.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('css/admin/statistics-download-menu-v3.css') }}?v=3">
    <link rel="stylesheet" href="{{ asset('css/admin/statistics-download-menu-v4.css') }}?v=4">
    <link rel="stylesheet" href="{{ asset('css/admin/statistics-period-dropdown-v5.css') }}?v=5">
    <link rel="stylesheet" href="{{ asset('css/admin/statistics-dropdown-layer-v6.css') }}?v=6">
    <link rel="stylesheet" href="{{ asset('css/admin/statistics-cards-consistency-v1.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/admin/statistics-illustration-icons-v1.css') }}?v=1">
@endsection

@section('content')

    <section class="fs-page">
        <div class="fs-header fb-work-heading fb-work-heading--statistics">
            <div>
                <p class="fs-eyebrow">ADMINISTRASI · FINDBACK KAMPUS</p>
                <h1>Laporan <span>& Statistik</span></h1>
                <p>Ringkasan aktivitas barang hilang, barang ditemukan, klaim, dan pencocokan berdasarkan database.</p>
            </div>
            <div class="fs-header-actions">
                <div class="fs-toolbar-label"><span class="fs-toolbar-dot"></span> PUSAT LAPORAN</div>
                <form method="GET" action="{{ route('admin.statistics.index') }}" class="fs-filter">
                    <label for="period">Periode laporan</label>
                    <select id="period" name="period" data-fb-stat-period>
                        @foreach (['7' => '7 Hari Terakhir', '30' => '30 Hari Terakhir', '90' => '3 Bulan Terakhir', '365' => '1 Tahun Terakhir', 'all' => 'Semua Data'] as $key => $label)
                            <option value="{{ $key }}" @selected($period === (string) $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <noscript><button type="submit">Terapkan</button></noscript>
                </form>
                <div class="fs-report-download-wrap">
                    <details class="fs-report-download">
                        <summary class="fs-report-download-trigger">
                            <span class="fs-report-download-trigger-main">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v11m0 0 4-4m-4 4-4-4M5 17v3h14v-3" /></svg>
                                <span>Unduh Laporan PDF</span>
                            </span>
                            <svg class="fs-report-download-chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6" /></svg>
                        </summary>
                        <div class="fs-report-download-menu" role="group" aria-label="Pilih format laporan PDF">
                            <a class="fs-report-download-option" href="{{ route('admin.statistics.export.pdf', ['period' => $period]) }}">
                                <span class="fs-report-download-option-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24"><path d="M7 3h7l5 5v13H7zM14 3v5h5M10 13h6M10 17h6" /></svg>
                                </span>
                                <span class="fs-report-download-option-copy">
                                    <strong>PDF Laporan Administrasi</strong>
                                    <small>Format A4 · Dokumentasi dan arsip</small>
                                </span>
                                <svg class="fs-report-download-option-arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9" /></svg>
                            </a>
                            <a class="fs-report-download-option" href="{{ route('admin.statistics.export.board', ['period' => $period]) }}">
                                <span class="fs-report-download-option-icon is-tv" aria-hidden="true">
                                    <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="13" rx="2"/><path d="M8 21h8m-4-4v4" /></svg>
                                </span>
                                <span class="fs-report-download-option-copy">
                                    <strong>PDF Informasi TV Fakultas</strong>
                                    <small>Format 9:16 · Tampilan layar informasi</small>
                                </span>
                                <svg class="fs-report-download-option-arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9" /></svg>
                            </a>
                        </div>
                    </details>
                </div>
                <small class="fs-export-hint">Pilihan laporan mengikuti periode yang dipilih. Versi TV menampilkan laporan terverifikasi yang masih aktif, 4 barang per halaman.</small>
            </div>
        </div>

        @php
            $periodLabels = [
                '7' => '7 Hari Terakhir',
                '30' => '30 Hari Terakhir',
                '90' => '3 Bulan Terakhir',
                '365' => '1 Tahun Terakhir',
                'all' => 'Semua Data',
            ];
            $activePeriodLabel = $periodLabels[(string) $period] ?? 'Periode Laporan';
        @endphp
        <div class="fs-cards" aria-label="Ringkasan statistik pada periode terpilih">
            <article class="fs-card fs-card-v4">
                <div class="fs-card-top"><span class="fs-card-icon fs-card-icon-illustrated" aria-hidden="true"><svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="12" y="7" width="25" height="34" rx="4"/><path d="M18 14h13M18 21h9M18 28h11M18 35h7"/><path d="M8 12v27a4 4 0 0 0 4 4h21"/><circle cx="33" cy="33" r="8" fill="#FBEAF0" stroke="currentColor"/><path d="M30 35v-4m3 4v-7m3 7v-5"/></svg></span><span class="fs-card-period">{{ $activePeriodLabel }}</span></div>
                <strong>{{ number_format($total, 0, ',', '.') }}</strong>
                <span class="fs-card-name">Total Laporan</span>
                <small>Seluruh laporan periode ini</small>
            </article>
            <article class="fs-card fs-card-v4">
                <div class="fs-card-top"><span class="fs-card-icon fs-card-icon-illustrated" aria-hidden="true"><svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="m10 17 13-7 13 7v14l-13 7-13-7V17Z"/><path d="m10 17 13 7 13-7M23 24v14M17 13l13 7"/><circle cx="33" cy="32" r="7" fill="#FBEAF0"/><path d="m38 37 5 5"/></svg></span><span class="fs-card-period">{{ $activePeriodLabel }}</span></div>
                <strong>{{ number_format($lost, 0, ',', '.') }}</strong>
                <span class="fs-card-name">Barang Hilang</span>
                <small>Laporan berjenis hilang</small>
            </article>
            <article class="fs-card fs-card-v4">
                <div class="fs-card-top"><span class="fs-card-icon fs-card-icon-illustrated" aria-hidden="true"><svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="m9 16 14-8 16 8v17l-16 8-14-8V16Z"/><path d="m9 16 14 8 16-8M23 24v17M16 12l16 8"/><path d="m18 28 4 4 8-9" stroke-width="2.4"/><path d="M35 8v-3m5 6 2-2" opacity=".65"/></svg></span><span class="fs-card-period">{{ $activePeriodLabel }}</span></div>
                <strong>{{ number_format($found, 0, ',', '.') }}</strong>
                <span class="fs-card-name">Barang Ditemukan</span>
                <small>Laporan berjenis ditemukan</small>
            </article>
            <article class="fs-card fs-card-v4">
                <div class="fs-card-top"><span class="fs-card-icon fs-card-icon-illustrated" aria-hidden="true"><svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="10" y="9" width="27" height="34" rx="4"/><path d="M18 9V6a3 3 0 0 1 3-3h5a3 3 0 0 1 3 3v3"/><path d="m16 20 2 2 4-4m3 2h7M16 29l2 2 4-4m3 2h7"/><path d="m29 39 10-10 4 4-10 10-5 1 1-5Z" fill="#FBEAF0"/></svg></span><span class="fs-card-period">{{ $activePeriodLabel }}</span></div>
                <strong>{{ number_format($claimTotal, 0, ',', '.') }}</strong>
                <span class="fs-card-name">Pengajuan Klaim</span>
                <small>Seluruh klaim periode ini</small>
            </article>
            <article class="fs-card fs-card-v4">
                <div class="fs-card-top"><span class="fs-card-icon fs-card-icon-illustrated" aria-hidden="true"><svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="7" y="10" width="13" height="13" rx="3"/><rect x="29" y="26" width="13" height="13" rx="3"/><path d="M20 16h5a5 5 0 0 1 5 5v5M28 32h-5a5 5 0 0 1-5-5v-4"/><path d="m24 12 4 4-4 4M24 36l-4-4 4-4" fill="#FBEAF0"/></svg></span><span class="fs-card-period">{{ $activePeriodLabel }}</span></div>
                <strong>{{ number_format($matchTotal, 0, ',', '.') }}</strong>
                <span class="fs-card-name">Kandidat Pencocokan</span>
                <small>Hasil pencocokan tersimpan</small>
            </article>
            <article class="fs-card fs-card-v4">
                <div class="fs-card-top"><span class="fs-card-icon fs-card-icon-illustrated" aria-hidden="true"><svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="m5 20 8-8 8 3 5-3 17 10-5 8-7 8-6-2-5 2-15-13Z"/><path d="m15 22 8 8a4 4 0 0 0 6-1l3-4M21 18l8 7a4 4 0 0 0 6-1l2-3"/><path d="m18 31 4 4m1-7 5 5m-1-9 5 4" opacity=".9"/></svg></span><span class="fs-card-period">{{ $activePeriodLabel }}</span></div>
                <strong>{{ number_format($statuses['returned'] ?? 0, 0, ',', '.') }}</strong>
                <span class="fs-card-name">Barang Dikembalikan</span>
                <small>Berdasarkan status laporan barang</small>
            </article>
        </div>

        <div class="fs-section-grid">
            <section class="fs-panel fs-trend-panel">
                <div class="fs-panel-head">
                    <div>
                        <h2>Tren Laporan Barang</h2>
                        <p>Jumlah laporan hilang dan ditemukan berdasarkan periode terpilih</p>
                    </div>
                </div>
                <div class="fs-chart-stage">
                    <div class="fs-chart-scale" id="fs-chart-scale" aria-hidden="true">
                        <span>4</span><span>3</span><span>2</span><span>1</span><span>0</span>
                    </div>
                    <div class="fs-chart-scroll">
                        <div class="fs-chart" id="fs-trend" role="img"
                            aria-label="Grafik jumlah laporan hilang dan ditemukan"></div>
                    </div>
                </div>
                <p class="fs-chart-help" id="fs-chart-summary" aria-live="polite">Arahkan kursor atau sentuh batang untuk
                    melihat rincian.</p>
                <div class="fs-legend"><span><i class="fs-dot lost"></i> Hilang</span><span><i class="fs-dot found"></i>
                        Ditemukan</span></div>
            </section>
            <section class="fs-panel">
                <div class="fs-panel-head">
                    <div>
                        <h2>Status Klaim</h2>
                        <p>Jumlah pengajuan menurut keputusan</p>
                    </div>
                </div>
                @foreach (\App\Support\WorkflowStatus::claims() as $key => $label)
                    @php $count = (int)($claimStatuses[$key] ?? 0); @endphp
                    <div class="fs-status-row">
                        <span>{{ $label }}</span><strong>{{ number_format($count, 0, ',', '.') }}</strong>
                    </div>
                    <div class="fs-meter"><i
                            class="fb-refactor fb-percent-{{ $claimTotal ? round(($count / $claimTotal) * 100) : 0 }}"></i>
                    </div>
                @endforeach
            </section>
        </div>

        <div class="fs-section-grid fs-secondary-grid fb-stat-no-category">
            <section class="fs-panel">
                <div class="fs-panel-head">
                    <div>
                        <h2>Status Pencocokan</h2>
                        <p>Kandidat yang tersimpan di sistem</p>
                    </div>
                </div>
                @foreach (\App\Support\WorkflowStatus::matches() as $key => $label)
                    <div class="fs-status-row">
                        <span>{{ $label }}</span><strong>{{ number_format($matchStatuses[$key] ?? 0, 0, ',', '.') }}</strong>
                    </div>
                @endforeach
            </section>
            <section class="fs-panel">
                <div class="fs-panel-head">
                    <div>
                        <h2>Lokasi Terbanyak</h2>
                        <p>Lokasi yang paling sering dilaporkan</p>
                    </div>
                </div>
                @forelse($topLocations as $location)
                    <div class="fs-status-row">
                        <span>{{ $location->location }}</span><strong>{{ number_format($location->total, 0, ',', '.') }}</strong>
                    </div>
                @empty <p class="fs-empty">Belum ada data lokasi.</p>
                @endforelse
            </section>
        </div>

        <section class="fs-panel">
            <div class="fs-panel-head fs-recent-head">
                <div>
                    <h2>Laporan Terbaru</h2>
                    <p>Menampilkan hingga 8 laporan terbaru pada periode yang dipilih</p>
                </div><span class="fs-recent-counter"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M7 3h8l4 4v14H7z" />
                        <path d="M15 3v5h4M10 12h6M10 16h6" />
                    </svg> {{ $recent->count() }} laporan</span>
            </div>
            {{-- Mobile-only presentation of the same report records; desktop table is unchanged. --}}
            <div class="fs-mobile-reports" aria-label="Daftar laporan terbaru">
                @forelse($recent as $report)
                    <article class="fs-mobile-report-card">
                        <div class="fs-mobile-report-top">
                            <span class="fs-mobile-report-photo">
                                @if (!empty($report->photo_path))
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($report->photo_path) }}"
                                        alt="Foto barang {{ $report->title }}" loading="lazy" decoding="async"
                                        data-image-fallback="hidden">
                                @endif
                                <span class="fs-mobile-report-photo-fallback" @if (!empty($report->photo_path)) hidden @endif aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8" cy="8" r="1.5"/><path d="m3 17 6-6 4 4 3-3 5 5"/></svg>
                                </span>
                            </span>
                            <div class="fs-mobile-report-title">
                                <strong>{{ $report->title }}</strong>
                                <span class="fs-type-pill {{ $report->type === 'lost' ? 'is-lost' : 'is-found' }}">{{ $report->type === 'lost' ? 'Hilang' : 'Ditemukan' }}</span>
                            </div>
                        </div>
                        <div class="fs-mobile-report-details">
                            <span>Lokasi: <strong>{{ $report->location ?: '—' }}</strong></span>
                            <span>{{ \Illuminate\Support\Carbon::parse($report->created_at)->format('d M Y') }}</span>
                        </div>
                        <div class="fs-mobile-report-footer">
                            <span class="fs-pill {{ in_array($report->status, ['verified', 'approved', 'returned']) ? 'is-approved' : (in_array($report->status, ['rejected']) ? 'is-rejected' : 'is-pending') }}">{{ \App\Support\WorkflowStatus::report($report->status) }}</span>
                            <a class="fs-detail-link" href="{{ route('admin.items.show', $report->id) }}" aria-label="Lihat detail {{ $report->title }}">
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                <span>Lihat Detail</span>
                            </a>
                        </div>
                    </article>
                @empty
                    <p class="fs-mobile-reports-empty">Belum ada laporan pada periode ini.</p>
                @endforelse
            </div>
            <div class="fs-table-scroll">
                <table class="fs-table">
                    <thead>
                        <tr>
                            <th>Nama Barang</th>
                            <th>Jenis</th>
                            <th>Lokasi</th>
                            <th>Status</th>
                            <th class="fs-col-created">Dibuat</th>
                            <th class="fs-action-col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recent as $report)
                            <tr>
                                <td><strong>{{ $report->title }}</strong></td>
                                <td><span
                                        class="fs-type-pill {{ $report->type === 'lost' ? 'is-lost' : 'is-found' }}">{{ $report->type === 'lost' ? 'Hilang' : 'Ditemukan' }}</span>
                                </td>
                                <td>{{ $report->location ?: '—' }}</td>
                                <td><span
                                        class="fs-pill {{ in_array($report->status, ['verified', 'approved', 'returned']) ? 'is-approved' : (in_array($report->status, ['rejected']) ? 'is-rejected' : 'is-pending') }}">{{ \App\Support\WorkflowStatus::report($report->status) }}</span>
                                </td>
                                <td class="fs-col-created">
                                    {{ \Illuminate\Support\Carbon::parse($report->created_at)->format('d/m/Y') }}</td>
                                <td class="fs-action-col"><a class="fs-detail-link"
                                        href="{{ route('admin.items.show', $report->id) }}"
                                        aria-label="Lihat detail {{ $report->title }}"><svg aria-hidden="true"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7-10-7-10-7Z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg><span>Lihat Detail</span></a></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="fs-empty">Belum ada laporan pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
        <p class="fs-note">Catatan: jumlah pada tabel klaim dan pencocokan dihitung berdasarkan tanggal pembuatan
            masing-masing record. Tren periode “Semua data” menampilkan 30 hari terakhir, sedangkan rekap total tetap
            seluruh data.</p>
    </section>
    <script type="application/json" id="fs-trend-data" data-period="{{ $period }}">@json($trend)</script>
@endsection
