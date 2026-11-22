@extends('user.layouts.campus')

@section('title', 'Aktivitas Saya')

@section('content')
    

@include('user.components.activity-heading', ['activityTab' => 'reports'])


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
