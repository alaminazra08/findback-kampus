
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#800d2b">
    <meta name="description" content="{{ $fbBrand['site_description'] }}">

    <title>@yield('title') | {{ $fbBrand['site_name'] }}</title>

    <link rel="icon" href="{{ asset('images/logo-findback.png') }}">

    {{-- CSS USER --}}
    <link rel="stylesheet" href="{{ asset('css/user/campus.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/user/navbar-premium-v3.css') }}?v=3">
    <link rel="stylesheet" href="{{ asset('css/shared/view-utilities.css') }}">

    {{-- JAVASCRIPT --}}
    <script src="{{ asset('js/user/campus.js') }}?v=1" defer></script>
    <script src="{{ asset('js/shared/view-interactions.js') }}"></script>
</head>

<body class="cu-body">

    <a class="cu-skip" href="#main">Langsung ke konten</a>

    @php
        $noticeCount = auth()->user()->unreadNotifications()->count();
        $siteName = $fbBrand['site_name'];
        $institutionName = $fbBrand['institution_name'];
    @endphp

    {{-- HEADER / NAVBAR --}}
    <header class="cu-header">
        <div class="cu-nav-wrap">

            {{-- IDENTITAS APLIKASI DINAMIS --}}
            <a class="cu-brand"
               href="{{ route('dashboard') }}"
               aria-label="{{ $siteName }} - Beranda">

                <img
                    src="{{ asset('images/logo-findback.png') }}"
                    alt=""
                >

                <span>
                    {{ $siteName }}
                    <small>{{ $institutionName }}</small>
                </span>
            </a>

            {{-- MENU MOBILE --}}
            <button
                id="cu-menu"
                class="cu-menu"
                type="button"
                aria-expanded="false"
                aria-controls="cu-nav"
                aria-label="Buka menu"
            >
                ☰
            </button>

            {{-- NAVIGASI UTAMA --}}
            <nav class="cu-nav" id="cu-nav" aria-label="Navigasi utama">

                {{-- BERANDA --}}
                <a
                    class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"
                    href="{{ route('dashboard') }}"
                >
                    <svg class="fb-nav-icon" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor"
                         stroke-width="1.8" stroke-linecap="round"
                         stroke-linejoin="round" aria-hidden="true">
                        <path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z" />
                    </svg>
                    <span>Beranda</span>
                </a>

                {{-- LAPOR BARANG --}}
                <details class="cu-dropdown">
                    <summary class="{{ request()->routeIs('user.reports.*create') ? 'active' : '' }}">

                        <svg class="fb-nav-icon" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor"
                             stroke-width="1.8" stroke-linecap="round"
                             stroke-linejoin="round" aria-hidden="true">
                            <path d="M6 3h9l4 4v14H6z" />
                            <path d="M15 3v5h4M9 13h7M9 17h7" />
                        </svg>

                        <span>Lapor Barang</span>

                        <svg class="fb-nav-icon" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor"
                             stroke-width="1.8" stroke-linecap="round"
                             stroke-linejoin="round" aria-hidden="true">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </summary>

                    <div>
                        <a href="{{ route('user.reports.lost.create') }}">
                            Barang hilang
                        </a>

                        <a href="{{ route('user.reports.found.create') }}">
                            Barang ditemukan
                        </a>
                    </div>
                </details>

                {{-- AKTIVITAS SAYA --}}
                <a
                    class="{{ request()->routeIs('user.reports.index', 'user.claims.*') ? 'active' : '' }}"
                    href="{{ route('user.reports.index') }}"
                >
                    <svg class="fb-nav-icon" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor"
                         stroke-width="1.8" stroke-linecap="round"
                         stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" />
                        <path d="M12 7v5l4 3" />
                    </svg>
                    <span>Aktivitas Saya</span>
                </a>

            </nav>

            {{-- AKUN DAN NOTIFIKASI --}}
            <div class="cu-account">

                {{-- NOTIFIKASI --}}
                <a
                    class="cu-bell"
                    href="{{ route('user.notifications.index') }}"
                    aria-label="Notifikasi, {{ $noticeCount }} belum dibaca"
                >
                    @include('user.components.icon', ['icon' => 'bell'])

                    @if ($noticeCount)
                        <b>{{ min($noticeCount, 99) }}</b>
                    @endif
                </a>

                {{-- PROFIL USER --}}
                <details class="cu-dropdown cu-profile">
                    <summary>
                        <span class="cu-avatar">
                            {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                        </span>

                        <span class="cu-name">
                            {{ auth()->user()->name }}
                            <small>Civitas Akademika</small>
                        </span>

                        <span>⌄</span>
                    </summary>

                    <div>
                        <a href="{{ route('user.profile.index') }}">
                            Profil saya
                        </a>

                        <a href="{{ route('user.profile.index') }}#password">
                            Ubah password
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit">
                                Keluar dari akun
                            </button>
                        </form>
                    </div>
                </details>

            </div>

        </div>
    </header>

    {{-- KONTEN UTAMA --}}
    <main class="cu-main" id="main">

        @if (session('success'))
            <div class="cu-alert success" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="cu-alert" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="cu-alert" role="alert">
                <strong>Periksa kembali isian berikut:</strong>

                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')

    </main>

    {{-- FOOTER DINAMIS --}}
    <footer class="cu-footer">

        <a class="cu-brand" href="{{ route('dashboard') }}">
            {{ $siteName }}
        </a>

        <span>{{ $institutionName }}</span>

        <small>© {{ date('Y') }}</small>

    </footer>

</body>
</html>
