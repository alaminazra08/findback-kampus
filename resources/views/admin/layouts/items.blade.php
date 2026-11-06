<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $adminSettings['site_description'] }}">
    <meta name="theme-color" content="#121114">

    <title>@yield('title', 'Data Barang') | {{ $adminSettings['site_name'] }}</title>

    <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png">

    <link rel="stylesheet" href="{{ asset('css/admin/admin-dashboard.css') }}?v=24">
    <link rel="stylesheet" href="{{ asset('css/admin/admin-items.css') }}?v=34">

    <link rel="stylesheet" href="{{ asset('css/pages/admin/layouts/items.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/admin-workflow.css') }}?v=1">
    {{-- FINDBACK_SIDEBAR_V43 --}}
    <link rel="stylesheet" href="{{ asset('css/admin/admin-sidebar-v43.css') }}?v=43">
    {{-- FINDBACK_SIDEBAR_V44 --}}
    <link rel="stylesheet" href="{{ asset('css/admin/admin-sidebar-v44.css') }}?v=44">
    <!-- FINDBACK_V45_PREPAINT -->
    <script src="{{ asset('js/admin/sidebar-prepaint.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('css/admin/admin-sidebar-v45.css') }}?v=48">
    <link rel="stylesheet" href="{{ asset('css/admin/admin-responsive-v2d.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/shared/view-utilities.css') }}">
    <script src="{{ asset('js/shared/view-interactions.js') }}"></script>
</head>

<body class="fb-admin-body">

    <div class="fb-admin-shell">

        {{-- SIDEBAR ADMIN --}}
        <aside class="fb-sidebar" id="fb-sidebar" aria-label="Navigasi administrator">
            <button type="button" class="fb-v43-close" aria-label="Tutup navigasi" data-close-admin-nav>&#215;</button>

            @include('admin.partials.brand')

            <div class="fb-sidebar-nav-scroll" aria-label="Daftar menu administrator">

                <div class="fb-sidebar-divider" aria-hidden="true"></div>

                <p class="fb-nav-label">MENU UTAMA</p>

                @include('admin.partials.main-nav')

                {{-- PENGELOLAAN --}}
                <p class="fb-nav-label fb-nav-label-secondary">PENGELOLAAN</p>

                <nav class="fb-nav" aria-label="Pengelolaan administrator">

                    {{-- Manajemen Akun --}}
                    @if (\Illuminate\Support\Facades\Route::has('admin.users.index'))
                        <a href="{{ route('admin.users.index') }}"
                            class="fb-nav-link {{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}"
                            @if (request()->routeIs('admin.users.*')) aria-current="page" @endif>

                            <span class="fb-nav-icon">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <circle cx="9" cy="8" r="3" />
                                    <path d="M3 20v-2a6 6 0 0 1 12 0v2M17 6a3 3 0 0 1 0 6m1 4a5 5 0 0 1 3 4" />
                                </svg>
                            </span>

                            <span class="fb-nav-text">Manajemen Akun</span>
                        </a>
                    @else
                        <span class="fb-nav-link is-disabled" aria-disabled="true">
                            <span class="fb-nav-text">Manajemen Akun</span>
                        </span>
                    @endif

                    {{-- Pengaturan --}}
                    @if (\Illuminate\Support\Facades\Route::has('admin.settings.index'))
                        <a href="{{ route('admin.settings.index') }}"
                            class="fb-nav-link {{ request()->routeIs('admin.settings.*') ? 'is-active' : '' }}"
                            @if (request()->routeIs('admin.settings.*')) aria-current="page" @endif>

                            <span class="fb-nav-icon">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <circle cx="12" cy="12" r="3" />
                                    <path
                                        d="M19 13.7 21 12l-2-1.7-.7-2.5-2.5-.7L14 5l-2 1-2-1-1.8 2.1-2.5.7L5 10.3 3 12l2 1.7.7 2.5 2.5.7L10 19l2-1 2 1 1.8-2.1 2.5-.7z" />
                                </svg>
                            </span>

                            <span class="fb-nav-text">Pengaturan</span>
                        </a>
                    @else
                        <span class="fb-nav-link is-disabled" aria-disabled="true">
                            <span class="fb-nav-text">Pengaturan</span>
                        </span>
                    @endif

                </nav>

            </div>

            {{-- SIDEBAR BOTTOM --}}
            <div class="fb-sidebar-bottom">

                <div class="fb-sidebar-help">

                    <span class="fb-help-dot" aria-hidden="true"></span>

                    <div>
                        <strong>Panel Administrator</strong>

                        @if ($adminSettings['contact_email'])
                            <a href="mailto:{{ $adminSettings['contact_email'] }}">
                                Hubungi admin
                            </a>
                        @endif

                        <small>{{ $adminSettings['institution_name'] }}</small>
                    </div>

                </div>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf

                    <button class="fb-sidebar-logout" type="submit">

                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">

                            <path d="M10 4H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h5M14 8l4 4-4 4m-8-4h12" />

                        </svg>

                        <span>Keluar dari Akun</span>

                    </button>

                </form>

            </div>

        </aside>

        <div class="fb-sidebar-backdrop" id="fb-sidebar-backdrop" hidden>
        </div>

        {{-- WORKSPACE --}}
        <div class="fb-workspace">

            {{-- TOPBAR --}}
            <header class="fb-topbar">

                <div class="fb-topbar-left">

                    <button class="fb-menu-toggle" type="button" aria-label="Buka menu" aria-controls="fb-sidebar"
                        aria-expanded="false" id="fb-menu-toggle">
                        ☰
                    </button>

                    <div class="fb-mobile-brand" aria-label="{{ $adminSettings['site_name'] }}, panel admin">

                        <strong>FindBack</strong>
                        <small>ADMIN PANEL</small>

                    </div>

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

                <div class="fb-topbar-right">

                    @include('admin.partials.notification-bell')

                    @include('admin.partials.account')

                </div>

            </header>

            {{-- MAIN CONTENT --}}
            <main class="fb-main fb-items-main">

                @yield('content')

            </main>

        </div>

    </div>

    {{-- NOTIFICATION --}}
    <script src="{{ asset('js/admin/admin-notifications.js') }}?v=1" defer></script>

    {{-- SIDEBAR RESPONSIVE --}}
    <script src="{{ asset('js/admin/sidebar-mobile.js') }}"></script>

    {{-- ANIMASI LOGO --}}
    <script src="{{ asset('js/admin/sidebar-brand.js') }}"></script>

    <script src="{{ asset('js/admin/admin-sidebar-v44.js') }}?v=48" defer></script>
</body>

</html>
