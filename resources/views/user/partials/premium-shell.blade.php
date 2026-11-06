@php
    $fpNav = [
        ['dashboard', 'Beranda', 'home', 'dashboard'],
        ['user.items.index', 'Cari Barang', 'search', 'user.items.*'],
        ['user.reports.index', 'Laporan Saya', 'report', 'user.reports.index'],
        ['user.claims.index', 'Klaim Saya', 'claim', 'user.claims.*'],
        ['user.chat.index', 'Chat Admin', 'message', 'user.chat.*'],
    ];

    $fpName = auth()->user()->name;

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

<a class="fp-skip" href="#fp-content">Langsung ke konten</a>

<div class="fp-overlay" id="fp-overlay" hidden></div>

<aside class="fp-sidebar" id="fp-sidebar" aria-label="Navigasi pengguna">

    <a class="fp-brand" href="{{ route('dashboard') }}">
        <img src="{{ asset('images/logo-findback.png') }}" alt="">
        <span>
            FindBack
            <span class="fp-brand-sub">KAMPUS · FST</span>
        </span>
        <span class="fp-brand-dot"></span>
    </a>

    <div class="fp-workspace">
        <span class="fp-workspace-icon">F</span>
        <div>
            <strong>Ruang pengguna</strong>
            <small>UIN Imam Bonjol Padang</small>
        </div>
    </div>

    <p class="fp-nav-label">RUANG UTAMA</p>

    <nav aria-label="Menu utama">
        @foreach ($fpNav as [$r, $label, $icon, $active])
            <a class="fp-nav-link {{ request()->routeIs($active) ? 'is-active' : '' }}" href="{{ route($r) }}"
                @if (request()->routeIs($active)) aria-current="page" @endif>
                @include('user.partials.premium-icon', ['name' => $icon])

                <span>{{ $label }}</span>

                @if (request()->routeIs($active))
                    <span class="fp-active-dot"></span>
                @endif
            </a>
        @endforeach
    </nav>

    <p class="fp-nav-label">BANTU TEMUKAN KEMBALI</p>

    <nav aria-label="Pelaporan barang">

        <a class="fp-nav-link {{ request()->routeIs('user.reports.lost.*') ? 'is-active' : '' }}"
            href="{{ route('user.reports.lost.create') }}">
            @include('user.partials.premium-icon', ['name' => 'lost'])
            <span>Laporkan Kehilangan</span>
        </a>

        <a class="fp-nav-link {{ request()->routeIs('user.reports.found.*') ? 'is-active' : '' }}"
            href="{{ route('user.reports.found.create') }}">
            @include('user.partials.premium-icon', ['name' => 'found'])
            <span>Laporkan Penemuan</span>
        </a>

    </nav>

    <div class="fp-sidebar-bottom">

        <div class="fp-care">
            <span>SEDIKIT PEDULI, BANYAK BERARTI.</span>

            <p>
                Barang yang kamu temukan bisa sangat berarti bagi seseorang.
            </p>

            <a href="{{ route('user.reports.found.create') }}">
                Bantu kembalikan
                @include('user.partials.premium-icon', ['name' => 'arrow'])
            </a>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button class="fp-exit" type="submit">
                @include('user.partials.premium-icon', ['name' => 'logout'])
                Keluar dari akun
            </button>
        </form>

    </div>

</aside>

<header class="fp-topbar">

    <div class="fp-top-left">

        <button class="fp-menu" id="fp-menu" type="button" aria-label="Buka navigasi" aria-controls="fp-sidebar"
            aria-expanded="false">
            @include('user.partials.premium-icon', ['name' => 'menu'])
        </button>

        <span class="fp-top-label">
            Ruang pengguna
            <span>/</span>
            <strong>{{ $fpCurrentPage }}</strong>
        </span>

    </div>

    <div class="fp-top-right">

        <span class="fp-campus">
            {{ $fbBrand['institution_name'] }}
        </span>

        <span class="fp-top-divider"></span>

        <span class="fp-avatar">
            {{ mb_strtoupper(mb_substr($fpName, 0, 1)) }}
        </span>

        <span class="fp-account">
            <strong>{{ $fpName }}</strong>
            <small>Akun pengguna</small>
        </span>

    </div>

</header>

<nav class="fp-mobile-nav" aria-label="Navigasi cepat">

    @foreach ($fpNav as [$r, $label, $icon, $active])
        <a href="{{ route($r) }}" class="{{ request()->routeIs($active) ? 'is-active' : '' }}"
            @if (request()->routeIs($active)) aria-current="page" @endif>
            @include('user.partials.premium-icon', ['name' => $icon])
            <span>{{ $label }}</span>
        </a>
    @endforeach

</nav>
