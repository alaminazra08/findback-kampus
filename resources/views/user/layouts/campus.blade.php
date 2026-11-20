<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#7d0d2b">
    <meta name="description" content="{{ $fbBrand['site_description'] }}">
    <title>FindBack Kampus</title>
    <link rel="icon" href="{{ asset('images/logo-findback.png') }}">

    {{-- CSS USER --}}

    <link rel="stylesheet" href="{{ asset('css/user/campus.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('css/user/navbar-premium.css') }}?v=5">
    @if (request()->routeIs('user.claims.index'))
    <link rel="stylesheet" href="{{ asset('css/user/user-claims.css') }}?v=4">
    @endif
    <link rel="stylesheet" href="{{ asset('css/shared/view-utilities.css') }}">

    {{-- JAVASCRIPT --}}

    <script src="{{ asset('js/user/campus.js') }}?v=1" defer></script>
    <script src="{{ asset('js/shared/view-interactions.js') }}"></script>

    <!-- FINDBACK_UI_TEXT_KONSISTEN_V1 -->
    <link rel="stylesheet" href="{{ asset('css/shared/ui-text-consistency.css') }}?v=1">

    <!-- FINDBACK_TYPOGRAPHY_V1 -->
    <link rel="stylesheet" href="{{ asset('css/shared/typography.css') }}">
        <style>
        @view-transition { navigation: auto; }
        ::view-transition-old(root),
        ::view-transition-new(root) { animation: none; }
        .cu-main, .cu-main *, .cu-main *::before, .cu-main *::after { animation: none !important; }
    </style>

    @if (request()->routeIs('user.profile.index'))
        <script>
            // Sembunyikan halaman saat posisi scroll dipulihkan setelah menyimpan.
            try {
                if (sessionStorage.getItem('findback:profil:scroll') !== null) {
                    document.documentElement.style.visibility = 'hidden';
                    // Tampilkan lagi halaman walaupun script di bawah mengalami error.
                    window.addEventListener('load', () => {
                        document.documentElement.style.removeProperty('visibility');
                    }, { once: true });
                }
            } catch {
                // Browser tetap menampilkan halaman jika penyimpanan diblokir.
            }
        </script>
    @endif

    {{-- Gaya Chat User dimuat sebelum halaman ditampilkan --}}
    @if (request()->routeIs('user.chat.index'))
        <link rel="stylesheet" href="{{ asset('css/chat/chat-ui.css') }}?v=2">
        <link rel="stylesheet" href="{{ asset('css/chat/user-chat-modal.css') }}?v=14">
        <link rel="stylesheet" href="{{ asset('css/chat/findback-chat-compact.css') }}?v=7">
    @endif
</head>

<body class="fb-responsive cu-body {{ request()->routeIs('user.reports.index', 'user.claims.index') ? 'fb-activity-page' : '' }}">

    @php
        $noticeCount = auth()->user()->unreadNotifications()->count();
        $siteName = $fbBrand['site_name'];
        $institutionName = $fbBrand['institution_name'];
        $navUser = auth()->user();
        $navPhoto = $navUser->profile_photo_path;
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

                
{{-- LAPOR BARANG --}}
<details class="cu-dropdown cu-report-dropdown">
    <summary class="{{ request()->routeIs('user.reports.*create') ? 'active' : '' }}">
        <svg class="fb-nav-icon" viewBox="0 0 24 24"
             fill="none" stroke="currentColor"
             stroke-width="1.8" stroke-linecap="round"
             stroke-linejoin="round" aria-hidden="true">
            <path d="M6 3h9l4 4v14H6z" />
            <path d="M15 3v5h4M9 13h7M9 17h7" />
        </svg>

        <span>
            {{ request()->routeIs('user.reports.lost.create')
                ? 'Barang Hilang'
                : (request()->routeIs('user.reports.found.create')
                    ? 'Barang Ditemukan'
                    : 'Lapor Barang') }}
        </span>

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
                        <span class="cu-avatar" aria-hidden="true">

                            @if ($navPhoto && \Illuminate\Support\Facades\Storage::disk('public')->exists($navPhoto))
                                <img
                                    src="{{ route('user.profile.photo', ['v' => basename($navPhoto)]) }}"
                                    alt=""
                                    style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;"
                                >
                            @else
                                {{ mb_strtoupper(mb_substr(trim($navUser->name), 0, 1)) }}
                            @endif

                        </span>
                        <span class="cu-name">
                            {{ $navUser->name }}
                            <small>Civitas Akademika</small>
                        </span>
                        <span>⌄</span>
                    </summary>
                    <div>
                        <a href="{{ route('user.profile.index') }}">
                            Profil saya
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

        {{-- FLOATING NOTIFICATION USER --}}

        <div class="cu-toast-stack" aria-live="polite">

            @if (session('success'))
                <div class="cu-alert success" role="status" data-auto-dismiss>
                    <span class="cu-toast-icon">✓</span>
                    <div class="cu-toast-message">
                        {{ session('success') }}
                    </div>

                </div>

            @endif

            @if (session('error'))
                <div class="cu-alert error" role="alert" data-auto-dismiss>
                    <span class="cu-toast-icon">!</span>
                    <div class="cu-toast-message">
                        {{ session('error') }}
                    </div>

                </div>

            @endif

            @if (session('warning'))
                <div class="cu-alert warning" role="alert" data-auto-dismiss>
                    <span class="cu-toast-icon">!</span>
                    <div class="cu-toast-message">
                        {{ session('warning') }}
                    </div>

                </div>

            @endif

            @if (session('info'))
                <div class="cu-alert info" role="status" data-auto-dismiss>
                    <span class="cu-toast-icon">i</span>
                    <div class="cu-toast-message">
                        {{ session('info') }}
                    </div>

                </div>

            @endif

            @if ($errors->any())
                <div class="cu-alert error" role="alert" data-auto-dismiss>
                    <span class="cu-toast-icon">!</span>
                    <div class="cu-toast-message">
                        <strong>Periksa kembali isian berikut:</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>

                </div>

            @endif

        </div>

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
    <link rel="stylesheet" href="{{ asset('css/shared/responsive-all-devices.css') }}?v=20261111">
    <script src="{{ asset('js/shared/responsive-viewport.js') }}?v=20261111" defer></script>
</body>
</html>
