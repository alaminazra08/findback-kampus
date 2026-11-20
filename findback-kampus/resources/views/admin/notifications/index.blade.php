@extends('admin.layouts.items')

@section('title', 'Pusat Notifikasi')
@section('breadcrumb', 'Pusat Notifikasi')

@section('page-styles')
    <link rel="stylesheet" href="{{ asset('css/admin/header-consistency.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('css/admin/admin-notifications-center.css') }}?v=8">
@endsection

@section('content')
    @php
        $tabs = [
            'all' => 'Semua',
            'unread' => 'Belum Dibaca',
            'reports' => 'Laporan',
            'claims' => 'Klaim',
            'registrations' => 'Registrasi',
            'system' => 'Sistem',
        ];
        $categoryLabels = [
            'reports' => 'Laporan',
            'claims' => 'Klaim',
            'registrations' => 'Registrasi',
            'system' => 'Sistem',
        ];
        $categoryIcons = [
            'reports' => 'M12 3 3 8l9 5 9-5-9-5Zm-9 9 9 5 9-5M3 16l9 5 9-5',
            'claims' => 'M8 3h8M8 2h8v4H8zM6 4H5a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-1M8 12h8M8 16h5',
            'registrations' => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM19 8v6M16 11h6',
            'system' => 'M12 3v3m0 12v3M3 12h3m12 0h3M5.64 5.64l2.12 2.12m8.48 8.48 2.12 2.12m0-12.72-2.12 2.12m-8.48 8.48-2.12 2.12M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8Z',
        ];
    @endphp

    <div class="fb-notif-center" data-fb-stats-url="{{ route('admin.notifications.stats') }}" data-fb-index-url="{{ route('admin.notifications.index') }}" data-fb-user-key="{{ auth()->id() }}">
        <div class="fb-notif-center-heading">
            <div>
                <p class="fb-notif-center-eyebrow">ADMINISTRASI · FINDBACK KAMPUS</p>
                <div class="fb-notif-center-title-row">
                    <h1>Pusat <span>Notifikasi</span></h1>
                    <span class="fb-notif-unread-pill" data-fb-unread-pill><span></span>{{ number_format($unreadCount, 0, ',', '.') }} belum dibaca</span>
                </div>
                <p class="fb-notif-center-subtitle">Kelola seluruh aktivitas dan pemberitahuan FindBack Kampus.</p>
            </div>
            <form action="{{ route('admin.notifications.read-all') }}" method="POST">
                @csrf
                <button class="fb-notif-primary-btn" data-fb-mark-all type="submit" {{ $unreadCount === 0 ? 'disabled' : '' }}>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>
                    Tandai semua dibaca
                </button>
            </form>
        </div>

        <div class="fb-notif-stat-grid" aria-label="Ringkasan notifikasi">
            <article class="fb-notif-stat-card" data-fb-stat="total">
                <div class="fb-notif-stat-copy"><span>Total Notifikasi</span><strong data-fb-stat-value>{{ number_format($totalCount, 0, ',', '.') }}</strong><small>Seluruh notifikasi tersimpan</small></div>
                <span class="fb-notif-stat-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg></span>
            </article>
            <article class="fb-notif-stat-card {{ $unreadCount > 0 ? 'is-highlight' : '' }}" data-fb-stat="unread">
                <div class="fb-notif-stat-copy"><span>Belum Dibaca</span><strong data-fb-stat-value>{{ number_format($unreadCount, 0, ',', '.') }}</strong><small data-fb-unread-description>{{ $unreadCount > 0 ? 'Ada notifikasi yang perlu diperiksa' : 'Semua notifikasi telah dibaca' }}</small></div>
                <span class="fb-notif-stat-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v14H4zM4 7l8 6 8-6"/></svg></span>
            </article>
            <article class="fb-notif-stat-card" data-fb-stat="today">
                <div class="fb-notif-stat-copy"><span>Notifikasi Hari Ini</span><strong data-fb-stat-value>{{ number_format($notificationsToday, 0, ',', '.') }}</strong><small>Masuk hari ini (WIB)</small></div>
                <span class="fb-notif-stat-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 2v4m8-4v4M3 10h18M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2z"/></svg></span>
            </article>
        </div>

        <section class="fb-notif-center-panel" aria-label="Daftar notifikasi">
            <div class="fb-notif-toolbar">
                <nav class="fb-notif-tabs" aria-label="Filter kategori notifikasi" data-fb-notif-tabs>
                    @foreach ($tabs as $key => $label)
                        <a href="{{ route('admin.notifications.index', array_filter(['category' => $key, 'q' => $search, 'sort' => $sort], fn ($value) => $value !== null && $value !== '')) }}"
                            class="{{ $category === $key ? 'is-active' : '' }}" @if ($category === $key) aria-current="page" @endif>{{ $label }}</a>
                    @endforeach
                </nav>
                <form class="fb-notif-search-form" method="GET" action="{{ route('admin.notifications.index') }}" aria-label="Pencarian dan penyaringan notifikasi" data-fb-filter-form>
                    <input type="hidden" name="category" value="{{ $category }}">
                    <label class="fb-notif-search">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
                        <input type="search" name="q" value="{{ $search }}" placeholder="Cari notifikasi..." maxlength="120" aria-label="Cari notifikasi" spellcheck="false" autocorrect="off" autocapitalize="off">
                    </label>
                    <select name="sort" aria-label="Urutkan notifikasi">
                        <option value="newest" @selected($sort === 'newest')>Terbaru</option>
                        <option value="oldest" @selected($sort === 'oldest')>Terlama</option>
                    </select>
                    <button type="submit" class="fb-notif-filter-submit">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16l-6.5 7.5V19l-3 1.5v-8z"/></svg>
                        <span>Terapkan</span>
                    </button>
                    <a class="fb-notif-filter-reset" href="{{ route('admin.notifications.index') }}" data-fb-reset aria-label="Atur ulang semua filter notifikasi">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 8.5H13.5C17.5 8.5 20 11 20 14.5C20 18 17.5 20 14 20H10"/><path d="M4.2 8.5L10 3.5V13.5L4.2 8.5Z" fill="currentColor" stroke="none"/></svg>
                        <span>Atur Ulang</span>
                    </a>
                </form>
            </div>

            <div id="fb-notif-live-list" data-fb-live-list>
                @include('admin.notifications.partials.live-list')
            </div>
        </section>
    </div>
    <script src="{{ asset('js/admin/notification-stats-live.js') }}?v=6" defer></script>
@endsection
