@extends('admin.layouts.items')
@section('title', 'Verifikasi Laporan')
@section('content')
<link rel="stylesheet" href="{{ asset('css/admin/findback-header-consistency-v2.css') }}?v=2">

    <link rel="stylesheet" href="{{ asset('css/admin/admin-verification.css') }}?v=31">
    <link rel="stylesheet" href="{{ asset('css/admin/verification-table-columns.css') }}?v=3">
    <link rel="stylesheet" href="{{ asset('css/admin/verification-photo-responsive.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/admin/findback-pm-detail-date.css') }}?v=1">
    <div class="fb-page-heading">
        <div>
            <p class="fb-eyebrow">ADMINISTRASI · FINDBACK KAMPUS</p>
            <h1>Verifikasi <span>Laporan</span></h1>
            <p class="fb-heading-text">Kelola laporan yang membutuhkan pemeriksaan dan tindak lanjut administrator.</p>
        </div><span class="fb-date-label fb-pm-date" aria-label="Tanggal hari ini"><svg viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="5" width="18" height="16" rx="2" />
                <path d="M7 3v4M17 3v4M3 10h18" />
            </svg>{{ now()->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d F Y') }}</span>
    </div>
    @if (session('success'))
        <div class="fb-v-alert is-success" role="status">{{ session('success') }}</div>
    @endif
    @if (session('warning'))
        <div class="fb-v-alert" role="alert">{{ session('warning') }}</div>
    @endif
    <div class="fb-v-stats fb-v-stats-v18">
        @foreach ([['label' => 'Antrean Verifikasi', 'value' => $pendingReadyCount, 'class' => 'pending', 'description' => 'Perlu diperiksa admin'], ['label' => 'Laporan Ditolak', 'value' => $openRevisionCount, 'class' => 'revision', 'description' => 'Ditolak dan menunggu perbaikan'], ['label' => 'Laporan Terverifikasi', 'value' => $verified, 'class' => 'approved', 'description' => 'Berstatus disetujui'], ['label' => 'Laporan Ditolak', 'value' => $rejected, 'class' => 'rejected', 'description' => 'Tidak memenuhi ketentuan']] as $stat)
            <div class="fb-v-stat {{ $stat['class'] }}">
                <span>{{ $stat['label'] }}</span><strong>{{ number_format($stat['value'], 0, ',', '.') }}</strong><small>{{ $stat['description'] }}</small>
            </div>
        @endforeach
    </div>
    <section class="fb-panel fb-v-panel">
        <div class="fb-panel-head">
            <div>
                <h2>{{ $revisionMode === 'history' ? 'Laporan Ditolak' : 'Antrean Verifikasi' }}</h2>
                <p>{{ $revisionMode === 'history' ? 'Laporan ditolak yang menunggu perbaikan dari User.' : 'Laporan yang perlu diperiksa, diurutkan dari yang paling lama.' }}
                </p>
            </div><span class="fb-panel-pill">{{ $items->total() }} hasil</span>
        </div>
        <nav class="fb-v-tabs" aria-label="Jenis antrean verifikasi">
            <a class="{{ $revisionMode === 'ready' ? 'active' : '' }}"
                href="{{ route('admin.reports.verification', array_filter(['search' => request('search'), 'type' => request('type')])) }}"
                @if ($revisionMode === 'ready') aria-current="page" @endif>Antrean Verifikasi
                <span>{{ $pendingReadyCount }}</span></a>
            <a class="{{ $revisionMode === 'history' ? 'active' : '' }}"
                href="{{ route('admin.reports.verification', array_merge(array_filter(['search' => request('search'), 'type' => request('type')]), ['revision' => 'history'])) }}"
                @if ($revisionMode === 'history') aria-current="page" @endif>Laporan Ditolak
                <span>{{ $openRevisionCount }}</span></a>
        </nav>
        <form class="fb-v-filters fb-v25-filters fb-v30-filters" method="GET"
            action="{{ route('admin.reports.verification') }}" role="search">
            <label class="fb-v30-search">
                <span class="sr-only">Cari barang, pelapor, lokasi, atau ID laporan</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                    aria-hidden="true">
                    <circle cx="11" cy="11" r="7" />
                    <path d="m16 16 5 5" />
                </svg>
                <input name="search" type="search" maxlength="100" value="{{ request('search') }}"
                    placeholder="Cari barang, pelapor, lokasi, atau ID...">
            </label>
            <label class="fb-v30-field"><span>Jenis Barang</span>
                <select name="type">
                    <option value="">Semua Jenis Barang</option>
                    <option value="lost" @selected(request('type') === 'lost')>Barang Hilang</option>
                    <option value="found" @selected(request('type') === 'found')>Barang Ditemukan</option>
                </select>
            </label>
            @if ($revisionMode === 'history')
                <input type="hidden" name="revision" value="history">
            @endif
            <button type="submit" class="fb-v30-apply"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.9" stroke-linejoin="round" aria-hidden="true">
                    <path d="M4 5h16l-6.5 7.5V19l-3 1v-7.5L4 5Z" />
                </svg><span>Terapkan</span></button>
            <a class="fb-v-reset fb-v30-reset"
                href="{{ route('admin.reports.verification', $revisionMode === 'history' ? ['revision' => 'history'] : []) }}"><svg
                    viewBox="0 0 24 24" width="20" height="20" fill="none" aria-hidden="true"
                    style="flex:none">
                    <path d="M4 8.5H13.5C17.5 8.5 20 11 20 14.5C20 18 17.5 20 14 20H10"
                        stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M4.2 8.5L10 3.5V13.5L4.2 8.5Z" fill="currentColor" />
                </svg><span>Atur Ulang</span></a>
        </form>
        <aside class="fb-v26-flow" aria-label="Panduan alur verifikasi">
            <span class="fb-v26-flow-icon" aria-hidden="true"><svg viewBox="0 0 24 24" width="19" height="19"
                    fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9" />
                    <path d="M12 10.5v5M12 7.4h.01" />
                </svg></span>
            <div class="fb-v26-flow-copy"><strong>Alur pemeriksaan laporan</strong>
                <p>Gunakan halaman ini untuk memeriksa dan memutuskan verifikasi. Hanya laporan yang telah diterima tampil
                    di <em>Laporan Barang</em>. Laporan ditolak tersimpan pada tab Laporan Ditolak dan menunggu perbaikan
                    User.</p>
            </div>
            <a class="fb-v26-flow-link" href="{{ route('admin.items.index', ['group' => 'semua']) }}"
                aria-label="Buka pusat Laporan Barang">Buka Laporan Barang <span aria-hidden="true">→</span></a>
        </aside>
        <div class="fb-table-scroll fb-v25-table-wrap">
            <table class="fb-user-table fb-v-table fb-v25-table">
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Jenis</th>
                        <th>Pelapor</th>
                        <th>Lokasi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr class="fb-v25-row">
                            <td data-label="Barang">
                                <div class="fb-v25-item">
                                    @if ($item->photo_path)
                                        <img class="fb-v25-thumb"
                                            src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->photo_path) }}"
                                            alt="Foto barang {{ $item->title }}" loading="lazy" decoding="async"
                                            data-image-fallback="grid">
                                        <span class="fb-v25-thumb-empty fb-refactor fb-initially-hidden"
                                            aria-label="Foto tidak tersedia"><svg viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                                <rect x="3" y="4" width="18" height="16" rx="2" />
                                                <circle cx="8" cy="9" r="1.5" />
                                                <path d="m4 17 5-5 4 4 3-3 4 4" />
                                            </svg></span>
                                    @else
                                        <span class="fb-v25-thumb-empty" aria-label="Belum ada foto"><svg
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.7" aria-hidden="true">
                                                <rect x="3" y="4" width="18" height="16" rx="2" />
                                                <circle cx="8" cy="9" r="1.5" />
                                                <path d="m4 17 5-5 4 4 3-3 4 4" />
                                            </svg></span>
                                    @endif
                                    <div class="fb-v25-item-copy">
                                        <strong>{{ $item->title }}</strong><small>{{ \App\Support\WorkflowStatus::code($item->id) }}</small><span
                                            class="fb-v25-date">{{ $item->created_at?->timezone('Asia/Jakarta')->format('d M Y, H:i') }}
                                            WIB</span>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Jenis"><span
                                    class="fb-v25-type {{ $item->type === 'lost' ? 'is-lost' : 'is-found' }}">{{ $item->type === 'lost' ? 'Hilang' : 'Ditemukan' }}</span>
                            </td>
                            <td data-label="Pelapor"><span
                                    class="fb-v25-detail-text">{{ $item->reporter?->name ?? '—' }}</span></td>
                            <td data-label="Lokasi"><span class="fb-v25-detail-text">{{ $item->location ?: '—' }}</span>
                            </td>
                            <td data-label="Status"><span
                                    class="fb-v-status {{ $revisionMode === 'history' ? 'status-revision' : 'status-pending' }}">{{ $revisionMode === 'history' ? 'Ditolak · Menunggu Perbaikan' : ($item->resubmitted ? 'Pemeriksaan Ulang' : 'Menunggu Verifikasi') }}</span>
                            </td>
                            <td data-label="Aksi"><a class="fb-v-detail fb-v25-detail"
                                    href="{{ route('admin.reports.verification.show', array_merge(request()->only(['search', 'type', 'page']), ['item' => $item, 'from' => $revisionMode === 'history' ? 'history' : 'queue'])) }}"><svg viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                        stroke-linejoin="round" aria-hidden="true">
                                        <path d="M2 12s3.6-6.5 10-6.5S22 12 22 12s-3.6 6.5-10 6.5S2 12 2 12Z" />
                                        <circle cx="12" cy="12" r="2.6" />
                                    </svg><span>{{ $revisionMode === 'history' ? 'Lihat Penolakan' : 'Periksa Laporan' }}</span></a>
                            </td>
                        </tr>
                    @empty<tr>
                            <td colspan="6" class="fb-empty">
                                <div class="fb-empty-state">
                                    <span>✓</span><strong>{{ request('search') || request('type') ? 'Tidak ditemukan laporan yang sesuai dengan filter.' : ($revisionMode === 'history' ? 'Tidak ada laporan yang menunggu perbaikan.' : 'Tidak ada laporan yang menunggu verifikasi.') }}</strong>
                                    <p>{{ request('search') || request('type') ? 'Ubah kata kunci atau reset filter.' : 'Laporan yang diterima tersedia di menu Laporan Barang.' }}
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($items->hasPages())
            <div class="fb-v-pagination">{{ $items->links() }}</div>
        @endif
    </section>
<script src="{{ asset('js/pages/admin/reports/verification/fast-tabs.js') }}?v=1" defer></script>
<script src="{{ asset('js/pages/admin/reports/verification/detail-prefetch.js') }}?v=1" defer></script>
<script src="{{ asset('js/pages/admin/reports/verification/return-context.js') }}?v=1" defer></script>

@endsection
