@extends('admin.layouts.items')

@section('title', 'Pencocokan Barang')
@section('breadcrumb', 'Pencocokan Barang')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin/findback-header-consistency-v2.css') }}?v=2">

    {{-- FINDBACK_ICON_PREMIUM_FIX_V2 --}}
    <link rel="stylesheet" href="{{ asset('css/admin/admin-matching.css') }}?v=match-header-align-1">
    <div class="match-wrap">
        <div class="match-heading match-index-heading">
            <div>
                <p class="match-kicker">ADMINISTRASI · FINDBACK KAMPUS</p>
                <h1>Pencocokan <span>Barang</span></h1>
                <p>Bandingkan laporan barang hilang dan ditemukan untuk menghasilkan kandidat yang ditinjau administrator.
                </p>
            </div>
            <form method="POST" action="{{ route('admin.matching.generate') }}"
                data-confirm="Jalankan pencocokan laporan terverifikasi? Kandidat yang sudah diperiksa tidak akan diubah."
                data-confirm-event="submit">
                @csrf
                <button class="match-button" type="submit"><svg class="match-action-svg" viewBox="0 0 24 24"
                        aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="1.9"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 3h5v5" />
                        <path d="M4 20l7-7" />
                        <path d="M21 3l-7.5 7.5" />
                        <path d="M14 14l7 7" />
                        <path d="M21 16v5h-5" />
                        <path d="M4 4l6 6" />
                    </svg><span>Jalankan Pencocokan</span></button>
                <p class="match-generation-hint">Kandidat baru tetap membutuhkan keputusan Admin.</p>
            </form>
        </div>

        @if (session('success'))
            <div class="match-flash success" role="status">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="match-flash error" role="alert">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="match-flash error" role="alert">{{ $errors->first() }}</div>
        @endif

        <section class="match-stats" aria-label="Statistik pencocokan">
            <div class="match-stat match-stat-all"><small>Total
                    Kandidat</small><strong>{{ number_format($stats['all'], 0, ',', '.') }}</strong><span>Semua pasangan
                    tersimpan</span></div>
            <div class="match-stat match-stat-pending"><small>Perlu
                    Pemeriksaan</small><strong>{{ number_format($stats['pending'], 0, ',', '.') }}</strong><span>Menunggu
                    keputusan</span></div>
            <div class="match-stat match-stat-confirmed"><small>Dikonfirmasi
                    Cocok</small><strong>{{ number_format($stats['confirmed'], 0, ',', '.') }}</strong><span>Pasangan
                    terverifikasi Admin</span></div>
            <div class="match-stat match-stat-rejected"><small>Tidak
                    Cocok</small><strong>{{ number_format($stats['rejected'], 0, ',', '.') }}</strong><span>Bukan pasangan
                    yang cocok</span></div>
        </section>

        <section class="match-panel">
            <div class="match-panel-top">
                <div class="match-list-title">
                    <div>
                        <h2>Daftar Kandidat</h2>
                        <p>Cari berdasarkan barang, lokasi, atau nomor pencocokan.</p>
                    </div>
                </div>
                <form class="match-filters" method="GET" action="{{ route('admin.matching.index') }}">
                    <input type="search" name="q" maxlength="100" placeholder="Cari nama barang / lokasi ..."
                        value="{{ $search }}" aria-label="Cari kandidat">
                    <select name="status" aria-label="Filter status">
                        <option value="all" @selected($status === 'all')>Semua status</option>
                        <option value="pending" @selected($status === 'pending')>Perlu Pemeriksaan</option>
                        <option value="confirmed" @selected($status === 'confirmed')>Cocok</option>
                        <option value="rejected" @selected($status === 'rejected')>Tidak Cocok</option>
                    </select>
                    <button class="match-button secondary match-apply-premium" type="submit"><svg class="match-action-icon"
                            viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M4 5h16l-6.5 7.5V19l-3 1.5v-8L4 5Z" />
                        </svg><span>Terapkan</span></button>
                </form>
            </div>
            <p class="match-guidance"> Persentase kemiripan merupakan skor sistem untuk membantu peninjauan, bukan keputusan
                akhir kepemilikan. Konfirmasi kandidat tidak otomatis menyetujui klaim.</p>
            <div class="match-table-scroll">
                <table class="match-table">
                    <thead>
                        <tr>
                            <th>NOMOR</th>
                            <th>Barang Hilang</th>
                            <th>Barang Ditemukan</th>
                            <th>Kemiripan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($matches as $match)
                            <tr>
                                <td data-label="Nomor">{{ $match->id }}</td>
                                <td data-label="Barang hilang">
                                    <div class="match-item-cell match-item-cell-lost">
                                        <div>
                                            <strong>{{ $match->lostItem?->title ?? 'Laporan tidak tersedia' }}</strong><small>{{ $match->lostItem?->location ?? '—' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Barang ditemukan">
                                    <div class="match-item-cell match-item-cell-found">
                                        <div>
                                            <strong>{{ $match->foundItem?->title ?? 'Laporan tidak tersedia' }}</strong><small>{{ $match->foundItem?->location ?? '—' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Skor kemiripan"><span
                                        class="match-score">{{ number_format((float) $match->match_score, 0) }}%</span>
                                </td>
                                <td data-label="Status"><span
                                        class="match-pill {{ $match->status }}">{{ \App\Support\WorkflowStatus::matches()[$match->status] ?? $match->status }}</span>
                                </td>
                                <td data-label="Aksi"><a class="match-detail-link"
                                        href="{{ route('admin.matching.show', $match) }}"><svg class="match-action-svg"
                                            viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none"
                                            stroke="currentColor" stroke-width="1.9" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg><span>Lihat Detail</span></a></td>
                            </tr>
                        @empty
                            <tr>
                                <td class="match-empty" colspan="6"><strong>Belum ada kandidat pencocokan</strong>
                                    <p>Jalankan pencocokan setelah tersedia laporan hilang dan ditemukan yang berstatus
                                        Terverifikasi.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($matches->hasPages())
                <div class="match-pagination">{{ $matches->links() }}</div>
            @endif
        </section>
    </div>
@endsection
