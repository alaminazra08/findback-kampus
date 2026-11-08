@extends('user.layouts.campus')
@section('title', 'Cari Barang')
@section('content')
    <div class="cu-title">
        <span class="cu-kicker">TEMUKAN KEMBALI YANG BERARTI</span>
        <h1>Pencarian barang</h1>
        <p>Cari barang hilang dan ditemukan berdasarkan nama, lokasi, jenis, atau tanggal.</p>
    </div>
    <form class="cu-panel cu-filter" method="GET">
        <div class="cu-search-row"><label class="cu-sr" for="search">Kata kunci</label><input id="search" name="search"
                maxlength="100" value="{{ request('search') }}"
                placeholder="Cari nama barang, deskripsi, atau lokasi..."><button
                class="cu-btn">@include('user.components.icon', ['icon' => 'search']) Cari barang</button></div>
        <div class="cu-filter-grid"><label>Lokasi<input name="location" maxlength="100" value="{{ request('location') }}"
                    placeholder="Gedung / lantai / ruangan"></label><label>Jenis<select name="type">
                    <option value="">Semua jenis</option>
                    <option value="lost" @selected(request('type') === 'lost')>Barang hilang</option>
                    <option value="found" @selected(request('type') === 'found')>Barang ditemukan</option>
                </select></label><label>Tanggal kejadian<input type="date" name="date"
                    value="{{ request('date') }}"></label><a class="cu-btn light"
                href="{{ route('user.items.index') }}">Reset filter</a></div>
    </form>
    <div class="cu-result"><strong>{{ $items->total() }} barang ditemukan</strong><span>Hanya laporan terverifikasi</span>
    </div>
    <div class="cu-cards">
        @forelse($items as $item)
            @include('user.components.card')@empty
            <div class="cu-empty">
                <h2>Belum ada hasil yang sesuai</h2>
                <p>Coba kata kunci lain atau hapus beberapa filter.</p><a class="cu-btn light"
                    href="{{ route('user.items.index') }}">Lihat semua barang</a>
            </div>
        @endforelse
    </div>@include('user.components.pages', ['paginator' => $items])
@endsection
