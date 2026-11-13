@extends('admin.layouts.items')
@section('title', 'Klaim Barang')
@section('breadcrumb', 'Klaim Barang')
@section('page-styles')
    <link rel="stylesheet" href="{{ asset('css/admin/header-consistency.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('css/admin/admin-claims.css') }}?v=20261101-executive">
    <link rel="stylesheet" href="{{ asset('css/admin/claims-table-consistency.css') }}?v=1">
@endsection

@section('content')

    <section class="fb-claim-executive" aria-label="Ringkasan Klaim Barang">
        <div class="fb-page-heading">
            <div>
                <p class="fb-eyebrow">ADMINISTRASI · FINDBACK KAMPUS</p>
                <h1>Klaim <span>Barang</span></h1>
                <p class="fb-heading-text">Tinjau bukti kepemilikan, proses keputusan klaim, dan pantau pengembalian barang.
                </p>
            </div>
            <span class="fb-date-label">{{ now()->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d F Y') }}</span>
        </div>

        <div class="fb-claim-stats">
            @foreach ([['label' => 'Total Klaim', 'value' => $total, 'tone' => ''], ['label' => 'Menunggu Pemeriksaan Klaim', 'value' => $pending, 'tone' => 'is-pending'], ['label' => 'Klaim Disetujui', 'value' => $approved, 'tone' => 'is-approved'], ['label' => 'Klaim Ditolak', 'value' => $rejected, 'tone' => 'is-rejected'], ['label' => 'Selesai Dikembalikan', 'value' => $returned, 'tone' => 'is-returned']] as $stat)
                <article class="fb-claim-stat {{ $stat['tone'] }}">
                    <span>{{ $stat['label'] }}</span>
                    <strong>{{ number_format($stat['value'], 0, ',', '.') }}</strong>
                    <p class="fb-claim-stat-note">
                        {{ match ($stat['tone']) {'' => 'Seluruh pengajuan tercatat','is-pending' => 'Perlu pemeriksaan Admin','is-approved' => 'Menunggu serah terima','is-rejected' => 'Pengajuan tidak disetujui','is-returned' => 'Serah terima telah selesai',default => ''} }}
                    </p>
                </article>
            @endforeach
        </div>
    </section>

    @if (session('success'))
        <div class="fb-claim-alert" role="status">{{ session('success') }}</div>
    @endif

    <section class="fb-claim-panel">
        <div class="fb-claim-panel-head">
            <div>
                <h2>Daftar Pengajuan Klaim</h2>
                <p>Pengajuan terbaru, dengan status menunggu ditampilkan terlebih dahulu.</p>
            </div>
            <span class="fb-claim-count">{{ $claims->total() }} hasil</span>
        </div>

        <nav class="fb-claim-tabs" aria-label="Filter cepat pengajuan klaim">
            @if (request('evidence') !== 'open')
            <span class="fb-claim-tab is-active" aria-current="page">Semua Klaim</span>@else<a class="fb-claim-tab"
                    href="{{ route('admin.claims.index') }}">Semua Klaim</a>
            @endif
            @if (request('evidence') === 'open')
            <span class="fb-claim-tab is-active" aria-current="page">Menunggu Bukti Tambahan</span>@else<a
                    class="fb-claim-tab" href="{{ route('admin.claims.index', ['evidence' => 'open']) }}">Menunggu Bukti
                    Tambahan</a>
            @endif
        </nav>
        <p class="fb-claim-filter-note">
            @if (request('evidence') === 'open')
                Hanya menampilkan klaim dengan permintaan bukti tambahan yang masih terbuka. Klaim lainnya tidak terhapus.
            @else
                Menampilkan seluruh pengajuan dan riwayat keputusan klaim. Gunakan tab Menunggu Bukti Tambahan untuk
                memantau permintaan aktif.
            @endif
        </p>
        <form class="fb-claim-filters" method="GET" action="{{ route('admin.claims.index') }}">
            @if (request('evidence') === 'open')
                <input type="hidden" name="evidence" value="open">
            @endif
            <input type="search" name="search" maxlength="100" value="{{ request('search') }}"
                placeholder="Cari barang, pengaju, lokasi, atau ID klaim..." aria-label="Cari pengajuan klaim">
            <select name="status" aria-label="Status klaim">
                <option value="">Semua Status</option>
                @foreach (\App\Support\WorkflowStatus::claims() as $key => $label)
                    <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit">Terapkan</button>
            <a class="fb-claim-reset" href="{{ route('admin.claims.index') }}"><svg
                    viewBox="0 0 24 24" width="20" height="20" fill="none" aria-hidden="true"
                    style="flex:none">
                    <path d="M4 8.5H13.5C17.5 8.5 20 11 20 14.5C20 18 17.5 20 14 20H10"
                        stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M4.2 8.5L10 3.5V13.5L4.2 8.5Z" fill="currentColor" />
                </svg><span>Atur Ulang</span></a>
        </form>

        <div class="fb-claim-table-scroll">
            <table class="fb-claim-table">
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Pengaju</th>
                        <th>Lokasi</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($claims as $claim)
                        <tr>
                            <td>
                                {{-- FINDBACK_CLAIM_THUMBNAIL_V1 --}}
                                <div class="fb-claim-item-media">
                                    @if ($claim->item?->photo_path)
                                        <img class="fb-claim-item-thumb"
                                            src="{{ asset('storage/' . $claim->item->photo_path) }}"
                                            alt="Foto barang {{ $claim->item->title }}" width="48" height="48"
                                            loading="lazy" data-image-fallback="grid">
                                        <span class="fb-claim-item-placeholder fb-refactor fb-initially-hidden"
                                            aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.5">
                                                <rect x="3" y="4" width="18" height="16" rx="2" />
                                                <circle cx="8.5" cy="9" r="1.5" />
                                                <path d="m3 17 5-5 4 4 3-3 6 6" />
                                            </svg>
                                        </span>
                                    @else
                                        <span class="fb-claim-item-placeholder" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.5">
                                                <rect x="3" y="4" width="18" height="16" rx="2" />
                                                <circle cx="8.5" cy="9" r="1.5" />
                                                <path d="m3 17 5-5 4 4 3-3 6 6" />
                                            </svg>
                                        </span>
                                    @endif
                                    <div class="fb-claim-item-copy">
                                        <strong>{{ $claim->item?->title ?? 'Barang tidak tersedia' }}</strong>
                                        <small>K{{ $claim->id }} @if ($claim->awaiting_evidence)
                                                <span> · Menunggu Bukti Tambahan</span>
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $claim->claimant?->name ?? '—' }}</td>
                            <td>{{ $claim->item?->location ?? '—' }}</td>
                            <td>{{ $claim->created_at?->timezone('Asia/Jakarta')->format('d/m/Y') ?? '—' }}</td>
                            <td><span
                                    class="fb-claim-status status-{{ $claim->status }}">{{ \App\Support\WorkflowStatus::claims()[$claim->status] ?? $claim->status }}</span>
                            </td>
                            <td><a class="fb-claim-detail-link" href="{{ route('admin.claims.show', $claim) }}">Periksa</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                @if (request('evidence') === 'open')
                                    <div class="fb-claim-empty" role="status"><span
                                            aria-hidden="true">◇</span><strong>Tidak ada permintaan bukti tambahan yang
                                            aktif</strong>
                                        <p>Saat ini tidak ada klaim yang sedang menunggu kelengkapan bukti. Klaim lain tetap
                                            tersedia di daftar Semua Klaim.</p><a class="fb-claim-empty-action"
                                            href="{{ route('admin.claims.index') }}">Lihat Semua Klaim</a>
                                    </div>
                                @elseif(request()->filled('search') || request()->filled('status'))
                                    <div class="fb-claim-empty" role="status"><span
                                            aria-hidden="true">◇</span><strong>Tidak ada klaim yang sesuai filter</strong>
                                        <p>Coba ubah kata pencarian atau pilihan status untuk melihat pengajuan lainnya.</p>
                                        <a class="fb-claim-empty-action" href="{{ route('admin.claims.index') }}">Hapus
                                            Filter</a>
                                    </div>
                                @else
                                    <div class="fb-claim-empty" role="status"><span
                                            aria-hidden="true">◇</span><strong>Belum ada pengajuan klaim</strong>
                                        <p>Pengajuan akan muncul setelah pengguna mengirimkan klaim melalui sistem.</p>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($claims->hasPages())
            <div class="fb-claim-pages">{{ $claims->links() }}</div>
        @endif
    </section>
    <footer class="fb-footer">© {{ date('Y') }} FindBack Kampus · Fakultas Sains dan Teknologi</footer>
@endsection
