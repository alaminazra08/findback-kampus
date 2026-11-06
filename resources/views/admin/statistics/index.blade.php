@extends('admin.layouts.items')

@section('title', 'Laporan & Statistik')
@section('breadcrumb', 'Laporan & Statistik')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/admin/admin-statistics.css') }}?v=33">
    <link rel="stylesheet" href="{{ asset('css/admin/findback-statistics-premium.css') }}?v=4">
    <section class="fs-page">
        <div class="fs-header">
            <div>
                <p class="fs-eyebrow">FINDBACK KAMPUS · ANALITIK OPERASIONAL</p>
                <h1>Laporan <span>& Statistik</span></h1>
                <p>Ringkasan aktivitas barang hilang, barang ditemukan, klaim, dan pencocokan berdasarkan database.</p>
            </div>
            <div class="fs-header-actions">
                <div class="fs-toolbar-label"><span class="fs-toolbar-dot"></span> PUSAT LAPORAN</div>
                <form method="GET" action="{{ route('admin.statistics.index') }}" class="fs-filter">
                    <label for="period">Periode laporan</label>
                    <select id="period" name="period" data-submit-on-change>
                        @foreach (['7' => '7 Hari Terakhir', '30' => '30 Hari Terakhir', '90' => '3 Bulan Terakhir', '365' => '1 Tahun Terakhir', 'all' => 'Semua Data'] as $key => $label)
                            <option value="{{ $key }}" @selected($period === (string) $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <noscript><button type="submit">Terapkan</button></noscript>
                </form>
                <div class="fs-export-actions" aria-label="Ekspor laporan">
                    <a class="fs-export-button"
                        href="{{ route('admin.statistics.export.csv', ['period' => $period]) }}"><span
                            class="fs-export-icon" aria-hidden="true">▤</span> CSV (Excel)</a>
                    <a class="fs-export-button is-secondary"
                        href="{{ route('admin.statistics.export.pdf', ['period' => $period]) }}"><span
                            class="fs-export-icon" aria-hidden="true">▧</span> PDF Admin</a>
                    <a class="fs-export-button fs-export-tv"
                        href="{{ route('admin.statistics.export.board', ['period' => $period]) }}"><span
                            class="fs-export-icon" aria-hidden="true">▣</span> PDF TV (9:16)</a>
                </div>
                <small class="fs-export-hint">Semua unduhan mengikuti periode. PDF TV menampilkan laporan terverifikasi yang
                    masih aktif, 4 barang per halaman.</small>
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
                <div class="fs-card-top"><span class="fs-card-icon" aria-hidden="true"><svg viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                            stroke-linejoin="round">
                            <rect x="6" y="3" width="13" height="16" rx="2" />
                            <path d="M3 7v13a2 2 0 0 0 2 2h11" />
                            <path d="M9 8h7M9 12h7" />
                        </svg></span><span class="fs-card-period">{{ $activePeriodLabel }}</span></div>
                <strong>{{ number_format($total, 0, ',', '.') }}</strong>
                <span class="fs-card-name">Total Laporan</span>
                <small>Seluruh laporan periode ini</small>
            </article>
            <article class="fs-card fs-card-v4">
                <div class="fs-card-top"><span class="fs-card-icon" aria-hidden="true"><svg viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                            stroke-linejoin="round">
                            <circle cx="11" cy="11" r="7" />
                            <path d="m20 20-4-4" />
                        </svg></span><span class="fs-card-period">{{ $activePeriodLabel }}</span></div>
                <strong>{{ number_format($lost, 0, ',', '.') }}</strong>
                <span class="fs-card-name">Barang Hilang</span>
                <small>Laporan berjenis hilang</small>
            </article>
            <article class="fs-card fs-card-v4">
                <div class="fs-card-top"><span class="fs-card-icon" aria-hidden="true"><svg viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="m12 3 9 5v9l-9 5-9-5V8l9-5Z" />
                            <path d="m3 8 9 5 9-5M12 13v9" />
                            <path d="m9 10 2 2 4-4" />
                        </svg></span><span class="fs-card-period">{{ $activePeriodLabel }}</span></div>
                <strong>{{ number_format($found, 0, ',', '.') }}</strong>
                <span class="fs-card-name">Barang Ditemukan</span>
                <small>Laporan berjenis ditemukan</small>
            </article>
            <article class="fs-card fs-card-v4">
                <div class="fs-card-top"><span class="fs-card-icon" aria-hidden="true"><svg viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                            stroke-linejoin="round">
                            <rect x="5" y="5" width="14" height="17" rx="2" />
                            <rect x="9" y="2" width="6" height="5" rx="1" />
                            <path d="m9 14 2 2 4-4" />
                        </svg></span><span class="fs-card-period">{{ $activePeriodLabel }}</span></div>
                <strong>{{ number_format($claimTotal, 0, ',', '.') }}</strong>
                <span class="fs-card-name">Pengajuan Klaim</span>
                <small>Seluruh klaim periode ini</small>
            </article>
            <article class="fs-card fs-card-v4">
                <div class="fs-card-top"><span class="fs-card-icon" aria-hidden="true"><svg viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                            stroke-linejoin="round">
                            <circle cx="6" cy="6" r="2" />
                            <circle cx="18" cy="18" r="2" />
                            <path d="M6 8v8a2 2 0 0 0 2 2h8M18 16V8a2 2 0 0 0-2-2H8" />
                        </svg></span><span class="fs-card-period">{{ $activePeriodLabel }}</span></div>
                <strong>{{ number_format($matchTotal, 0, ',', '.') }}</strong>
                <span class="fs-card-name">Kandidat Pencocokan</span>
                <small>Hasil pencocokan tersimpan</small>
            </article>
            <article class="fs-card fs-card-v4">
                <div class="fs-card-top"><span class="fs-card-icon" aria-hidden="true"><svg viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="m2 9 4-4 5 2 3-2 8 5-3 6-5 5-3-2-3 1-6-7Z" />
                            <path d="m8 12 4 4 2-1 3 2" />
                        </svg></span><span class="fs-card-period">{{ $activePeriodLabel }}</span></div>
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
    <script src="{{ asset('js/pages/admin/statistics/index.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('css/pages/admin/statistics/index.css') }}">
@endsection
