@extends('user.layouts.campus')
@section('title', 'Aktivitas Saya')
@section('content')
    <link rel="stylesheet" href="{{ asset('css/report-cycle.css') }}?v=7">
    

{{-- HEADER AKTIVITAS SAYA --}}
<div class="fb-activity-heading">
    <div class="fb-activity-heading-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"
            aria-hidden="true">
            <path d="M3 7.5A2.5 2.5 0 0 1 5.5 5H10l2 2h6.5A2.5 2.5 0 0 1 21 9.5l-1.5 9A2.5 2.5 0 0 1 17 20.5H5a2.5 2.5 0 0 1-2.5-2.5z"/>
            <path d="M3.5 9h16.8"/>
        </svg>
    </div>

    <div class="fb-activity-heading-text">
        <h1>Aktivitas Saya</h1>
        <p>Pantau perkembangan laporan dan klaim barangmu.</p>
    </div>
</div>

{{-- TAB AKTIVITAS --}}
<nav class="fb-activity-tabs" aria-label="Navigasi aktivitas">
    <a class="active" href="{{ route('user.reports.index') }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
            aria-hidden="true">
            <path d="M6 3h9l4 4v14H6z"/>
            <path d="M14 3v5h5M9 12h7M9 16h7"/>
        </svg>
        <span>Riwayat Laporan</span>
    </a>

    <a href="{{ route('user.claims.index') }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
            aria-hidden="true">
            <path d="m20.5 13.5-7.5 7.5-10-10 7.5-7.5a2 2 0 0 1 2.8 0l7.2 7.2a2 2 0 0 1 0 2.8Z"/>
            <circle cx="8.5" cy="8.5" r="1.3"/>
        </svg>
        <span>Pengajuan Klaim</span>
    </a>
</nav>


    <section class="cu-panel fb-cycle-report-list">
        
<form method="GET" action="{{ route('user.reports.index') }}" class="fb-activity-filter">
    <div class="fb-filter-search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true">
            <circle cx="10.8" cy="10.8" r="6.8"></circle>
            <path d="m16 16 5 5"></path>
        </svg>
        <input
            type="search"
            name="search"
            value="{{ request('search') }}"
            placeholder="Cari nama barang atau lokasi..."
            aria-label="Cari laporan">
    </div>

{{-- DROPDOWN JENIS --}}
<div class="fb-custom-select" data-custom-select>
    <input type="hidden" name="type" value="{{ request('type') ?? '' }}">

    <button type="button" class="fb-custom-trigger" aria-expanded="false">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="m12 3 9 5-9 5-9-5 9-5Z"></path>
            <path d="m3 12 9 5 9-5M3 16l9 5 9-5"></path>
        </svg>
        <span class="fb-custom-label">
            {{ request('type') === 'lost' ? 'Barang hilang' : (request('type') === 'found' ? 'Barang ditemukan' : 'Semua jenis') }}
        </span>
        <span class="fb-custom-chevron">⌄</span>
    </button>

    <div class="fb-custom-options">
        <button type="button" data-value="">Semua jenis</button>
        <button type="button" data-value="lost">Barang hilang</button>
        <button type="button" data-value="found">Barang ditemukan</button>
    </div>
</div>

{{-- DROPDOWN STATUS --}}
<div class="fb-custom-select" data-custom-select>
    <input type="hidden" name="status" value="{{ request('status') ?? '' }}">

    <button type="button" class="fb-custom-trigger" aria-expanded="false">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M4 4h16l-6.5 8v6l-3 2v-8L4 4Z"></path>
        </svg>
        <span class="fb-custom-label">
            @php
                $statusLabels = [
                    'pending' => 'Menunggu verifikasi',
                    'verified' => 'Terverifikasi',
                    'rejected' => 'Ditolak',
                    'claimed' => 'Diklaim',
                    'returned' => 'Dikembalikan',
                ];
            @endphp
            {{ $statusLabels[request('status')] ?? 'Semua status' }}
        </span>
        <span class="fb-custom-chevron">⌄</span>
    </button>

    <div class="fb-custom-options">
        <button type="button" data-value="">Semua status</button>
        @foreach ($statusLabels as $key => $label)
            <button type="button" data-value="{{ $key }}">{{ $label }}</button>
        @endforeach
    </div>
</div>


    <button type="submit" class="fb-filter-submit">
        <span>Terapkan</span>
    </button>

    <a href="{{ route('user.reports.index') }}" class="fb-filter-reset">
        Reset
    </a>
</form>


<div class="cu-table-wrap">
    <table class="fb-report-table">
        <thead>
            <tr>
                <th>No.</th>
                <th>Barang</th>
                <th>Jenis</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $report)
                <tr>
                    <td data-label="No.">
                        <span class="fb-report-number">
                            {{ $reports->firstItem() + $loop->index }}
                        </span>
                    </td>
                    
<td data-label="Barang">
    <div class="fb-report-item">
        <div class="fb-report-photo">
            @if ($report->photo_path)
                <img
                    src="{{ asset('storage/' . $report->photo_path) }}"
                    alt="Foto {{ $report->title }}"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                >
                <div class="fb-report-photo-placeholder" style="display:none;">
                    <span>▧</span>
                </div>
            @else
                <div class="fb-report-photo-placeholder">
                    <span>▧</span>
                </div>
            @endif
        </div>

        <div class="fb-report-item-info">
            <strong>{{ $report->title }}</strong>
            <small>
                <span aria-hidden="true">⌖</span>
                {{ $report->location }}
            </small>
        </div>
    </div>
</td>

                    <td data-label="Jenis">
                        <span class="fb-report-type {{ $report->type === 'lost' ? 'lost' : 'found' }}">
                            {{ $report->type === 'lost' ? 'Hilang' : 'Ditemukan' }}
                        </span>
                    </td>
                    <td data-label="Tanggal">
                        {{ $report->event_date?->format('d M Y') }}
                    </td>
                    <td data-label="Status">
                        @if ($report->status === 'rejected')
                            <span class="cu-badge amber">Ditolak · Perlu Perbaikan</span>
                        @elseif ($report->status === 'pending' && $report->awaiting_revision)
                            <span class="cu-badge amber">Menunggu perbaikan Anda</span>
                        @else
                            @include('user.components.status', ['status' => $report->status])
                        @endif
                    </td>
                    <td data-label="Aksi">
                        
<a class="fb-report-view"
   href="{{ route('user.reports.show', $report) }}">
    @if ($report->status === 'rejected' || ($report->status === 'pending' && $report->awaiting_revision))
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
            aria-hidden="true">
            <path d="M12 5H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-6"/>
            <path d="m14 4 6 6M20 4l-9 9-4 1 1-4 9-9"/>
        </svg>
        Perbaiki laporan
    @else
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
            aria-hidden="true">
            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
            <circle cx="12" cy="12" r="3"/>
        </svg>
        Lihat
    @endif
</a>

                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        <div class="cu-empty">
                            Belum ada laporan yang sesuai.
                            <br>
                            <a href="{{ route('user.reports.lost.create') }}">
                                Buat laporan pertama →
                            </a>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@include('user.components.pages', ['paginator' => $reports])
    </section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const dropdowns = document.querySelectorAll('[data-custom-select]');

    dropdowns.forEach(function (dropdown) {
        const trigger = dropdown.querySelector('.fb-custom-trigger');
        const input = dropdown.querySelector('input[type="hidden"]');
        const label = dropdown.querySelector('.fb-custom-label');
        const options = dropdown.querySelectorAll('.fb-custom-options button');

        if (!trigger || !input || !label) return;

        // Membuka dan menutup dropdown
        trigger.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();

            const isOpen = dropdown.classList.contains('open');

            dropdowns.forEach(function (item) {
                item.classList.remove('open');

                const button = item.querySelector('.fb-custom-trigger');
                if (button) button.setAttribute('aria-expanded', 'false');
            });

            if (!isOpen) {
                dropdown.classList.add('open');
                trigger.setAttribute('aria-expanded', 'true');
            }
        });

        // Memilih pilihan dropdown
        options.forEach(function (option) {
            option.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();

                input.value = option.dataset.value;
                label.textContent = option.textContent.trim();

                options.forEach(function (item) {
                    item.classList.remove('selected');
                });

                option.classList.add('selected');

                dropdown.classList.remove('open');
                trigger.setAttribute('aria-expanded', 'false');
            });
        });

        // Menandai pilihan yang sedang aktif
        options.forEach(function (option) {
            if (option.dataset.value === input.value) {
                option.classList.add('selected');
                label.textContent = option.textContent.trim();
            }
        });
    });

    // Menutup dropdown ketika klik di luar
    document.addEventListener('click', function (event) {
        dropdowns.forEach(function (dropdown) {
            if (!dropdown.contains(event.target)) {
                dropdown.classList.remove('open');

                const trigger = dropdown.querySelector('.fb-custom-trigger');
                if (trigger) trigger.setAttribute('aria-expanded', 'false');
            }
        });
    });

    // Menutup dropdown dengan tombol Escape
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            dropdowns.forEach(function (dropdown) {
                dropdown.classList.remove('open');

                const trigger = dropdown.querySelector('.fb-custom-trigger');
                if (trigger) trigger.setAttribute('aria-expanded', 'false');
            });
        }
    });
});
</script>

@endsection
