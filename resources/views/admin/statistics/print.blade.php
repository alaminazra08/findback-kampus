<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan {{ $settings['site_name'] }}</title>
    <link rel="stylesheet" href="{{ asset('css/pages/admin/statistics/print.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/view-utilities.css') }}">
    <script src="{{ asset('js/shared/view-interactions.js') }}"></script>
</head>

<body>
    <div class="toolbar">
        <p>Pilih <strong>Cetak / Simpan PDF</strong>, lalu pilih printer atau <strong>Save as PDF</strong>.<br>Gunakan
            A4 lanskap. Matikan header/footer browser bila tidak diperlukan.</p><button type="button"
            data-print-page>Cetak / Simpan PDF</button>
    </div>
    <main>
        <header>
            <p class="eyebrow">{{ $settings['site_name'] }} · Laporan Operasional</p>
            <h1>Laporan Barang Hilang dan Ditemukan</h1>
            <p>{{ $settings['institution_name'] }}</p>
            <p>Periode: <strong>{{ $periodLabel }}</strong>
                @if ($since)
                    · Sejak {{ $since->format('d/m/Y') }} (zona waktu aplikasi)
                @endif
            </p>
            <p class="muted">Dicetak oleh {{ auth()->user()->name }} · {{ $generatedAt->format('d/m/Y H:i') }} WIB</p>
        </header>
        <div class="summary">
            @foreach (['Total Laporan' => $total, 'Barang Hilang' => $lost, 'Barang Ditemukan' => $found, 'Pengajuan Klaim' => $claimTotal, 'Kandidat Pencocokan' => $matchTotal, 'Barang Dikembalikan' => $statuses['returned'] ?? 0] as $label => $value)
                <div><span>{{ $label }}</span><strong>{{ number_format($value, 0, ',', '.') }}</strong></div>
            @endforeach
        </div>
        <div class="recaps">
            <section>
                <h2>Rekap Status Klaim</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (\App\Support\WorkflowStatus::claims() as $key => $label)
                            <tr>
                                <td>{{ $label }}</td>
                                <td>{{ $claimStatuses[$key] ?? 0 }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
            <section>
                <h2>Rekap Status Pencocokan</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (\App\Support\WorkflowStatus::matches() as $key => $label)
                            <tr>
                                <td>{{ $label }}</td>
                                <td>{{ $matchStatuses[$key] ?? 0 }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
            <section>
                <h2>Enam Kategori Terbanyak</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topCategories as $row)
                            <tr>
                                <td>{{ $row->category }}</td>
                                <td>{{ $row->total }}</td>
                        </tr>@empty<tr>
                                <td colspan="2">Belum ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
            <section>
                <h2>Enam Lokasi Terbanyak</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Lokasi</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topLocations as $row)
                            <tr>
                                <td>{{ $row->location }}</td>
                                <td>{{ $row->total }}</td>
                        </tr>@empty<tr>
                                <td colspan="2">Belum ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
        </div>
        <h2>Daftar Lengkap Laporan Barang · {{ $reports->count() }} laporan</h2>
        <div class="table-wrap">
            <table class="detail-table">
                <colgroup>
                    <col class="fb-refactor fb-width-5">
                    <col class="fb-refactor fb-width-21">
                    <col class="fb-refactor fb-width-10">
                    <col class="fb-refactor fb-width-13">
                    <col class="fb-refactor fb-width-18">
                    <col class="fb-refactor fb-width-11">
                    <col class="fb-refactor fb-width-12">
                    <col class="fb-refactor fb-width-10">
                </colgroup>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Barang</th>
                        <th>Jenis</th>
                        <th>Kategori</th>
                        <th>Lokasi</th>
                        <th>Kejadian</th>
                        <th>Status</th>
                        <th>Dilaporkan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                        <tr>
                            <td>{{ $report->id }}</td>
                            <td>{{ $report->title }}</td>
                            <td>{{ ['lost' => 'Hilang', 'found' => 'Ditemukan'][$report->type] ?? $report->type }}</td>
                            <td>{{ $report->category ?: '—' }}</td>
                            <td>{{ $report->location ?: '—' }}</td>
                            <td>{{ $report->event_date ? \Illuminate\Support\Carbon::parse($report->event_date)->format('d/m/Y') : '—' }}
                            </td>
                            <td>{{ \App\Support\WorkflowStatus::reports()[$report->status] ?? $report->status }}</td>
                            <td>{{ $report->created_at ? \Illuminate\Support\Carbon::parse($report->created_at)->format('d/m/Y') : '—' }}
                            </td>
                    </tr>@empty<tr>
                            <td colspan="8" class="empty">Belum ada laporan pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <footer>Periode menggunakan tanggal pembuatan masing-masing laporan, klaim, dan kandidat. Barang dikembalikan
            dihitung dari status terkini laporan dalam periode, bukan tanggal serah terima. CSV berisi detail laporan
            barang; dokumen cetak berisi rekap klaim/pencocokan dan seluruh laporan barang. Data sesuai keadaan saat
            dokumen dibuka.</footer>
    </main>
</body>

</html>
