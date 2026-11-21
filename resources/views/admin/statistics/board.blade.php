<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Papan Informasi FindBack</title>
    <style>{!! file_get_contents(public_path('css/pages/admin/statistics/board.css')) !!}</style>
</head>
<body>
    @php($logo = 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('images/logo-findback.png'))))
    @foreach ($pages as $items)
        <section class="slide {{ $loop->last ? 'last' : '' }}">
            <header class="header">
                <table><tr>
                    <td class="logo"><img src="{{ $logo }}" alt="FindBack Kampus"></td>
                    <td><strong>FindBack Kampus</strong><br>{{ $settings['site_name'] }}</td>
                </tr></table>
                <h1>Barang Hilang &amp; Ditemukan</h1>
                <p>Terakhir diperbarui {{ $generatedAt->format('d/m/Y H:i') }} WIB · {{ $periodLabel }} · {{ $total }} laporan aktif</p>
            </header>
            @if ($items->isNotEmpty())
                <table class="cards"><tr>
                    @foreach ($items as $item)
                        <td class="card">
                            <span class="badge {{ $item->type === 'found' ? 'found' : '' }}">{{ $item->type === 'found' ? 'DITEMUKAN' : 'HILANG' }}</span>
                            <h2>{{ mb_strimwidth($item->title, 0, 45, '…') }}</h2>
                            <p class="code">{{ \App\Support\WorkflowStatus::code((int) $item->id) }}</p>
                            <p class="meta"><strong>Lokasi</strong><br>{{ mb_strimwidth($item->location ?: 'Belum dicantumkan', 0, 52, '…') }}</p>
                            <p class="meta"><strong>Tanggal kejadian</strong><br>{{ $item->event_date ? \Illuminate\Support\Carbon::parse($item->event_date)->format('d/m/Y') : 'Belum dicantumkan' }}</p>
                        </td>
                    @endforeach
                    @for ($slot = $items->count(); $slot < 3; $slot++)
                        <td class="space"></td>
                    @endfor
                </tr></table>
            @else
                <div class="empty">Belum ada laporan aktif pada periode ini.</div>
            @endif
            <footer class="footer">
                <strong>Kenali barang ini? Hubungi petugas fakultas dan sebutkan kode BRG.</strong>
                <span>{{ $loop->iteration }} / {{ $loop->count }}</span>
            </footer>
        </section>
    @endforeach
</body>
</html>
