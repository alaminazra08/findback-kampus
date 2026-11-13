<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description"
        content="{{ $adminSettings['site_description'] }}">

    <meta name="theme-color" content="#121114">

    <title>FindBack Kampus</title>
    <script src="{{ asset('js/admin/sidebar-prepaint.js') }}"></script>

    <link rel="icon"
        href="{{ asset('favicon.png') }}"
        type="image/png">

    {{-- CSS UTAMA --}}
    <link rel="stylesheet"
        href="{{ asset('css/admin/admin-dashboard.css') }}?v=40">

    <link rel="stylesheet"
        href="{{ asset('css/admin/admin-items.css') }}?v=34">

    @yield('item-upload-style')

    <link rel="stylesheet"
        href="{{ asset('css/pages/admin/layouts/items.css') }}">

    <link rel="stylesheet"
        href="{{ asset('css/admin/admin-workflow.css') }}?v=1">

    {{-- SIDEBAR --}}
    <link rel="stylesheet"
        href="{{ asset('css/admin/admin-sidebar-v43.css') }}?v=43">

    <link rel="stylesheet"
        href="{{ asset('css/admin/admin-sidebar-v44.css') }}?v=44">


    <link rel="stylesheet"
        href="{{ asset('css/admin/admin-sidebar-v45.css') }}?v=48">

    {{-- RESPONSIVE --}}
    <link rel="stylesheet"
        href="{{ asset('css/admin/admin-responsive-v2d.css') }}?v=1">

    <link rel="stylesheet"
        href="{{ asset('css/shared/view-utilities.css') }}">

    <script src="{{ asset('js/shared/view-interactions.js') }}" defer></script>
    @yield('page-styles')
    <link rel="stylesheet" href="{{ asset('css/shared/responsive-all-devices.css') }}?v=20261111">
    <!-- FINDBACK_UI_TEXT_KONSISTEN_V1 -->
    <link rel="stylesheet" href="{{ asset('css/shared/findback-ui-text-consistency-v1.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/admin/findback-brand-premium-v1.css') }}?v=1">
<!-- FINDBACK_TYPOGRAPHY_V1 -->
<link rel="stylesheet" href="{{ asset('css/shared/findback-typography-v1.css') }}">
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
                        aria-label="FindBack, panel admin">

                        <strong>
                            FindBack
                        </strong>

                        <small>ADMIN PANEL</small>

                    </div>

                    {{-- BREADCRUMB --}}
                    <div class="fb-breadcrumb">

                        Administrasi <span>/</span>

                        <strong>

                            @if (request()->routeIs('admin.chat.*'))

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
            <main class="fb-main fb-items-main" @if (request()->routeIs('admin.reports.verification')) data-fb-nav-page="verification" @elseif (request()->routeIs('admin.items.index')) data-fb-nav-page="items" @elseif (request()->routeIs('admin.matching.index')) data-fb-nav-page="matching" @elseif (request()->routeIs('admin.claims.index')) data-fb-nav-page="claims" @elseif (request()->routeIs('admin.statistics.index')) data-fb-nav-page="statistics" @elseif (request()->routeIs('admin.users.index')) data-fb-nav-page="users" @elseif (request()->routeIs('admin.users.show')) data-fb-nav-page="user-detail" @elseif (request()->routeIs('admin.settings.index')) data-fb-nav-page="settings" @endif>

                @yield('content')

            </main>

        </div>

    </div>

    {{-- NOTIFIKASI --}}
    <script
        src="{{ asset('js/admin/admin-notifications.js') }}?v=2"
        defer>
    </script>

    {{-- SIDEBAR MOBILE --}}
    <script src="{{ asset('js/admin/sidebar-mobile.js') }}?v=20261111" defer></script>

    {{-- SIDEBAR --}}
    <script
        src="{{ asset('js/admin/admin-sidebar-v44.js') }}?v=49"
        defer>
    </script>

    <script src="{{ asset('js/shared/responsive-viewport.js') }}?v=20261111" defer></script>
    <script src="{{ asset('js/admin/smart-navigation-session.js') }}?v=1" defer></script>
    @if (request()->routeIs('admin.reports.verification', 'admin.items.index', 'admin.matching.index', 'admin.claims.index', 'admin.statistics.index', 'admin.users.index', 'admin.users.show', 'admin.settings.index'))
        <script src="{{ asset('js/admin/admin-analytics.js') }}?v=2" defer></script>
        <script src="{{ asset('js/admin/dashboard-date.js') }}?v=2" defer></script>
        <script src="{{ asset('js/pages/admin/reports/verification/fast-tabs.js') }}?v=3" defer></script>
        <script src="{{ asset('js/pages/admin/reports/verification/return-context.js') }}?v=2" defer></script>
        <script src="{{ asset('js/admin/admin-items-upload.js') }}?v=2" defer></script>
        <script src="{{ asset('js/admin/items-filter-ajax-scroll.js') }}?v=2" defer></script>
        <script src="{{ asset('js/pages/admin/matching/filter-navigation-v2.js') }}?v=3" defer></script>
        <script src="{{ asset('js/admin/claims-filter-ajax-scroll.js') }}?v=2" defer></script>
        <script src="{{ asset('js/pages/admin/statistics/index.js') }}?v=2" defer></script>
        <script src="{{ asset('js/admin/users-filter-navigation-v1.js') }}?v=2" defer></script>
        <script src="{{ asset('js/admin/settings-score.js') }}?v=2" defer></script>
        <script src="{{ asset('js/admin/dashboard-verification-navigation.js') }}?v=8" defer
            data-dashboard="{{ route('admin.dashboard') }}"
            data-verification="{{ route('admin.reports.verification') }}"
            data-items="{{ route('admin.items.index') }}"
            data-matching="{{ route('admin.matching.index') }}"
            data-claims="{{ route('admin.claims.index') }}"
            data-statistics="{{ route('admin.statistics.index') }}"
            data-users="{{ route('admin.users.index') }}"
            data-settings="{{ route('admin.settings.index') }}"></script>
    @endif
    {{-- Smart Save harus sudah siap sebelum navigasi AJAX menuju Pengaturan. --}}
    <script src="{{ asset('js/admin/settings-smart-save-v5.js') }}?v=10" defer></script>
</body>

</html>
