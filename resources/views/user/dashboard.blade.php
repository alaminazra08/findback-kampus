@extends('user.layouts.campus')
@section('title', 'Beranda')
@section('content')
    <div class="fb-home-v4">
        <section class="cu-hero">
            <div class="cu-hero-copy"><span class="cu-kicker">SALING PEDULI, SALING TEMUKAN</span>
                <p class="cu-greeting">Selamat datang kembali,</p>
                <h1>{{ auth()->user()->name }}<span>.</span></h1>
                <p>Bersama {{ $fbBrand['site_name'] }}, temukan kembali barangmu<br class="cu-desktop"> dan bantu barang orang lain
                    kembali ke pemiliknya.</p>
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
        <section class="cu-bottom">
            <div class="cu-panel">
                <div class="cu-section-title">
                    <h2>Aktivitas terakhirmu</h2><a href="{{ route('user.reports.index') }}">Lihat riwayat →</a>
                </div>
                @forelse($recentReports as $report)
                    <a class="cu-activity-row" href="{{ route('user.reports.show', $report->id) }}">
                        <div><strong>{{ $report->title }}</strong><small>{{ $report->location }}</small></div>
                        @include('user.components.status', ['status' => $report->status])
                </a>@empty<p class="cu-muted">Belum ada laporan. Mulai dengan melaporkan barang hilang atau ditemukan.
                    </p>
                @endforelse
            </div>
            <aside class="cu-tip"><span class="cu-kicker">SEBELUM MELAPOR</span>
                <h2>Detail kecil.<br>Peluang lebih besar.</h2>
                <p>Tambahkan foto yang jelas, ciri khas, dan lokasi kejadian agar barang lebih mudah dikenali.</p><a
                    href="{{ route('user.reports.lost.create') }}">Buat laporan →</a>
            </aside>
        </section>
    </div>
@endsection
