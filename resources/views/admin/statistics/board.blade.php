<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Papan Informasi FindBack</title>
    <link rel="stylesheet"
        href="{{ 'file:///' . ltrim(str_replace('\\', '/', public_path('css/pages/admin/statistics/board.css')), '/') }}">
    <link rel="stylesheet"
        href="{{ 'file:///' . ltrim(str_replace('\\', '/', public_path('css/shared/view-utilities.css')), '/') }}">
<!-- FINDBACK_TYPOGRAPHY_V1 -->
<link rel="stylesheet" href="{{ asset('css/shared/findback-typography-v1.css') }}">
</head>

<body>
    @foreach ($pages as $pageIndex => $items)
        <section class="page {{ $loop->last ? 'last' : '' }}">
            <header class="header">
                <p class="brand">{{ mb_strtoupper(mb_strimwidth($settings['site_name'], 0, 35, '...')) }}</p>
                <h1>BARANG HILANG<br>&amp; DITEMUKAN</h1>
                <p class="institution">{{ mb_strimwidth($settings['institution_name'], 0, 70, '...') }}</p>
            </header>
            <div class="strip">INFORMASI TERVERIFIKASI &nbsp; | &nbsp; {{ $periodLabel }} &nbsp; | &nbsp;
                {{ $total }} laporan aktif</div>
            <div class="content">
                @forelse($items as $item)
                    <article class="card">
                        <table>
                            <tr>
                                <td class="picture">
                                    <div class="photo-frame">
                                        @if ($item->photoData)
                                        <img src="{{ $item->photoData }}" alt="Foto barang">@else<div
                                                class="no-photo">FOTO BARANG<br>BELUM TERSEDIA</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="details"><span
                                        class="badge {{ $item->type === 'found' ? 'found' : '' }}">{{ $item->type === 'found' ? 'DITEMUKAN' : 'HILANG' }}</span>
                                    <h2 class="title">{{ mb_strimwidth($item->title, 0, 36, '...') }}</h2>
                                    <p class="meta"><strong>Lokasi:</strong>
                                        {{ mb_strimwidth($item->location ?: 'Belum dicantumkan', 0, 34, '...') }}<br><strong>Tanggal:</strong>
                                        {{ $item->event_date ? \Illuminate\Support\Carbon::parse($item->event_date)->format('d/m/Y') : 'Belum dicantumkan' }}
                                    </p>
                                    <p class="report-id">REFERENSI LAPORAN #{{ $item->id }}</p>
                                </td>
                            </tr>
                        </table>
                    </article>
                @empty<div class="empty">
                        <h2>Belum ada pengumuman<br>pada periode ini.</h2>
                        <p>Kehilangan atau menemukan barang?<br>Laporkan melalui FindBack Kampus.</p>
                    </div>
                @endforelse
            </div>
            <footer class="footer">
                <h2>Kenali barangmu? Hubungi petugas.</h2>
                <p>Sebutkan nomor laporan dan siapkan bukti kepemilikan.<br>Lihat informasi selengkapnya melalui
                    FindBack Kampus.</p>
                <div class="update">Diperbarui {{ $generatedAt->format('d/m/Y H:i') }} WIB &nbsp; | &nbsp; Halaman
                    {{ $pageIndex + 1 }}/{{ $pages->count() }}</div>
            </footer>
        </section>
    @endforeach
</body>

</html>
