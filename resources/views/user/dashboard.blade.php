@extends('user.layouts.campus')
@section('title', 'Beranda')
@section('content')
    <div class="fb-home-v4">
        <section class="cu-hero">
            <div class="cu-hero-copy"><span class="cu-kicker">SALING PEDULI, SALING TEMUKAN</span>
                <p class="cu-greeting">Selamat datang kembali,</p>
                <h1>{{ auth()->user()->name }}<span>.</span></h1>
                {{-- Deskripsi dinamis dari Admin > Pengaturan Sistem --}}
                <p class="fb-wrap-anywhere">{{ trim((string) ($fbBrand['site_description'] ?? '')) ?: 'Bersama FindBack Kampus, temukan kembali barangmu dan bantu barang orang lain kembali ke pemiliknya.' }}</p>
                <form class="cu-hero-search" action="{{ route('user.items.index') }}"><label class="cu-sr"
                        for="hero-search">Cari barang atau lokasi</label>@include('user.components.icon', ['icon' => 'search'])<input id="hero-search"
                        name="search" maxlength="100" placeholder="Cari barang, lokasi, atau kata kunci..."><button
                        aria-label="Cari barang">@include('user.components.icon', ['icon' => 'search'])</button></form>
            </div>
            <div class="cu-hero-caption">Kampus yang peduli.<br><strong>Barang kembali, cerita berlanjut.</strong></div>
        </section>
        <div class="cu-stats">
            @foreach ([['Laporan hilang saya', $lostReports, 'red', 'box', route('user.reports.index', ['type' => 'lost'])], ['Laporan ditemukan saya', $foundReports, 'green', 'box', route('user.reports.index', ['type' => 'found'])], ['Menunggu verifikasi', $pendingReports, 'plain', 'clock', route('user.reports.index', ['status' => 'pending'])], ['Klaim dikembalikan', $returnedClaims, 'plain', 'check', route('user.claims.index', ['status' => 'returned'])]] as [$label, $count, $tone, $icon, $url])
                <a class="cu-stat {{ $tone }}" href="{{ $url }}"><span
                        class="cu-stat-icon">@include('user.components.icon', ['icon' => $icon])</span><span><strong>{{ $count }}</strong><small>{{ $label }}</small></span><span>›</span></a>
            @endforeach
        </div>
        <div class="cu-quick"><a href="{{ route('user.reports.lost.create') }}"><span
                    class="cu-round red">@include('user.components.icon', ['icon' => 'search'])</span>
                <div><strong>Kehilangan sesuatu?</strong>
                    <p>Buat laporan agar lebih mudah ditemukan.</p>
                </div><span>Laporkan kehilangan ↗</span>
            </a><a href="{{ route('user.reports.found.create') }}"><span
                    class="cu-round green">@include('user.components.icon', ['icon' => 'box'])</span>
                <div><strong>Menemukan barang?</strong>
                    <p>Bantu kembalikan kepada pemiliknya.</p>
                </div><span>Laporkan penemuan ↗</span>
            </a></div>
        <section>
            <div class="cu-section-title">
                <div><span class="cu-kicker">DI SEKITAR KAMPUS</span>
                    <h2>Barang terbaru</h2>
                    <p>Laporan yang sudah diverifikasi oleh admin.</p>
                </div><a href="{{ route('user.items.index') }}">Lihat semua →</a>
            </div>
            <div class="cu-cards">
                @forelse($latestItems as $item)
                    @include('user.components.card')@empty
                    <div class="cu-empty">
                        <h3>Belum ada barang terverifikasi</h3>
                        <p>Barang akan tampil setelah laporan disetujui admin.</p>
                    </div>
                @endforelse
            </div>
        </section>
        @if ($approvedClaims > 0)
            <div class="cu-alert success"><strong>{{ $approvedClaims }} klaim telah disetujui</strong>. <a
                    href="{{ route('user.chat.index') }}">Hubungi admin untuk mengatur serah terima →</a></div>
        @endif
        

{{-- Aktivitas terbaru pengguna --}}
<section class="cu-bottom fb-recent-section">
    <div class="cu-panel fb-recent-panel">

        <div class="fb-recent-header">
            <div>
                <h2>Aktivitas terakhirmu</h2>
                <p>Pantau perkembangan laporan terbarumu.</p>
            </div>

            <a href="{{ route('user.reports.index') }}"
               class="fb-recent-history">
                Lihat riwayat
                <span aria-hidden="true">→</span>
            </a>
        </div>

        <div class="fb-recent-list">
            @forelse ($recentReports as $report)
                @php
                    $isLost = $report->type === 'lost';
                @endphp

                <a href="{{ route('user.reports.show', $report->id) }}"
                   class="fb-recent-item">

                    <span class="fb-recent-icon {{ $isLost ? 'lost' : 'found' }}">
                        @include('user.components.icon', [
                            'icon' => $isLost ? 'search' : 'box'
                        ])
                    </span>

                    <span class="fb-recent-info">
                        <strong>{{ $report->title }}</strong>

                        <span class="fb-recent-meta">
                            <span class="fb-recent-type">
                                {{ $isLost ? 'Barang hilang' : 'Barang ditemukan' }}
                            </span>

                            <span class="fb-recent-separator">•</span>

                            <span>{{ $report->location ?: 'Lokasi tidak tersedia' }}</span>

                            <span class="fb-recent-separator">•</span>

                            <span>
                                {{ \Carbon\Carbon::parse($report->created_at)->locale('id')->translatedFormat('d M Y') }}
                            </span>
                        </span>
                    </span>

                    <span class="fb-recent-status">
                        @include('user.components.status', [
                            'status' => $report->status
                        ])
                    </span>

                    <span class="fb-recent-arrow" aria-hidden="true">›</span>
                </a>
            @empty
                <div class="fb-recent-empty">
                    <strong>Belum ada aktivitas</strong>
                    <p>Laporan terbarumu akan tampil di sini.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

    </div>
@endsection
