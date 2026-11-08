
@php
    /*
    |--------------------------------------------------------------------------
    | IDENTITAS APLIKASI DINAMIS
    |--------------------------------------------------------------------------
    | Menggunakan data Pengaturan Sistem yang disediakan ke view.
    */

    $fpSiteName = $fbBrand['site_name'] ?? 'FindBack Kampus';
    $fpInstitutionName = $fbBrand['institution_name'] ?? 'Fakultas Sains dan Teknologi';

    /*
    |--------------------------------------------------------------------------
    | NAVIGASI PENGGUNA
    |--------------------------------------------------------------------------
    */

    $fpNav = [
        ['dashboard', 'Beranda', 'home', 'dashboard'],
        ['user.items.index', 'Cari Barang', 'search', 'user.items.*'],
        ['user.reports.index', 'Laporan Saya', 'report', 'user.reports.index'],
        ['user.claims.index', 'Klaim Saya', 'claim', 'user.claims.*'],
        ['user.chat.index', 'Chat Admin', 'message', 'user.chat.*'],
    ];

    $fpName = auth()->user()->name ?? 'Pengguna';

    $fpCurrentPage = 'Laporan barang';

    if (request()->routeIs('dashboard')) {
        $fpCurrentPage = 'Beranda';
    } elseif (request()->routeIs('user.items.*')) {
        $fpCurrentPage = 'Cari barang';
    } elseif (request()->routeIs('user.claims.*')) {
        $fpCurrentPage = 'Klaim saya';
    } elseif (request()->routeIs('user.chat.*')) {
        $fpCurrentPage = 'Chat Admin';
    } elseif (request()->routeIs('user.reports.*')) {
        $fpCurrentPage = 'Laporan saya';
    }
@endphp

{{-- AKSESIBILITAS --}}
<a class="fp-skip" href="#fp-content">
    Langsung ke konten
</a>

{{-- OVERLAY MOBILE --}}
<div class="fp-overlay" id="fp-overlay" hidden></div>

{{-- SIDEBAR PENGGUNA --}}
<aside
    class="fp-sidebar"
    id="fp-sidebar"
    aria-label="Navigasi pengguna"
>

    {{-- IDENTITAS APLIKASI --}}
    <a
        class="fp-brand"
        href="{{ route('dashboard') }}"
        aria-label="{{ $fpSiteName }} - Beranda pengguna"
    >

        <img
            src="{{ asset('images/logo-findback.png') }}"
            alt=""
        >

        <span>
            {{ $fpSiteName }}

            <span class="fp-brand-sub">
                {{ $fpInstitutionName }}
            </span>
        </span>

        <span class="fp-brand-dot"></span>

    </a>

    {{-- INFORMASI WORKSPACE --}}
    <div class="fp-workspace">

        <span class="fp-workspace-icon">F</span>

        <div>
            <strong>Ruang pengguna</strong>

            <small>{{ $fpInstitutionName }}</small>
        </div>

    </div>

    {{-- MENU UTAMA --}}
    <p class="fp-nav-label">
        RUANG UTAMA
    </p>

    <nav aria-label="Menu utama">

        @foreach ($fpNav as [$r, $label, $icon, $active])

            <a
                class="fp-nav-link {{ request()->routeIs($active) ? 'is-active' : '' }}"
                href="{{ route($r) }}"
                @if (request()->routeIs($active))
                    aria-current="page"
                @endif
            >

                @include('user.partials.premium-icon', ['name' => $icon])

                <span>{{ $label }}</span>

                @if (request()->routeIs($active))
                    <span class="fp-active-dot"></span>
                @endif

            </a>

        @endforeach

    </nav>

    {{-- MENU PELAPORAN --}}
    <p class="fp-nav-label">
        BANTU TEMUKAN KEMBALI
    </p>

    <nav aria-label="Pelaporan barang">

        {{-- LAPORAN KEHILANGAN --}}
        <a
            class="fp-nav-link {{ request()->routeIs('user.reports.lost.*') ? 'is-active' : '' }}"
            href="{{ route('user.reports.lost.create') }}"
        >

            @include('user.partials.premium-icon', ['name' => 'lost'])

            <span>Laporkan Kehilangan</span>

        </a>

        {{-- LAPORAN PENEMUAN --}}
        <a
            class="fp-nav-link {{ request()->routeIs('user.reports.found.*') ? 'is-active' : '' }}"
            href="{{ route('user.reports.found.create') }}"
        >

            @include('user.partials.premium-icon', ['name' => 'found'])

            <span>Laporkan Penemuan</span>

        </a>

    </nav>

    {{-- BAGIAN BAWAH SIDEBAR --}}
    <div class="fp-sidebar-bottom">

        <div class="fp-care">

            <span>
                SEDIKIT PEDULI, BANYAK BERARTI.
            </span>

            <p>
                Barang yang kamu temukan bisa sangat berarti bagi seseorang.
            </p>

            <a href="{{ route('user.reports.found.create') }}">

                Bantu kembalikan

                @include('user.partials.premium-icon', ['name' => 'arrow'])

            </a>

        </div>

        {{-- LOGOUT --}}
        <form
            method="POST"
            action="{{ route('logout') }}"
        >

            @csrf

            <button
                class="fp-exit"
                type="submit"
            >

                @include('user.partials.premium-icon', ['name' => 'logout'])

                Keluar dari akun

            </button>

        </form>

    </div>

</aside>

{{-- TOPBAR --}}
<header class="fp-topbar">

    <div class="fp-top-left">

        {{-- TOMBOL NAVIGASI MOBILE --}}
        <button
            class="fp-menu"
            id="fp-menu"
            type="button"
            aria-label="Buka navigasi"
            aria-controls="fp-sidebar"
            aria-expanded="false"
        >

            @include('user.partials.premium-icon', ['name' => 'menu'])

        </button>

        {{-- BREADCRUMB --}}
        <span class="fp-top-label">

            Ruang pengguna

            <span>/</span>

            <strong>{{ $fpCurrentPage }}</strong>

        </span>

    </div>

    {{-- BAGIAN KANAN TOPBAR --}}
    <div class="fp-top-right">

        {{-- NAMA INSTITUSI DINAMIS --}}
        <span class="fp-campus">
            {{ $fpInstitutionName }}
        </span>

        <span class="fp-top-divider"></span>

        {{-- AVATAR USER --}}
        <span class="fp-avatar">
            {{ mb_strtoupper(mb_substr($fpName, 0, 1)) }}
        </span>

        {{-- INFORMASI AKUN --}}
        <span class="fp-account">

            <strong>{{ $fpName }}</strong>

            <small>Akun pengguna</small>

        </span>

    </div>

</header>

{{-- NAVIGASI CEPAT MOBILE --}}
<nav
    class="fp-mobile-nav"
    aria-label="Navigasi cepat"
>

    @foreach ($fpNav as [$r, $label, $icon, $active])

        <a
            href="{{ route($r) }}"
            class="{{ request()->routeIs($active) ? 'is-active' : '' }}"
            @if (request()->routeIs($active))
                aria-current="page"
            @endif
        >

            @include('user.partials.premium-icon', ['name' => $icon])

            <span>{{ $label }}</span>

        </a>

    @endforeach

</nav>
