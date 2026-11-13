@extends('admin.layouts.items')

@section('title', 'Laporan Barang')

@section('item-upload-style')
    <link rel="stylesheet" href="{{ asset('css/admin/admin-items-upload.css') }}?v=20261111-2">
@endsection

@section('page-styles')
    <link rel="stylesheet" href="{{ asset('css/admin/header-consistency.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('css/admin/items-table-consistency.css') }}?v=2">
@endsection

@section('content')


    {{-- FINDBACK_ADMIN_ITEMS_V16 --}}

    {{-- FINDBACK_ADMIN_ITEMS_V15_FIX --}}

    <div class="fb-items-heading">

        <div>

            <p class="fb-eyebrow">ADMINISTRASI · FINDBACK KAMPUS</p>

            <h1>Laporan <span>Barang</span></h1>

            <p>Kelola dan telusuri laporan barang hilang maupun ditemukan secara terpusat.</p>

        </div>

        <button type="button" class="fb-items-tag fb-admin-add-button" data-dialog-open="fb-admin-add-dialog">＋ Tambah Barang</button>

    </div>

    @if(session('success'))

        <div role="status" class="fb-admin-add-alert">{{ session('success') }}</div>

    @endif

        {{-- Popup laporan: SVG internal tanpa dependensi ikon eksternal --}}
    {{-- FORM UNGGAH LAPORAN ADMIN --}}
    <dialog id="fb-admin-add-dialog" class="fb-admin-add-dialog" aria-labelledby="fb-admin-add-title"
            data-has-errors="{{ $errors->any() ? 'true' : 'false' }}">
        <form method="POST"
              action="{{ route('admin.items.store') }}"
              enctype="multipart/form-data"
              class="fb-admin-add-form">
            @csrf

            {{-- 01. HEADER --}}
            <header class="fb-admin-add-top">
                <div class="fb-admin-add-title-row">
                    <span class="fb-add-title-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m12 3-9 5 9 5 9-5-9-5Z" />
                            <path d="M3 8v8l9 5 9-5V8" />
                            <path d="M12 13v8M16 5.2 7.5 10" />
                            <path d="M19 13v6M16 16h6" />
                        </svg>
                    </span>
                    <div class="fb-add-title-copy">
                        <h2 id="fb-admin-add-title">Unggah Laporan Barang</h2>
                        <p>Lengkapi informasi barang hilang atau ditemukan secara akurat sebelum dipublikasikan.</p>
                    </div>
                </div>
                <button type="button" class="fb-add-close" aria-label="Tutup formulir"
                        data-dialog-close="fb-admin-add-dialog">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" aria-hidden="true">
                        <path d="M5 5l14 14M19 5L5 19" />
                    </svg>
                </button>
            </header>

            {{-- 02. INPUT LAPORAN --}}
            <div class="fb-add-fields">
                <label class="fb-add-field">
                    <span class="fb-add-label">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <rect x="3" y="7" width="18" height="14" rx="2" />
                            <path d="m3 10 9 5 9-5M12 15v6M8 3h8" />
                        </svg>
                        Jenis Laporan <b aria-label="wajib"></b>
                    </span>
                    <select name="type" required>
                        <option value="lost" @selected(old('type', 'lost') === 'lost')>Barang Hilang</option>
                        <option value="found" @selected(old('type') === 'found')>Barang Ditemukan</option>
                    </select>
                </label>

                <label class="fb-add-field">
                    <span class="fb-add-label">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="M4 7h16v13H4zM8 7V4h8v3M4 12h16" />
                        </svg>
                        Nama Barang <b aria-label="wajib"></b>
                    </span>
                    <input type="text" name="title" maxlength="150" required
                           value="{{ old('title') }}" placeholder="Contoh: Dompet hitam">
                </label>

                <label class="fb-add-field">
                    <span class="fb-add-label">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z" />
                            <circle cx="12" cy="10" r="2.5" />
                        </svg>
                        Lokasi Kejadian <b aria-label="wajib"></b>
                    </span>
                    <input type="text" name="location" maxlength="255" required
                           value="{{ old('location') }}" placeholder="Contoh: Kantin kampus">
                </label>

                <label class="fb-add-field">
                    <span class="fb-add-label">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <rect x="3" y="5" width="18" height="16" rx="2" />
                            <path d="M7 3v4M17 3v4M3 10h18" />
                        </svg>
                        Tanggal Kejadian <b aria-label="wajib"></b>
                    </span>
                    <input type="date" name="event_date" required
                           max="{{ now()->toDateString() }}"
                           value="{{ old('event_date', now()->toDateString()) }}">
                </label>

                <label class="fb-add-field">
                    <span class="fb-add-label">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="1.8" stroke-linecap="round" aria-hidden="true">
                            <path d="M5 4h14v16H5zM8 8h8M8 12h8M8 16h5" />
                        </svg>
                        Deskripsi Barang <b aria-label="wajib"></b>
                    </span>
                    <textarea name="description" maxlength="5000" rows="4" required
                              placeholder="Tuliskan warna, ciri-ciri, dan kondisi barang...">{{ old('description') }}</textarea>
                </label>

                {{-- 03. FOTO BARANG --}}
                <div class="fb-add-field fb-add-photo-field">
                    <label for="fb-add-photo" class="fb-add-label">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <rect x="3" y="3" width="18" height="18" rx="3" />
                            <circle cx="8.5" cy="8.5" r="1.5" />
                            <path d="m4 18 5-5 4 4 3-3 4 4" />
                        </svg>
                        Unggah Foto Barang <b aria-label="wajib"></b>
                    </label>
                    <label for="fb-add-photo" class="fb-add-upload-zone" id="fb-add-upload-zone">
                        <span class="fb-add-upload-illustration" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="5" width="18" height="16" rx="3" />
                                <circle cx="8.5" cy="10" r="1.5" />
                                <path d="m5 18 4.5-5 3.5 3.5 2.5-3 3.5 4" />
                                <path d="M17 2v6M14 5h6" />
                            </svg>
                        </span>
                        <strong class="fb-add-upload-title">Pilih foto barang</strong>
                        <span class="fb-add-upload-hint">Format JPG, PNG, atau WebP · Maksimal 5 MB</span>
                        <span class="fb-add-file-name" id="fb-add-file-name" aria-live="polite">Belum ada foto yang dipilih</span>
                        <img id="fb-add-photo-preview" class="fb-add-photo-preview"
                             alt="Pratinjau foto barang" hidden>
                    </label>
                    <input id="fb-add-photo" class="fb-add-file-input" type="file" name="photo"
                           accept="image/jpeg,image/png,image/webp"
                           aria-describedby="fb-add-file-name" required>
                </div>

                {{-- 04. INFORMASI PUBLIKASI --}}
                <div class="fb-admin-add-note" role="note">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" />
                        <path d="m8 12 3 3 5-6" />
                    </svg>
                    <span>Laporan yang dibuat administrator akan langsung berstatus
                        <strong>Disetujui</strong> dan tersedia di pencarian pengguna.</span>
                </div>
            </div>

            {{-- 05. TOMBOL AKSI --}}
            <footer class="fb-admin-add-actions">
                <button type="button" class="fb-add-cancel"
                        data-dialog-close="fb-admin-add-dialog">Batal</button>
                <button type="submit" class="fb-add-submit">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"
                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 16V3m-5 5 5-5 5 5M4 16v4h16v-4" />
                    </svg>
                    Unggah Laporan
                </button>
            </footer>
        </form>
    </dialog>

    {{-- PESAN VALIDASI --}}
    @if ($errors->any())
        <div class="fb-admin-add-alert fb-admin-add-error" role="alert">
            <strong>Data belum berhasil disimpan:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- CSS dan JavaScript popup dimuat dari layout admin.items --}}


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
                    viewBox="0 0 24 24" width="20" height="20" fill="none" aria-hidden="true">
                    <path d="M4 8.5H13.5C17.5 8.5 20 11 20 14.5C20 18 17.5 20 14 20H10"
                        stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M4.2 8.5L10 3.5V13.5L4.2 8.5Z" fill="currentColor" />
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
