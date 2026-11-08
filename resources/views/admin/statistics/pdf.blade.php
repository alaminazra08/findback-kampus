<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Laporan Admin</title>
    <link rel="stylesheet"
        href="{{ 'file:///' . ltrim(str_replace('\\', '/', public_path('css/pages/admin/statistics/pdf.css')), '/') }}">
    <link rel="stylesheet"
        href="{{ 'file:///' . ltrim(str_replace('\\', '/', public_path('css/shared/view-utilities.css')), '/') }}">
</head>

<body>
    <header class="header">
        <div class="eyebrow">{{ $settings['site_name'] }} / ADMINISTRASI</div>
        <h1>Laporan &amp; Statistik</h1>
        <div>{{ $settings['institution_name'] }}</div>
        <p class="muted">Periode: {{ $periodLabel }} &nbsp; | &nbsp; Dibuat {{ $generatedAt->format('d/m/Y H:i') }} WIB
            &nbsp; | &nbsp; {{ auth()->user()->name }}</p>
    </header>
    <table class="summary">
        <tr>
            <td><span>Total Laporan</span><strong>{{ $total }}</strong></td>
            <td><span>Barang Hilang</span><strong>{{ $lost }}</strong></td>
            <td><span>Barang Ditemukan</span><strong>{{ $found }}</strong></td>
        </tr>
        <tr>
            <td><span>Pengajuan Klaim</span><strong>{{ $claimTotal }}</strong></td>
            <td><span>Kandidat Pencocokan</span><strong>{{ $matchTotal }}</strong></td>
            <td><span>Barang Dikembalikan</span><strong>{{ $statuses['returned'] ?? 0 }}</strong></td>
        </tr>
    </table>
    <table class="recaps">
        <tr>
            <td>
                <h2>Status Klaim</h2>
                <table class="data">
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
            </td>
            <td>
                <h2>Status Pencocokan</h2>
                <table class="data">
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
            </td>
        </tr>
    </table>
    <h2>Daftar Laporan Barang ({{ $reports->count() }})</h2>
    <table class="data">
        <thead>
            <tr>
                <th class="fb-refactor fb-width-5">ID</th>
                <th class="fb-refactor fb-width-22">Nama Barang</th>
                <th class="fb-refactor fb-width-11">Jenis</th>
                <th class="fb-refactor fb-width-15">Kategori</th>
                <th class="fb-refactor fb-width-22">Lokasi</th>
                <th class="fb-refactor fb-width-13">Status</th>
                <th class="fb-refactor fb-width-12">Dilaporkan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->title }}</td>
                    <td>{{ $item->type === 'found' ? 'Ditemukan' : 'Hilang' }}</td>
                    <td>{{ $item->category ?: '-' }}</td>
                    <td>{{ $item->location ?: '-' }}</td>
                    <td>{{ \App\Support\WorkflowStatus::reports()[$item->status] ?? $item->status }}</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($item->created_at)->format('d/m/Y') }}</td>
            </tr>@empty<tr>
                    <td colspan="7">Belum ada laporan pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <p class="note">Periode mengikuti tanggal pembuatan masing-masing laporan/klaim/kandidat. Dikembalikan dihitung
        dari status terkini laporan pada periode tersebut, bukan tanggal serah terima. Dokumen ini untuk administrasi
        internal.</p>
</body>

</html>
