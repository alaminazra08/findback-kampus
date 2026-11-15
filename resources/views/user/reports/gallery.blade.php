@extends('user.layouts.campus')
@section('title', $kind === 'found' ? 'Lapor Barang Ditemukan' : 'Lapor Barang Hilang')
@section('content')
    <div class="cu-section-title">
        <div>
            <span class="cu-kicker">{{ $kind === 'found' ? 'BARANG DITEMUKAN' : 'BARANG HILANG' }}</span>
            <h1>{{ $kind === 'found' ? 'Barang ditemukan di kampus' : 'Barang hilang di kampus' }}</h1>
            <p>
                {{ $kind === 'found'
                    ? 'Cek dulu apakah barang yang kamu temukan sudah pernah dilaporkan. Kalau belum, tambahkan.'
                    : 'Cek dulu apakah barangmu sudah pernah dilaporkan. Kalau belum, tambahkan laporan baru.' }}
            </p>
        </div>
        <a class="cu-btn {{ $kind === 'found' ? 'green' : '' }}"
            href="{{ route('user.reports.' . $kind . '.form') }}">+ Tambah barang</a>
    </div>

    <div class="cu-cards">
        @forelse($items as $item)
            @include('user.components.card')
        @empty
            <div class="cu-empty">
                <h3>Belum ada barang</h3>
                <p>Klik "Tambah barang" untuk membuat laporan pertama.</p>
            </div>
        @endforelse
    </div>
    @include('user.components.pages', ['paginator' => $items])
@endsection