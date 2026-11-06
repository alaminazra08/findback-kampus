@extends('admin.layouts.items')

@section('title', 'Laporan Barang Hilang')

@section('content')

    @php
        $reports = $items ?? ($reports ?? null);

        $totalLaporan = $totalReports ?? ($totalLost ?? 0);
        $menunggu = $pendingCount ?? ($totalPending ?? 0);
        $terverifikasi = $verifiedCount ?? ($totalVerified ?? 0);
        $dikembalikan = $returnedCount ?? ($totalReturned ?? 0);
    @endphp

    {{-- =====================================================
     HEADER HALAMAN
===================================================== --}}

    <div class="fb-page-heading">
        <div>
            <p class="fb-eyebrow">
                ADMINISTRASI · FINDBACK KAMPUS
            </p>

            <h1>
                Laporan <span>Hilang</span>
            </h1>

            <p class="fb-heading-text">
                Pantau dan kelola seluruh laporan kehilangan barang
                di lingkungan Fakultas Sains dan Teknologi.
            </p>
        </div>

        <span class="fb-date-label">
            {{ now()->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d F Y') }}
        </span>
    </div>

    {{-- =====================================================
     STATISTIK LAPORAN
===================================================== --}}

    <div class="fb-report-cards">

        <article class="fb-report-card is-lost">
            <div class="fb-report-card-top">
                <span class="fb-report-icon">▤</span>
                <span class="fb-report-card-label">
                    Total Laporan
                </span>
            </div>
            <strong>{{ number_format($totalLaporan, 0, ',', '.') }}</strong>
            <small>Seluruh laporan kehilangan</small>
        </article>

        <article class="fb-report-card is-claim">
            <div class="fb-report-card-top">
                <span class="fb-report-icon">◷</span>
                <span class="fb-report-card-label">
                    Menunggu Verifikasi
                </span>
            </div>
            <strong>{{ number_format($menunggu, 0, ',', '.') }}</strong>
            <small>Belum diperiksa administrator</small>
        </article>

        <article class="fb-report-card is-found">
            <div class="fb-report-card-top">
                <span class="fb-report-icon">✓</span>
                <span class="fb-report-card-label">
                    Terverifikasi
                </span>
            </div>
            <strong>{{ number_format($terverifikasi, 0, ',', '.') }}</strong>
            <small>Laporan telah disetujui</small>
        </article>

        <article class="fb-report-card is-returned">
            <div class="fb-report-card-top">
                <span class="fb-report-icon">◇</span>
                <span class="fb-report-card-label">
                    Dikembalikan
                </span>
            </div>
            <strong>{{ number_format($dikembalikan, 0, ',', '.') }}</strong>
            <small>Barang sudah dikembalikan</small>
        </article>

    </div>

    {{-- =====================================================
     DAFTAR LAPORAN
===================================================== --}}

    <section class="fb-panel fb-recent-reports">

        <div class="fb-panel-head">
            <div>
                <h2>Daftar Laporan Hilang</h2>
                <p>
                    Telusuri laporan kehilangan yang tersimpan
                    dalam database FindBack Kampus.
                </p>
            </div>

            <span class="fb-panel-pill">
                {{ $reports ? $reports->total() : 0 }} laporan
            </span>
        </div>

        {{-- PENCARIAN DAN FILTER --}}

        <form method="GET" action="{{ route('admin.reports.lost') }}" class="fb-refactor fb-report-filter">

            <input type="search" name="search" value="{{ request('search') }}"
                placeholder="Cari nama barang, kategori, lokasi..." aria-label="Cari laporan hilang"
                class="fb-refactor fb-report-search">

            <select name="status" aria-label="Filter status" class="fb-refactor fb-report-select">

                <option value="">Semua Status</option>

                @foreach (\App\Support\WorkflowStatus::reports() as $value => $label)
                    <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="fb-refactor fb-report-apply">
                Terapkan
            </button>

            <a href="{{ route('admin.reports.lost') }}" class="fb-refactor fb-report-reset">
                Reset
            </a>

        </form>

        {{-- TABEL DATA --}}

        <div class="fb-table-scroll">

            <table class="fb-user-table fb-report-table">

                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Pelapor</th>
                        <th>Lokasi</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($reports ?? [] as $item)
                        @php
                            $statusLabels = \App\Support\WorkflowStatus::reports();

                            $currentStatus = $item->status ?? 'pending';
                        @endphp

                        <tr>

                            <td>
                                <strong>
                                    {{ $item->title ?? ($item->name ?? 'Tanpa Nama') }}
                                </strong>

                                <div class="fb-refactor fb-report-caption">
                                    {{ \App\Support\WorkflowStatus::code($item->id) }}
                                </div>
                            </td>

                            <td>
                                {{ $item->reporter?->name ?? '—' }}
                            </td>

                            <td>
                                {{ $item->location ?? '—' }}
                            </td>

                            <td>
                                {{ $item->event_date
                                    ? \Carbon\Carbon::parse($item->event_date)->format('d/m/Y')
                                    : $item->created_at?->format('d/m/Y') ?? '—' }}
                            </td>

                            <td>
                                <span class="fb-report-status status-{{ $report['status'] ?? '' }}">
                                    {{ $statusLabels[$currentStatus] ?? ucfirst($currentStatus) }}
                                </span>
                            </td>

                            <td>
                                <a href="{{ route('admin.items.show', $item->id) }}"
                                    class="fb-refactor fb-report-detail-link">
                                    Lihat Detail ↗
                                </a>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="6" class="fb-empty">

                                <div class="fb-empty-state">
                                    <span>▤</span>

                                    <strong>
                                        Belum Ada Laporan Barang Hilang
                                    </strong>

                                    <p>
                                        Laporan kehilangan akan ditampilkan
                                        di sini setelah pengguna mengirimkan
                                        laporan melalui FindBack Kampus.
                                    </p>
                                </div>

                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>

        </div>

        {{-- PAGINATION --}}

        @if ($reports && $reports->hasPages())
            <div class="fb-refactor fb-space-top">
                {{ $reports->withQueryString()->links() }}
            </div>
        @endif

    </section>

    {{-- FOOTER --}}

    <footer class="fb-footer">
        © {{ now()->year }} FindBack Kampus
        <span>·</span>
        Fakultas Sains dan Teknologi
    </footer>

@endsection
