@extends('admin.layouts.items')
@section('title', 'Detail Barang')
@section('page-styles')
    <link rel="stylesheet" href="{{ asset('css/admin/claim-proof-modal.css') }}?v=17">
    <link rel="stylesheet" href="{{ asset('css/admin/verification-detail-card.css') }}?v=3">
    <link rel="stylesheet" href="{{ asset('css/pages/admin/detail-back.css') }}?v=1">
@endsection

@section('content')
    <div class="fb-items-heading fb-detail-return-heading">
        <a class="fb-detail-return" data-fb-detail-back href="{{ route('admin.items.index') }}"><span aria-hidden="true">←</span> Kembali ke daftar</a>
        <div>
            <p class="fb-eyebrow">ADMINISTRASI · DETAIL BARANG</p>
            <h1>Detail <span>Barang</span></h1>
            <p>Informasi lengkap laporan barang yang tersimpan dalam sistem.</p>
        </div>
    </div>
    <section class="fb-items-panel fb-items-detail fb-item-single-card" aria-labelledby="fb-detail-title">
        <div class="fb-items-panel-head">
            <div>
                <h2 id="fb-detail-title">{{ $item->title }}</h2>
                <p>{{ \App\Support\WorkflowStatus::code($item->id) }} ·
                    {{ $item->type === 'lost' ? 'Barang Hilang' : 'Barang Ditemukan' }}</p>
            </div>
            <span class="fb-items-badge status-{{ $item->status }}">{{ \App\Support\WorkflowStatus::report($item->status) }}</span>
        </div>
        <div @class(['fb-v-card-body', 'has-photo' => $item->photo_path])>
            @if ($item->photo_path)
                @include('admin.partials.report-main-photo', ['reportPhotoUrl' => \Illuminate\Support\Facades\Storage::url($item->photo_path)])
            @endif
            <div class="fb-v-card-facts">
                <dl class="fb-item-card-facts">
                    <div>
                        <dt>Pelapor</dt>
                        <dd>{{ $item->reporter?->name ?: 'Akun tidak tersedia' }}</dd>
                    </div>
                    <div>
                        <dt>Email pelapor</dt>
                        <dd>{{ $item->reporter?->email ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>Lokasi</dt>
                        <dd>{{ $item->location ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>Laporan dibuat</dt>
                        <dd>{{ $item->created_at ? $item->created_at->copy()->timezone('Asia/Jakarta')->format('d/m/Y H:i') . ' WIB' : '—' }}</dd>
                    </div>
                </dl>
                <div class="fb-v-description">
                    <h3>Deskripsi Barang</h3>
                    <p>{{ $item->description ?: 'Belum ada deskripsi barang.' }}</p>
                </div>
            </div>
        </div>
        <p class="fb-items-readonly">Keputusan laporan dilakukan melalui Verifikasi Laporan. Penyerahan fisik
            dicatat melalui Klaim &amp; Pengembalian.</p>
    </section>
    @if ($item->photo_path)
        @include('admin.partials.report-photo-dialog')
    @endif
    <footer class="fb-footer">© {{ date('Y') }} FindBack Kampus · Fakultas Sains dan Teknologi</footer>
@endsection
