@extends('admin.layouts.items')

@section('title', 'Laporan Barang')

@section('item-upload-style')
    <link rel="stylesheet" href="{{ asset('css/admin/admin-items-upload.css') }}?v=premium-v4">
@endsection

@section('page-styles')
    <link rel="stylesheet" href="{{ asset('css/pages/admin/summary-emblems.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('css/admin/header-consistency.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('css/admin/items-table-consistency.css') }}?v=3">
    <link rel="stylesheet" href="{{ asset('css/admin/report-filters.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/admin/report-list-polish.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('css/admin/hover-consistency-v6.css') }}?v=20261115-6">
    <link rel="stylesheet" href="{{ asset('css/admin/hover-line-v7.css') }}?v=20261115-7">
@endsection

@section('content')


    {{-- FINDBACK_ADMIN_ITEMS_V16 --}}

    {{-- FINDBACK_ADMIN_ITEMS_V15_FIX --}}

    <div class="fb-items-heading fb-work-heading">

        <div>

            <p class="fb-eyebrow">ADMINISTRASI · FINDBACK KAMPUS</p>

            <h1>Laporan <span>Barang</span></h1>

            <p>Kelola dan telusuri laporan barang hilang maupun ditemukan secara terpusat.</p>

        </div>

        <div class="fb-admin-header-action-group">
            <button type="button" class="fb-items-tag fb-admin-add-button fb-admin-header-action"
                data-dialog-open="fb-admin-add-dialog" aria-describedby="fb-add-item-hint">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"
                    stroke-linecap="round" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9" /><path d="M12 8v8M8 12h8" /></svg>
                <span>Tambah Barang</span>
            </button>
            <p class="fb-admin-header-action-hint" id="fb-add-item-hint">Buat laporan barang ditemukan.</p>
        </div>

    </div>

    @if(session('success'))

        <div role="status" class="fb-admin-add-alert">{{ session('success') }}</div>

    @endif

        {{-- Popup laporan: SVG internal tanpa dependensi ikon eksternal --}}
    {{-- FORM UNGGAH LAPORAN ADMIN --}}
    <dialog id="fb-admin-add-dialog" class="fb-admin-add-dialog" aria-labelledby="fb-admin-add-title" aria-describedby="fb-admin-add-description"
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
                            <circle cx="12" cy="12" r="9" />
                            <path d="M12 8v8M8 12h8" />
                        </svg>
                    </span>
                    <div class="fb-add-title-copy">
                        <h2 id="fb-admin-add-title">Tambah Laporan Barang</h2>
                        <p id="fb-admin-add-description">Laporkan barang ditemukan. Semua kolom wajib diisi.</p>
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

                <label class="fb-add-field">
                    <span class="fb-add-label">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <rect x="3" y="7" width="18" height="14" rx="2" />
                            <path d="m3 10 9 5 9-5M12 15v6M8 3h8" />
                        </svg>
                        Jenis Laporan
                    </span>
                    <input type="text" value="Barang Ditemukan" readonly>
                    <input type="hidden" name="type" value="found">
                </label>

                <label class="fb-add-field">
                    <span class="fb-add-label">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="M4 7h16v13H4zM8 7V4h8v3M4 12h16" />
                        </svg>
                        Nama Barang
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
                        Lokasi Kejadian
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
                        Tanggal Kejadian
                    </span>
                    <input type="date" name="event_date" required
                           max="{{ now()->toDateString() }}"
                           value="{{ old('event_date', now()->toDateString()) }}">
                </label>

                <label class="fb-add-field fb-add-field-wide">
                    <span class="fb-add-label">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="1.8" stroke-linecap="round" aria-hidden="true">
                            <path d="M5 4h14v16H5zM8 8h8M8 12h8M8 16h5" />
                        </svg>
                        Deskripsi Barang
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
                        Unggah Foto Barang
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
                        <span class="fb-add-upload-hint" id="fb-add-file-rules">Format JPG, PNG, atau WebP · Maksimal 5 MB</span>
                        <span class="fb-add-file-name" id="fb-add-file-name" aria-live="polite">Belum ada foto yang dipilih</span>
                        <img id="fb-add-photo-preview" class="fb-add-photo-preview"
                             alt="Pratinjau foto barang" hidden>
                    </label>
                    <input id="fb-add-photo" class="fb-add-file-input" type="file" name="photo"
                           accept="image/jpeg,image/png,image/webp"
                           aria-describedby="fb-add-file-rules fb-add-file-name" required>
                </div>
            </div>

            {{-- 05. TOMBOL AKSI --}}
            <p role="status" class="fb-admin-add-alert" data-fb-publish-feedback hidden></p>
            <footer class="fb-admin-add-actions">
                {{-- 04. INFORMASI PUBLIKASI --}}
                <div class="fb-admin-add-note" role="note">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" />
                        <path d="m8 12 3 3 5-6" />
                    </svg>
                    <span>Laporan langsung <strong>Disetujui</strong> dan tampil di pencarian pengguna.</span>
                </div>
                <button type="button" class="fb-add-cancel"
                        data-dialog-close="fb-admin-add-dialog">Batal</button>
                <button type="submit" class="fb-add-submit">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"
                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 16V3m-5 5 5-5 5 5M4 16v4h16v-4" />
                    </svg>
                    Publikasikan Laporan
                </button>
            </footer>
        </form>
    </dialog>

    {{-- CSS dan JavaScript popup dimuat dari layout admin.items --}}


    <section class="fb-items-summary" aria-label="Ringkasan data barang">

        @foreach ([
            ['total', 'Total Barang', $total, 'Seluruh laporan tersimpan', 'M4 7h16v13H4z M3 4h18v3H3z M10 11h4'],
            ['lost', 'Barang Hilang', $lost, 'Laporan kehilangan', 'M21 21l-5-5 M18 11a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z'],
            ['found', 'Barang Ditemukan', $found, 'Laporan penemuan', 'M12 3 3 8v8l9 5 9-5V8z M3 8l9 5 9-5 M12 13v8'],
            ['returned', 'Barang Dikembalikan', $returned, 'Laporan selesai serah terima', 'M20 6 9 17l-5-5 M4 21h16'],
        ] as [$tone, $label, $count, $description, $iconPath])
            <article class="fb-summary-emblem-card is-{{ $tone }}">
                <div class="fb-summary-emblem-head">
                    <span class="fb-report-card-label">{{ $label }}</span>
                    <span class="fb-summary-emblem">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="{{ $iconPath }}" />
                        </svg>
                    </span>
                </div>
                <strong>{{ number_format($count, 0, ',', '.') }}</strong>
                <small>{{ $description }}</small>
            </article>
        @endforeach

    </section>

    <section class="fb-items-panel" aria-labelledby="fb-list-title">

        <div class="fb-items-panel-head">

            <div>

                <h2 id="fb-list-title">Daftar Laporan Barang</h2>

                <p>Cari atau saring seluruh laporan barang yang sudah lolos verifikasi.</p>

            </div>

            <span class="fb-items-count">{{ $items->total() }} hasil</span>

        </div>

        <form method="GET" action="{{ route('admin.items.index') }}" class="fb-items-filter fb-v20-filter fb-report-filter" role="search">

            <label class="fb-items-search fb-v20-search">

                <span class="sr-only">Cari nama barang, lokasi, atau kode barang</span>

                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">

                    <circle cx="11" cy="11" r="7" />

                    <path d="m16 16 5 5" />

                </svg>

                <input name="search" type="search" maxlength="100" placeholder="Cari barang, lokasi, atau BRG-0001..."

                    value="{{ request('search') }}" spellcheck="false" autocorrect="off" autocapitalize="off">

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

            <a href="{{ route('admin.items.index') }}" class="fb-items-reset fb-v20-action"><svg
                    viewBox="0 0 24 24" width="20" height="20" fill="none" aria-hidden="true">
                    <path d="M4 8.5H13.5C17.5 8.5 20 11 20 14.5C20 18 17.5 20 14 20H10"
                        stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M4.2 8.5L10 3.5V13.5L4.2 8.5Z" fill="currentColor" />
                </svg><span>Atur Ulang</span></a>

        </form>

        <p class="fb-v24-filter-help">Gunakan <strong>Atur Ulang</strong> jika kombinasi filter tidak menghasilkan data.
            Laporan yang masih diperiksa atau ditolak ada di
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

                                    title="Waktu laporan dicatat">{{ $item->created_at?->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j M Y, H:i') ?? '-' }}

                                    WIB</span></td>

                            <td data-fb-v34="status"><span class="fb-v34-label">Status laporan</span><span

                                    class="fb-items-badge status-{{ $item->status }}">{{ \App\Support\WorkflowStatus::reports()[$item->status] ?? $item->status }}</span>

                            </td>

                            <td data-fb-v34="aksi">

                                <div class="fb-v15-actions">

                                    @include('admin.partials.detail-action', ['detailUrl' => route('admin.items.show', $item)])

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="6">

                                <div class="fb-items-empty fb-report-empty">
                                    @if ($total <= 0)
                                        <strong>Belum ada laporan barang</strong>
                                        <p>Laporan yang tersedia akan muncul di sini.</p>
                                    @elseif ($items->total() > 0)
                                        <strong>Halaman ini tidak berisi laporan</strong>
                                        <p>Kembali ke halaman pertama untuk melihat hasil.</p>
                                        <a class="fb-items-reset" href="{{ $items->url(1) }}">Halaman Pertama</a>
                                    @else
                                        <strong>Tidak ada barang yang cocok dengan filter ini</strong>
                                        <p>Ubah filter atau tampilkan semua laporan.</p>
                                        <a class="fb-items-reset" href="{{ route('admin.items.index') }}">Atur Ulang Filter</a>
                                    @endif
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
