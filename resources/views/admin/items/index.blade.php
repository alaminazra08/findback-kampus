@extends('admin.layouts.items')
@section('title', 'Laporan Barang')

@section('content')
    {{-- FINDBACK_ADMIN_ITEMS_V16 --}}

    {{-- FINDBACK_ADMIN_ITEMS_V15_FIX --}}

    <div class="fb-items-heading">
        <div>
            <p class="fb-eyebrow">ADMINISTRASI · FINDBACK KAMPUS</p>
            <h1>Laporan <span>Barang</span></h1>
            <p>Kelola dan telusuri laporan barang hilang maupun ditemukan secara terpusat.</p>
        </div>
        <span class="fb-items-tag">Pusat Laporan</span>
    </div>

    <section class="fb-items-summary" aria-label="Ringkasan data barang">
        <article><span>Total Barang</span><strong>{{ number_format($total, 0, ',', '.') }}</strong><small>Seluruh laporan
                tersimpan</small></article>
        <article><span>Barang Hilang</span><strong>{{ number_format($lost, 0, ',', '.') }}</strong><small>Laporan
                kehilangan</small></article>
        <article><span>Barang Ditemukan</span><strong>{{ number_format($found, 0, ',', '.') }}</strong><small>Laporan
                penemuan</small></article>
        <article><span>Barang Dikembalikan</span><strong>{{ number_format($returned, 0, ',', '.') }}</strong><small>Laporan
                selesai serah terima</small></article>
    </section>

    <section class="fb-items-panel" aria-labelledby="fb-list-title">
        <div class="fb-items-panel-head">
            <div>
                <h2 id="fb-list-title">Daftar Barang</h2>
                <p>Cari dan saring laporan barang FindBack Kampus.</p>
            </div>
            <span class="fb-items-count">{{ $items->total() }} hasil</span>
        </div>

        {{-- FINDBACK_FILTER_POLOS_V23 --}}
        <p class="fb-cycle-notice">Filter jenis, tahap, dan status diterapkan bersama. Kombinasi yang bertentangan
            menghasilkan daftar kosong; gunakan Atur Ulang untuk melihat semua laporan.</p>
        <form method="GET" action="{{ route('admin.items.index') }}" class="fb-items-filter fb-v20-filter" role="search">
            <label class="fb-items-search fb-v20-search">
                <span class="sr-only">Cari nama barang, lokasi, atau kode barang</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <circle cx="11" cy="11" r="7" />
                    <path d="m16 16 5 5" />
                </svg>
                <input name="search" type="search" maxlength="100" placeholder="Cari barang, lokasi, atau BRG-0001..."
                    value="{{ request('search') }}">
            </label>
            <label class="fb-v20-field"><span>Jenis Barang</span><select name="type">
                    <option value="">Semua Jenis Barang</option>
                    <option value="lost" @selected(request('type') === 'lost')>Barang Hilang</option>
                    <option value="found" @selected(request('type') === 'found')>Barang Ditemukan</option>
                </select></label>
            <label class="fb-v20-field"><span>Tahap Laporan</span><select name="group">
                    <option value="semua" @selected($group === 'semua')>Semua Tahap Laporan</option>
                    <option value="aktif" @selected($group === 'aktif')>Aktif (Terverifikasi/Klaim)</option>
                    <option value="selesai" @selected($group === 'selesai')>Selesai (Dikembalikan)</option>
                </select></label>
            <label class="fb-v20-field"><span>Status Laporan</span><select name="status">
                    <option value="">Semua Status Laporan</option>
                    @foreach (array_intersect_key(\App\Support\WorkflowStatus::reports(), array_flip(['verified', 'claimed', 'returned'])) as $key => $value)
                        <option value="{{ $key }}" @selected(request('status') === $key)>{{ $value }}</option>
                    @endforeach
                </select></label>
            <button type="submit" class="fb-items-apply fb-v20-action"><svg viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1.9" stroke-linejoin="round" aria-hidden="true">
                    <path d="M4 5h16l-6.5 7.5V19l-3 1v-7.5L4 5Z" />
                </svg><span>Terapkan</span></button>
            <a href="{{ route('admin.items.index', ['group' => 'semua']) }}" class="fb-items-reset fb-v20-action"><svg
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"
                    aria-hidden="true">
                    <path d="M3 11a9 9 0 1 1 2.8 6.5" />
                    <path d="M3 4v7h7" />
                </svg><span>Atur Ulang</span></a>
        </form>
        <p class="fb-v24-filter-help">Status Laporan yang dipilih secara khusus akan menjadi acuan utama pencarian. Halaman
            ini hanya menampilkan laporan yang telah diterima. Laporan menunggu verifikasi dan ditolak dikelola di
            Verifikasi Laporan.</p>
        <div class="fb-items-table-wrap">
            <table class="fb-items-table">
                <thead>
                    <tr>
                        <th>BARANG</th>
                        <th>JENIS</th>
                        <th>LOKASI</th>
                        <th>WAKTU LAPORAN</th>
                        <th>STATUS</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td data-fb-v34="barang">
                                <div class="fb-v20-item-cell">
                                    @if ($item->photo_path)
                                        <img class="fb-v20-thumb"
                                            src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->photo_path) }}"
                                            alt="Foto barang {{ $item->title }}" loading="lazy" decoding="async"
                                            data-image-fallback="grid">
                                        <span class="fb-v20-thumb-placeholder fb-refactor fb-initially-hidden"
                                            aria-label="Foto tidak tersedia"><svg viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                                <rect x="3" y="4" width="18" height="16" rx="2" />
                                                <circle cx="8" cy="9" r="1.4" />
                                                <path d="m4 17 5-5 4 4 3-3 4 4" />
                                            </svg></span>
                                    @else
                                        <span class="fb-v20-thumb-placeholder" aria-label="Tidak ada foto"><svg
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
                                                aria-hidden="true">
                                                <rect x="3" y="4" width="18" height="16" rx="2" />
                                                <circle cx="8" cy="9" r="1.4" />
                                                <path d="m4 17 5-5 4 4 3-3 4 4" />
                                            </svg></span>
                                    @endif
                                    <div class="fb-items-name">
                                        <strong>{{ $item->title }}</strong><small>BRG-{{ str_pad((string) $item->id, 4, '0', STR_PAD_LEFT) }}</small>
                                    </div>
                                </div>
                            </td>
                            <td data-fb-v34="jenis"><span
                                    class="fb-items-badge {{ $item->type === 'found' ? 'is-found' : 'is-lost' }}">{{ $item->type === 'found' ? 'Ditemukan' : 'Hilang' }}</span>
                            </td>
                            <td data-fb-v34="lokasi"><span class="fb-v34-label">Lokasi</span><span
                                    class="fb-v34-text">{{ $item->location }}</span></td>
                            <td data-fb-v34="waktu"><span class="fb-v34-label">Waktu laporan</span><span
                                    class="fb-v16-date"
                                    title="Waktu laporan dicatat">{{ $item->created_at?->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d M Y, H:i') ?? '-' }}
                                    WIB</span></td>
                            <td data-fb-v34="status"><span class="fb-v34-label">Status laporan</span><span
                                    class="fb-items-badge status-{{ $item->status }}">{{ \App\Support\WorkflowStatus::reports()[$item->status] ?? $item->status }}</span>
                            </td>
                            <td data-fb-v34="aksi">
                                <div class="fb-v15-actions">
                                    <a class="fb-items-view fb-v16-detail" href="{{ route('admin.items.show', $item) }}"
                                        aria-label="Lihat detail barang {{ $item->title }}"><svg viewBox="0 0 24 24"
                                            width="15" height="15" fill="none" stroke="currentColor"
                                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                                            aria-hidden="true">
                                            <path d="M2 12s3.6-6 10-6 10 6 10 6-3.6 6-10 6S2 12 2 12Z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg><span>Lihat Detail</span></a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="fb-items-empty"><span aria-hidden="true">⌕</span><strong>Belum ada barang yang
                                        sesuai</strong>
                                    <p>Belum terdapat laporan pada database atau tidak ada hasil untuk filter ini.</p>
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
    {{-- FINDBACK_ADMIN_AKSI_V17_START --}}

    {{-- FINDBACK_ADMIN_AKSI_V17_END --}}

@endsection
