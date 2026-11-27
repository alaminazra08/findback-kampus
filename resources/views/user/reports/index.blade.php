@extends('user.layouts.campus')

@section('title', 'Aktivitas Saya')

@section('content')
    

@include('user.components.activity-heading', ['activityTab' => 'reports'])

    @include('user.components.activity-summary', ['activityStats' => [
        ['label' => 'Total Laporan', 'value' => $totalReports, 'tone' => 'rose', 'icon' => 'report'],
        ['label' => 'Dalam Proses', 'value' => $pendingReports, 'tone' => 'amber', 'icon' => 'clock'],
        ['label' => 'Terverifikasi', 'value' => $verifiedReports, 'tone' => 'green', 'icon' => 'check'],
        ['label' => 'Pengajuan Klaim', 'value' => $totalClaims, 'tone' => 'violet', 'icon' => 'claim'],
    ]])



    <section class="cu-panel fb-cycle-report-list fb-report-list-premium">
        <div class="fb-report-list-heading">
            <div class="fb-report-list-heading-copy">
                <span class="fb-report-list-kicker">RIWAYAT AKTIVITAS</span>
                <h2>Daftar Laporan Saya</h2>
                <p>Kelola dan pantau status laporan barang yang telah kamu kirim.</p>
            </div>
            <div class="fb-report-hero-art" aria-hidden="true">
                <svg viewBox="0 0 240 170" role="presentation">
                    <defs>
                        <linearGradient id="fbHeroPaper" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0" stop-color="#fff"/>
                            <stop offset="1" stop-color="#fff0f4"/>
                        </linearGradient>
                        <linearGradient id="fbHeroAccent" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0" stop-color="#b72b5c"/>
                            <stop offset="1" stop-color="#7b0d35"/>
                        </linearGradient>
                    </defs>
                    <circle cx="178" cy="82" r="70" fill="#f8dce6" opacity=".58"/>
                    <circle cx="178" cy="82" r="53" fill="#fff" opacity=".5"/>
                    <path d="M24 127c10-23 22-31 34-25 9 5 9 20 18 24 9 4 16-3 24-12l12 27H20Z" fill="#e9b8c9"/>
                    <path d="M45 126 58 93l17 9 12 25Z" fill="#f1c9d6"/>
                    <rect x="78" y="22" width="94" height="125" rx="10" fill="url(#fbHeroPaper)" stroke="#8e1741" stroke-width="4"/>
                    <path d="M110 22v-8h29v8" fill="none" stroke="#8e1741" stroke-width="5" stroke-linecap="round"/>
                    <path d="M96 54l6 6 11-13M96 83l6 6 11-13M96 112l6 6 11-13" fill="none" stroke="#a51b4d" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M122 54h34M122 83h27M122 112h22" stroke="#d8a9ba" stroke-width="4" stroke-linecap="round"/>
                    <circle cx="173" cy="105" r="25" fill="#fff" stroke="url(#fbHeroAccent)" stroke-width="7"/>
                    <path d="m191 124 19 19" stroke="#7b0d35" stroke-width="9" stroke-linecap="round"/>
                    <path d="m160 104 8 8 17-19" fill="none" stroke="#b72b5c" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M30 49c5-9 11-10 16-3-1 8-7 13-16 14-3-4-3-7 0-11ZM204 34c7-8 13-7 17 1-3 8-9 11-18 10-2-5-1-8 1-11Z" fill="#e9a9bd"/>
                    <path d="M36 62c-2-11 2-18 10-21M208 47c-1-10 3-16 11-19" fill="none" stroke="#c66b8b" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>
        </div>
        
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
        <span class="fb-filter-reset-icon" aria-hidden="true">↻</span>
        <span>Reset</span>
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
            <small class="fb-report-location">
                <span class="fb-report-location-pin" aria-hidden="true">⌖</span>
                <span>{{ $report->location ?: 'Lokasi tidak tersedia' }}</span>
            </small>
        </div>
    </div>
</td>

                    <td data-label="Jenis">
                        <span class="fb-report-type {{ $report->type === 'lost' ? 'lost' : 'found' }}">
                            {{ $report->type === 'lost' ? 'Hilang' : 'Ditemukan' }}
                        </span>
                    </td>
                    <td data-label="Tanggal" class="fb-report-date-cell">
                        <span class="fb-report-date">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18M8 14h2M14 14h2"/></svg>
                            {{ $report->event_date?->locale('id')->translatedFormat('j M Y') }}
                        </span>
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
                    <td data-label="Aksi" class="fb-activity-action-cell">
                        
@include('user.components.activity-detail-link', ['url' => route('user.reports.show', $report)])
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
<div class="fb-activity-mobile-list" aria-label="Daftar laporan">
    @forelse($reports as $report)
        <article class="fb-activity-mobile-card">
            <div class="fb-activity-mobile-card-main">
                <div class="fb-report-photo">
                    @if ($report->photo_path)
                        <img src="{{ asset('storage/' . $report->photo_path) }}" alt="Foto {{ $report->title }}" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="fb-report-photo-placeholder" style="display:none;">▧</div>
                    @else
                        <div class="fb-report-photo-placeholder">▧</div>
                    @endif
                </div>
                <div class="fb-activity-mobile-title">
                    <strong>{{ $report->title }}</strong>
                    <small class="fb-report-location">
                        <span class="fb-report-location-pin" aria-hidden="true">⌖</span>
                        <span>{{ $report->location ?: 'Lokasi tidak tersedia' }}</span>
                    </small>
                    <small class="fb-activity-mobile-date"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/></svg>{{ $report->event_date?->locale('id')->translatedFormat('j M Y') }}</small>
                </div>
                <a class="fb-activity-mobile-chevron"
                   href="{{ route('user.reports.show', $report) }}"
                   aria-label="Lihat detail {{ $report->title }}">›</a>
            </div>
            <div class="fb-activity-mobile-meta">
                <span class="fb-report-type {{ $report->type === 'lost' ? 'lost' : 'found' }}">{{ $report->type === 'lost' ? 'Hilang' : 'Ditemukan' }}</span>
                @if ($report->status === 'rejected')
                    <span class="cu-badge amber">Ditolak · Perlu Perbaikan</span>
                @elseif ($report->status === 'pending' && $report->awaiting_revision)
                    <span class="cu-badge amber">Menunggu perbaikan Anda</span>
                @else
                    @include('user.components.status', ['status' => $report->status])
                @endif
            </div>
        </article>
    @empty
        <div class="fb-activity-mobile-empty">Belum ada laporan yang sesuai.</div>
    @endforelse
</div>

@include('user.components.pages', ['paginator' => $reports])
    </section>


<script>
window.fbInitActivityFilters = function () {
    window.fbActivityFilterController?.abort();

    const controller = new AbortController();
    window.fbActivityFilterController = controller;

    const dropdowns = document.querySelectorAll(
        '[data-custom-select]'
    );

    dropdowns.forEach(function (dropdown) {
        const trigger = dropdown.querySelector(
            '.fb-custom-trigger'
        );

        const input = dropdown.querySelector(
            'input[type="hidden"]'
        );

        const label = dropdown.querySelector(
            '.fb-custom-label'
        );

        const options = dropdown.querySelectorAll(
            '.fb-custom-options button'
        );

        if (!trigger || !input || !label) return;

        trigger.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();

            const isOpen = dropdown.classList.contains('open');

            dropdowns.forEach(function (item) {
                item.classList.remove('open');

                const button = item.querySelector(
                    '.fb-custom-trigger'
                );

                button?.setAttribute('aria-expanded', 'false');
            });

            if (!isOpen) {
                dropdown.classList.add('open');
                trigger.setAttribute('aria-expanded', 'true');
            }
        });

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

            if (option.dataset.value === input.value) {
                option.classList.add('selected');
                label.textContent = option.textContent.trim();
            }
        });
    });

    document.addEventListener('click', function (event) {
        dropdowns.forEach(function (dropdown) {
            if (!dropdown.contains(event.target)) {
                dropdown.classList.remove('open');

                dropdown.querySelector(
                    '.fb-custom-trigger'
                )?.setAttribute('aria-expanded', 'false');
            }
        });
    }, { signal: controller.signal });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;

        dropdowns.forEach(function (dropdown) {
            dropdown.classList.remove('open');

            dropdown.querySelector(
                '.fb-custom-trigger'
            )?.setAttribute('aria-expanded', 'false');
        });
    }, { signal: controller.signal });
};

if (!window.fbActivityTurboListenerInstalled) {
    window.fbActivityTurboListenerInstalled = true;

    document.addEventListener('turbo:load', function () {
        if (document.querySelector('[data-custom-select]')) {
            window.fbInitActivityFilters();
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    if (!window.Turbo && document.querySelector('[data-custom-select]')) {
        window.fbInitActivityFilters();
    }
});
</script>


@endsection
