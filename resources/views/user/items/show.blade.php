@extends('user.layouts.campus')
@section('title', 'Detail Barang')
@section('content')
    <div class="cu-crumb">
        <a href="{{ route('dashboard') }}">Beranda</a> / <a href="{{ route('user.items.index') }}">Cari Barang</a> / Detail
        barang
    </div>
    <section class="cu-panel cu-detail">
        <div class="cu-detail-photo">
            @if ($item->photo_path)
            <img src="{{ asset('storage/' . $item->photo_path) }}" alt="{{ $item->title }}">@else<div
                    class="cu-placeholder">@include('user.components.icon', ['icon' => 'box'])<p>Foto belum tersedia</p>
                </div>
            @endif
        </div>
        <div><span
                class="cu-badge {{ $item->type === 'found' ? 'green' : 'red' }}">{{ $item->type === 'found' ? 'Ditemukan' : 'Hilang' }}</span>
            <h1>{{ $item->title }}</h1>
            <dl class="cu-facts">
                <div>
                    <dt>Lokasi</dt>
                    <dd>{{ $item->location }}</dd>
                </div>
                <div>
                    <dt>Tanggal kejadian</dt>
                    <dd>{{ $item->event_date?->format('d M Y') }}</dd>
                </div>
                <div>
                    <dt>Nomor laporan</dt>
                    <dd>#{{ $item->id }}</dd>
                </div>
                <div>
                    <dt>Status</dt>
                    <dd>Terverifikasi admin</dd>
                </div>
            </dl>
            <h3>Deskripsi barang</h3>
            <p class="cu-description">{{ $item->description ?: 'Tidak ada deskripsi tambahan.' }}</p>
            <div class="cu-note">Simpan ciri kepemilikan yang hanya kamu ketahui untuk proses verifikasi klaim.</div>
            <div class="cu-actions"><a class="cu-btn light" href="{{ route('user.items.index') }}">← Kembali</a>
                @if ($item->type === 'found' && $item->user_id !== auth()->id())
                    <a class="cu-btn" href="{{ route('user.claims.create', $item) }}">Ajukan klaim →</a>
                @elseif($item->type === 'lost')
                    <a class="cu-btn green" href="{{ route('user.reports.found.create') }}">Saya menemukan barang →</a>
                @endif
            </div>
        </div>
    </section>
@endsection
