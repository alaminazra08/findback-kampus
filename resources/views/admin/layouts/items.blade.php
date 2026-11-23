<!DOCTYPE html>
<html @if (\App\Support\AdminSettings::brandColor($fbBrand) !== '') data-custom-brand @endif lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description"
        content="{{ $adminSettings['site_description'] }}">

    <meta name="theme-color" content="#121114">

    <title>FindBack Kampus</title>
    <script src="{{ asset('js/admin/admin-tab-guard.js') }}"
        data-logout="{{ route('logout') }}" data-token="{{ csrf_token() }}"></script>
    <script src="{{ asset('js/admin/sidebar-prepaint.js') }}"></script>

    <link rel="icon"
        href="{{ \App\Support\AdminSettings::logoUrl($fbBrand, 'favicon.png') }}">

    {{-- CSS UTAMA --}}
    <link rel="stylesheet"
        href="{{ asset('css/admin/admin-dashboard.css') }}?v=41">

    <link rel="stylesheet"
        href="{{ asset('css/admin/admin-items.css') }}?v=34">

    @yield('item-upload-style')

    <link rel="stylesheet"
        href="{{ asset('css/pages/admin/layouts/items.css') }}">

    <link rel="stylesheet"
        href="{{ asset('css/admin/admin-workflow.css') }}?v=1">

    {{-- SIDEBAR --}}
    <link rel="stylesheet"
        href="{{ asset('css/admin/sidebar-theme.css') }}?v=43">

    <link rel="stylesheet"
        href="{{ asset('css/admin/sidebar-layout.css') }}?v=46">


    <link rel="stylesheet"
        href="{{ asset('css/admin/sidebar-prepaint.css') }}?v=48">

    {{-- RESPONSIVE --}}
    <link rel="stylesheet"
        href="{{ asset('css/admin/admin-shell-responsive.css') }}?v=1">

    <link rel="stylesheet"
        href="{{ asset('css/shared/view-utilities.css') }}">

    <link rel="stylesheet" href="{{ asset('css/shared/confirmation-modal-v14.css') }}?v=16">
    <script src="{{ asset('js/shared/confirmation-modal-v14.js') }}?v=16" defer></script>
    <script src="{{ asset('js/admin/claim-rejection-dialog.js') }}?v=1" defer></script>
    <script src="{{ asset('js/shared/view-interactions.js') }}" defer></script>
    <script src="{{ asset('js/report-history-live.js') }}?v=1" defer></script>
    @yield('page-styles')
    <link rel="stylesheet" href="{{ asset('css/admin/admin-footer.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/admin/modal-scrollbar.css') }}?v=1">
    <script src="{{ asset('js/admin/modal-scrollbar.js') }}?v=1" defer></script>
    <link rel="stylesheet" href="{{ asset('css/shared/responsive-all-devices.css') }}?v=20261111">
    <!-- FINDBACK_UI_TEXT_KONSISTEN_V1 -->
    <link rel="stylesheet" href="{{ asset('css/shared/ui-text-consistency.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/admin/brand-polish.css') }}?v=1">
<!-- FINDBACK_TYPOGRAPHY_V1 -->
<link rel="stylesheet" href="{{ asset('css/shared/typography.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/notification-toast.css') }}?v=6">
    <link rel="stylesheet" href="{{ asset('css/admin/work-page-heading.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/admin/design-system.css') }}?v=9">
    {{-- FINDBACK: hide native search clear controls consistently on all admin pages --}}
    <link rel="stylesheet" href="{{ asset('css/admin/search-input-consistency.css') }}?v=20261120-3">
    <link rel="stylesheet" href="{{ asset('css/admin/report-type-size.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('css/admin/admin-profile-page.css') }}?v=4">
    @include('partials.findback-brand-theme')
</head>

<body class="fb-responsive fb-admin-body">

    <div class="fb-admin-shell">

        {{-- SIDEBAR ADMIN --}}
        <aside class="fb-sidebar"
            id="fb-sidebar"
            aria-label="Navigasi administrator">

            <button type="button"
                class="fb-v43-close"
                aria-label="Tutup navigasi"
                data-close-admin-nav>
                &#215;
            </button>

            {{-- BRAND SIDEBAR --}}
            @include('admin.partials.brand')

            <div class="fb-sidebar-nav-scroll"
                aria-label="Daftar menu administrator">

                <div class="fb-sidebar-divider"
                    aria-hidden="true"></div>

                <p class="fb-nav-label">MENU UTAMA</p>

                @include('admin.partials.main-nav')

                {{-- PENGELOLAAN --}}
                <p class="fb-nav-label fb-nav-label-secondary">
                    PENGELOLAAN
                </p>

                <nav class="fb-nav"
                    aria-label="Pengelolaan administrator">

                    {{-- MANAJEMEN AKUN --}}
                    @if (\Illuminate\Support\Facades\Route::has('admin.users.index'))

                        <a href="{{ route('admin.users.index') }}"
                            class="fb-nav-link {{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}"
                            @if (request()->routeIs('admin.users.*'))
                                aria-current="page"
                            @endif>

                            <span class="fb-nav-icon">
                                <svg viewBox="0 0 24 24"
                                    aria-hidden="true">
                                    <circle cx="9" cy="8" r="3" />
                                    <path
                                        d="M3 20v-2a6 6 0 0 1 12 0v2M17 6a3 3 0 0 1 0 6m1 4a5 5 0 0 1 3 4" />
                                </svg>
                            </span>

                            <span class="fb-nav-text">
                                Manajemen Akun
                            </span>
                        </a>

                    @else

                        <span class="fb-nav-link is-disabled"
                            aria-disabled="true">
                            <span class="fb-nav-text">
                                Manajemen Akun
                            </span>
                        </span>

                    @endif

                    {{-- PENGATURAN SISTEM --}}
                    @if (\Illuminate\Support\Facades\Route::has('admin.settings.index'))

                        <a href="{{ route('admin.settings.index') }}"
                            class="fb-nav-link {{ request()->routeIs('admin.settings.*') ? 'is-active' : '' }}"
                            @if (request()->routeIs('admin.settings.*'))
                                aria-current="page"
                            @endif>

                            <span class="fb-nav-icon">
                                <svg viewBox="0 0 24 24"
                                    aria-hidden="true">

                                    <circle cx="12" cy="12" r="3" />

                                    <path
                                        d="M19 13.7 21 12l-2-1.7-.7-2.5-2.5-.7L14 5l-2 1-2-1-1.8 2.1-2.5.7L5 10.3 3 12l2 1.7.7 2.5 2.5.7L10 19l2-1 2 1 1.8-2.1 2.5-.7z" />
                                </svg>
                            </span>

                            <span class="fb-nav-text">
                                Pengaturan
                            </span>
                        </a>

                    @else

                        <span class="fb-nav-link is-disabled"
                            aria-disabled="true">

                            <span class="fb-nav-text">
                                Pengaturan
                            </span>

                        </span>

                    @endif

                </nav>

            </div>

            {{-- BAGIAN BAWAH SIDEBAR --}}
            <div class="fb-sidebar-bottom">


                {{-- LOGOUT --}}
                <form action="{{ route('logout') }}"
                    method="POST">

                    @csrf

                    <button class="fb-sidebar-logout"
                        type="submit">

                        <svg viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true">

                            <path
                                d="M10 4H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h5M14 8l4 4-4 4m-8-4h12" />

                        </svg>

                        <span>Keluar dari Akun</span>

                    </button>

                </form>

            </div>

        </aside>

        {{-- BACKDROP MOBILE --}}
        <div class="fb-sidebar-backdrop"
            id="fb-sidebar-backdrop"
            hidden>
        </div>

        {{-- WORKSPACE ADMIN --}}
        <div class="fb-workspace">

            {{-- TOPBAR --}}
            <header class="fb-topbar">

                <div class="fb-topbar-left">

                    {{-- TOMBOL MENU MOBILE --}}
                    <button class="fb-menu-toggle"
                        type="button"
                        aria-label="Buka menu"
                        aria-controls="fb-sidebar"
                        aria-expanded="false"
                        id="fb-menu-toggle"><svg class="fb-hamburger-svg" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M4 6h16M4 12h16M4 18h16"/></svg></button>

                    {{-- BRAND MOBILE DINAMIS --}}
                    <div class="fb-mobile-brand"
                        aria-label="{{ $adminSettings['site_name'] }}, panel admin">

                        <strong>
                            FindBack
                        </strong>

                        <small>ADMIN PANEL</small>

                    </div>

                    {{-- BREADCRUMB --}}
                    <div class="fb-breadcrumb">

                        Administrasi <span>/</span>

                        <strong>

                            @if (request()->routeIs('admin.notifications.*'))

                                Pusat Notifikasi

                            @elseif (request()->routeIs('admin.chat.*'))

                                Pesan Pengguna

                            @elseif(request()->routeIs('admin.users.*'))

                                Manajemen Akun

                            @elseif(request()->routeIs('admin.statistics.*'))

                                Laporan &amp; Statistik

                            @elseif(request()->routeIs('admin.matching.*'))

                                Pencocokan Barang

                            @elseif(request()->routeIs('admin.claims.*'))

                                Klaim &amp; Pengembalian

                            @elseif(request()->routeIs('admin.reports.verification*'))

                                Verifikasi Laporan

                            @elseif(request()->routeIs('admin.reports.lost'))

                                Laporan Hilang

                            @elseif(request()->routeIs('admin.reports.found'))

                                Laporan Ditemukan

                            @else

                                @yield('breadcrumb', 'Laporan Barang')

                            @endif

                        </strong>

                    </div>

                </div>

                {{-- BAGIAN KANAN TOPBAR --}}
                <div class="fb-topbar-right">

                    @include('admin.partials.notification-bell')

                    @include('admin.partials.account')

                </div>

            </header>

            {{-- KONTEN UTAMA --}}
            @php
                $adminNavPage = [
                    'admin.dashboard' => 'dashboard',
                    'admin.notifications.index' => 'notifications',
                    'admin.reports.verification' => 'verification',
                    'admin.items.index' => 'items',
                    'admin.matching.index' => 'matching',
                    'admin.claims.index' => 'claims',
                    'admin.statistics.index' => 'statistics',
                    'admin.users.index' => 'users',
                    'admin.reports.verification.show' => 'verification-detail',
                    'admin.items.show' => 'item-detail',
                    'admin.matching.show' => 'match-detail',
                    'admin.claims.show' => 'claim-detail',
                    'admin.users.show' => 'user-detail',
                    'admin.profile.index' => 'profile',
                    'admin.settings.index' => 'settings',
                ][request()->route()?->getName()] ?? null;
            @endphp
            <main class="fb-main{{ request()->routeIs('admin.dashboard') ? '' : ' fb-items-main' }}" @if ($adminNavPage) data-fb-nav-page="{{ $adminNavPage }}" @endif>

                @yield('content')

                <footer class="fb-footer fb-admin-page-footer">
                    © {{ now()->year }} Fakultas Sains dan Teknologi
                    <span>·</span> Universitas Islam Negeri Imam Bonjol Padang
                </footer>

            </main>

        </div>

    </div>

    {{-- NOTIFIKASI --}}
    <script
        src="{{ asset('js/admin/admin-notifications.js') }}?v=6"
        defer>
    </script>

    {{-- SIDEBAR MOBILE --}}
    <script src="{{ asset('js/admin/sidebar-mobile.js') }}?v=20261111" defer></script>

    {{-- SIDEBAR --}}
    <script
        src="{{ asset('js/admin/sidebar-branding.js') }}?v=50"
        defer>
    </script>

    <script src="{{ asset('js/shared/responsive-viewport.js') }}?v=20261111" defer></script>
    <script src="{{ asset('js/admin/admin-analytics.js') }}?v=4" defer></script>
    <script src="{{ asset('js/admin/dashboard-date.js') }}?v=3" defer></script>
    <script src="{{ asset('js/admin/admin-items-upload.js') }}?v=3" defer></script>
    <script src="{{ asset('js/pages/admin/matching/filter-navigation.js') }}?v=6" defer></script>
    <script src="{{ asset('js/pages/admin/statistics/index.js') }}?v=2" defer></script>
    @unless(request()->routeIs('admin.settings.index'))
        <script src="{{ asset('js/admin/settings-panels.js') }}?v=1" defer></script>
    @endunless
    <script src="{{ asset('js/admin/notification-stats-live.js') }}?v=10" defer></script>
    <script src="{{ asset('js/admin/settings-score.js') }}?v=2" defer></script>
    <script src="{{ asset('js/admin/settings-smart-save.js') }}?v=18" defer></script>
    <script src="{{ asset('js/admin/claims-toast-v1.js') }}?v=2" defer></script>
    <script defer src="{{ asset('js/admin/statistics-cards-consistency-v1.js') }}?v=1"></script>
    <script defer src="{{ asset('js/admin/statistics-download-menu-v4.js') }}?v=5"></script>
    <script defer src="{{ asset('js/admin/statistics-period-dropdown-v5.js') }}?v=6"></script>
    <script defer src="{{ asset('js/admin/statistics-dropdown-layer-v6.js') }}?v=7"></script>
    <script defer src="{{ asset('js/pages/admin/matching/photo-viewer-v6.js') }}?v=8"></script>
    @include('admin.partials.detail-interactions')
    <script src="{{ asset('js/admin/admin-toast-standard-v1.js') }}?v=3" defer></script>
    <script src="{{ asset('js/admin/admin-scroll-continuity-v1.js') }}?v=2" defer></script>
    <script src="{{ asset('js/admin/account-photo-live.js') }}?v=3" defer></script>
    <script src="{{ asset('js/admin/admin-fast-navigation.js') }}?v=14" defer
        data-account="{{ auth()->id() }}"
        data-dashboard="{{ route('admin.dashboard') }}"
        data-verification="{{ route('admin.reports.verification') }}"
        data-verification-detail="{{ route('admin.reports.verification.show', ['item' => '__item__']) }}"
        data-items="{{ route('admin.items.index') }}"
        data-item-detail="{{ route('admin.items.show', ['item' => '__item__']) }}"
        data-matching="{{ route('admin.matching.index') }}"
        data-matching-detail="{{ route('admin.matching.show', ['match' => '__item__']) }}"
        data-claims="{{ route('admin.claims.index') }}"
        data-claim-detail="{{ route('admin.claims.show', ['claim' => '__item__']) }}"
        data-statistics="{{ route('admin.statistics.index') }}"
        data-profile="{{ route('admin.profile.index') }}"
        data-users="{{ route('admin.users.index') }}"
        data-user-detail="{{ route('admin.users.show', ['user' => '__item__']) }}"
        data-settings="{{ route('admin.settings.index') }}"
        data-notifications="{{ route('admin.notifications.index') }}"></script>
</body>

</html>
