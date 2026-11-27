@extends('admin.layouts.items')
@section('title', 'Klaim Barang')
@section('breadcrumb', 'Klaim Barang')
@section('page-styles')
    <link rel='stylesheet' href='{{ asset('css/shared/findback-claims-navigation.css') }}' >
    <link rel="stylesheet" href="{{ asset('css/admin/claims-panel-stay.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/admin/claims-toast.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/admin/header-consistency.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('css/admin/admin-claims.css') }}?v=20261118-claims-v1">
    <link rel="stylesheet" href="{{ asset('css/admin/claims-premium-responsive.css') }}?v=20261118-claims-v3">
@endsection

@section('content')
<section class="fb-claim-executive fb-work-claims" aria-label="Ringkasan Klaim Barang">
        <div class="fb-page-heading fb-work-heading">
            <div>
                <p class="fb-eyebrow">ADMINISTRASI · FINDBACK KAMPUS</p>
                <h1>Klaim <span>Barang</span></h1>
                <p class="fb-heading-text">Tinjau bukti kepemilikan, proses keputusan klaim, dan pantau pengembalian barang.
                </p>
            </div>
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
        <div class="fb-claim-toast" data-fb-claim-toast data-duration="5000" role="status" aria-live="polite" aria-atomic="true">
            <span class="fb-claim-toast-icon" aria-hidden="true">✓</span>
            <span class="fb-claim-toast-copy"><strong>Proses berhasil</strong><small>{{ session('success') }}</small></span>
            <button type="button" class="fb-claim-toast-close" aria-label="Tutup notifikasi" data-fb-claim-toast-close>&times;</button>
        </div>
    @endif

    <section class="fb-claim-panel">
        <div class="fb-claim-panel-head">
            <div>
                <h2>Daftar Pengajuan Klaim</h2>
                <p>Pengajuan terbaru, dengan status menunggu ditampilkan terlebih dahulu.</p>
            </div>
            <span class="fb-claim-count">{{ $claims->total() }} hasil</span>
        </div>

        <form class="fb-claim-filters" method="GET" action="{{ route('admin.claims.index') }}">
            <input type="search" name="search" maxlength="100" value="{{ request('search') }}"
                placeholder="Cari barang, pengaju, lokasi, atau ID klaim..." aria-label="Cari pengajuan klaim" spellcheck="false" autocorrect="off" autocapitalize="off">
            <select name="status" aria-label="Status klaim">
                <option value="">Semua Status</option>
                @foreach (\App\Support\WorkflowStatus::claims() as $key => $label)
                    <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit">Terapkan</button>
            <a class="fb-claim-reset" href="{{ route('admin.claims.index') }}"><svg
                    viewBox="0 0 24 24" width="20" height="20" fill="none" aria-hidden="true">
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
                                        <small>K{{ $claim->id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $claim->claimant?->name ?? '—' }}</td>
                            <td>{{ $claim->item?->location ?? '—' }}</td>
                            <td>{{ $claim->created_at?->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j M Y') ?? '—' }}</td>
                            <td><span
                                    class="fb-claim-status status-{{ $claim->status }}">{{ \App\Support\WorkflowStatus::claims()[$claim->status] ?? $claim->status }}</span>
                            </td>
                            <td><div class="fb-claim-actions">
                                @include('admin.partials.detail-action', ['detailUrl' => route('admin.claims.show', $claim)])
                                @if ($claim->status === 'approved')
                                    <a class="fb-claim-chat-count" href="{{ route('admin.claims.show', ['claim' => $claim->id, 'chat' => 1]) }}"
                                        data-fb-claim-chat-count="{{ $claim->id }}" hidden></a>
                                @endif
                            </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                @if (request()->filled('search') || request()->filled('status'))
                                    <div class="fb-claim-empty" role="status"><span
                                            aria-hidden="true">&#9671;</span><strong>Tidak ada klaim yang sesuai filter</strong>
                                        <p>Coba ubah kata pencarian atau pilihan status untuk melihat pengajuan lainnya.</p>
                                        <a class="fb-claim-empty-action" href="{{ route('admin.claims.index') }}">Hapus
                                            Filter</a>
                                    </div>
                                @else
                                    <div class="fb-claim-empty" role="status"><span
                                            aria-hidden="true">&#9671;</span><strong>Belum ada pengajuan klaim</strong>
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
@endsection




