@extends('admin.layouts.items')
@section('title', 'Laporan Ditemukan')
@section('breadcrumb', 'Laporan Ditemukan')

@section('content')
    <div class="fb-items-heading">
        <div>
            <p class="fb-eyebrow">ADMINISTRASI · FINDBACK KAMPUS</p>
            <h1>Laporan <span>Ditemukan</span></h1>
            <p>Pantau laporan barang ditemukan yang masuk, periksa identitas pelapor dan status tindak lanjutnya.</p>
        </div>
        <span class="fb-items-tag">Monitoring Penemuan</span>
    </div>

    <section class="fb-items-summary fb-found-summary" aria-label="Ringkasan laporan ditemukan">
        <article><span>Total Laporan</span><strong>{{ number_format($total, 0, ',', '.') }}</strong><small>Seluruh laporan
                barang ditemukan</small></article>
        <article><span>Menunggu Verifikasi</span><strong>{{ number_format($pending, 0, ',', '.') }}</strong><small>Belum
                diperiksa admin</small></article>
        <article><span>Terverifikasi</span><strong>{{ number_format($verified, 0, ',', '.') }}</strong><small>Laporan telah
                disetujui</small></article>
        <article><span>Dikembalikan</span><strong>{{ number_format($returned, 0, ',', '.') }}</strong><small>Proses
                pengembalian selesai</small></article>
    </section>

    <section class="fb-items-panel" aria-labelledby="fb-found-list-title">
        <div class="fb-items-panel-head">
            <div>
                <h2 id="fb-found-list-title">Daftar Laporan Ditemukan</h2>
                <p>Cari, pantau, dan buka detail laporan penemuan</p>
            </div>
            <span class="fb-found-count">{{ $items->total() }} hasil</span>
        </div>

        <form method="GET" action="{{ route('admin.reports.found') }}" class="fb-items-filter fb-found-filter"
            role="search">
            <label class="fb-items-search">
                <span class="sr-only">Cari nama, lokasi, kategori, atau ID barang</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <circle cx="11" cy="11" r="7" />
                    <path d="m16 16 5 5" />
                </svg>
                <input name="search" type="search" maxlength="100" placeholder="Cari nama barang, lokasi, kategori, ID..."
                    value="{{ request('search') }}">
            </label>
            <label><span class="sr-only">Filter status laporan</span><select name="status">
                    <option value="">Semua Status</option>
                    @foreach (\App\Support\WorkflowStatus::reports() as $key => $label)
                        <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                    @endforeach
                </select></label>
            <button type="submit" class="fb-items-apply">Terapkan</button>
            <a class="fb-items-reset" href="{{ route('admin.reports.found') }}">Reset</a>
        </form>

        <p class="fb-found-helper">Halaman ini hanya menampilkan laporan barang <strong>Ditemukan</strong>. Perubahan status
            verifikasi akan tersedia di modul Verifikasi Laporan, sehingga tidak ada aksi persetujuan yang belum
            terimplementasi.</p>

        <div class="fb-items-table-wrap">
            <table class="fb-items-table">
                <thead>
                    <tr>
                        <th>BARANG</th>
                        <th>PELAPOR</th>
                        <th>LOKASI</th>
                        <th>TANGGAL</th>
                        <th>STATUS</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>
                                <div class="fb-items-name">
                                    <strong>{{ $item->title }}</strong><small>{{ \App\Support\WorkflowStatus::code($item->id) }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="fb-found-person">
                                    <strong>{{ $item->reporter?->name ?? 'Tidak diketahui' }}</strong><small>{{ $item->reporter?->email ?? '—' }}</small>
                                </div>
                            </td>
                            <td>{{ $item->location ?: '—' }}</td>
                            <td>{{ ($item->event_date ?? $item->created_at)?->locale('id')->translatedFormat('d M Y') ?? '—' }}
                            </td>
                            <td><span
                                    class="fb-items-badge fb-found-status status-{{ $item->status }}">{{ \App\Support\WorkflowStatus::reports()[$item->status] ?? $item->status }}</span>
                            </td>
                            <td><a class="fb-items-view fb-found-view" href="{{ route('admin.items.show', $item) }}">Detail
                                    <span aria-hidden="true">↗</span></a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="fb-items-empty"><span aria-hidden="true">⌕</span><strong>Belum ada laporan
                                        barang ditemukan yang sesuai</strong>
                                    <p>Belum ada laporan yang tersimpan atau pencarian tidak menemukan hasil.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($items->hasPages())
            <div class="fb-items-pagination"><span>Menampilkan {{ $items->firstItem() }}–{{ $items->lastItem() }} dari
                    {{ $items->total() }}</span>
                <div>
                    @if ($items->onFirstPage())
                    <span class="is-disabled">Sebelumnya</span>@else<a
                            href="{{ $items->previousPageUrl() }}">Sebelumnya</a>
                    @endif
                    <span>Halaman {{ $items->currentPage() }} / {{ $items->lastPage() }}</span>
                    @if ($items->hasMorePages())
                    <a href="{{ $items->nextPageUrl() }}">Berikutnya</a>@else<span
                            class="is-disabled">Berikutnya</span>
                    @endif
                </div>
            </div>
        @endif
    </section>
@endsection
