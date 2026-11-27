<!doctype html>
<html @if (\App\Support\AdminSettings::brandColor($fbBrand) !== '') data-custom-brand @endif lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#7d0d2b">
    <meta name="description" content="{{ $fbBrand['site_description'] }}">
    <title>FindBack Kampus</title>
    <link rel="icon" href="{{ \App\Support\AdminSettings::logoUrl($fbBrand) }}">

{{-- Memuat logo utama lebih awal --}}
<link
    rel="preload"
    as="image"
    href="{{ \App\Support\AdminSettings::logoUrl($fbBrand) }}"
    fetchpriority="high"
>


    {{-- CSS USER --}}

    
<link rel="stylesheet" href="{{ asset('css/user/campus.css') }}?v=4">

    <link rel="stylesheet" href="{{ asset('css/user/navbar-premium.css') }}?v=18">
    @if (request()->routeIs('user.claims.show'))
    <link rel="stylesheet" href="{{ asset('css/user/user-claims.css') }}?v=4">
    @endif
    <link rel="stylesheet" href="{{ asset('css/shared/view-utilities.css') }}">

{{-- Tampilan responsif dimuat sebelum halaman ditampilkan --}}
@unless (request()->routeIs('user.chat.index'))
    <link rel="stylesheet" href="{{ asset('css/shared/responsive-all-devices.css') }}?v=20261111">
@endunless


    {{-- JAVASCRIPT --}}

    <script src="{{ asset('js/user/campus.js') }}?v=4" defer></script>
    <script src="{{ asset('js/user/mobile-navigation-behavior.js') }}?v=20261127-center-locked-v7" defer></script>
    <script src="{{ asset('js/shared/view-interactions.js') }}"></script>

    <!-- FINDBACK_UI_TEXT_KONSISTEN_V1 -->
    <link rel="stylesheet" href="{{ asset('css/shared/ui-text-consistency.css') }}?v=1">

    <!-- FINDBACK_TYPOGRAPHY_V1 -->
    <link rel="stylesheet" href="{{ asset('css/shared/typography.css') }}">
    
<style>
    /* Navigasi halaman tanpa animasi tambahan */
    @view-transition {
        navigation: auto;
    }

    ::view-transition-old(root),
    ::view-transition-new(root) {
        animation: none !important;
    }
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
        <link rel="stylesheet" href="{{ asset('css/shared/responsive-all-devices.css') }}?v=20261111">
        @include('shared.chat-tools-style')
    @endif
   
{{-- CSS konsisten untuk empat halaman utama --}}
@if (request()->routeIs(
    'dashboard',
    'user.reports.index',
    'user.reports.lost.create',
    'user.reports.found.create',
    'user.claims.*'
))
    <link rel="stylesheet" href="{{ asset('css/report-cycle.css') }}?v=13">
    <link rel="stylesheet" href="{{ asset('css/user/activity-consistency.css') }}?v=3">
    <link rel="stylesheet" href="{{ asset('css/user/activity-refresh.css') }}?v=20261127-4">
    <link rel="stylesheet" href="{{ asset('css/user/activity-tabs-polish.css') }}?v=20261127-3">
@endif

@if (request()->routeIs('user.reports.index'))
    <link rel="stylesheet" href="{{ asset('css/user/my-reports-premium.css') }}?v=20261127-5">
@endif

    @include('partials.findback-brand-theme')




{{-- Navigasi cepat menggunakan Turbo Drive --}}




<style>
    /* Posisi scrollbar tetap konsisten */
    html {
        scrollbar-gutter: stable;
    }

</style>



    <link rel="stylesheet" href="{{ asset('css/shared/notification-preview.css') }}?v=5">
    <script src="{{ asset('js/shared/notification-bell-motion.js') }}?v=3" defer></script>

    @if (request()->routeIs('user.profile.index', 'user.profile.edit', 'user.account.security'))
    <style>
        /* Halaman profil fokus hanya pada layar mobile. */
        @media(max-width:800px) {
        html:has(body.fbp-standalone-page) { background:#fff; scrollbar-gutter:stable; }
        body.fbp-standalone-page { background:#fff; min-height:100vh; }
        body.fbp-standalone-page .cu-header,
        body.fbp-standalone-page .cu-mobile-nav,
        body.fbp-standalone-page .cu-footer { display:none !important; }
        body.fbp-standalone-page .cu-main {
            width:100%; max-width:760px; min-height:100vh;
            margin:0 auto; padding:0 0 48px; background:#fff;
        }
        body.fbp-standalone-page .cu-toast-stack { padding:0 22px; }
        body.fbp-standalone-page .cu-toast-stack:empty { display:none; }
        }
        @media(max-width:600px) {
            body.fbp-standalone-page .cu-main { min-height:100dvh; padding-bottom:28px; }
        }
    </style>
    @endif

    {{-- Acuan final navbar mobile: harus dimuat paling akhir agar posisi,
         ukuran, safe-area, dan perilaku scroll seragam di seluruh halaman. --}}

    <link rel="stylesheet" href="{{ asset('css/user/mobile-nav-stable.css') }}?v=20261127-center-locked-v7">

</head>


<body class="fb-responsive cu-body {{ request()->routeIs('user.reports.index', 'user.claims.index') ? 'fb-activity-page' : '' }} {{ request()->routeIs('user.account.index') ? 'cu-account-hub-page' : '' }} {{ request()->routeIs('user.profile.index', 'user.profile.edit', 'user.account.security') ? 'fbp-standalone-page' : '' }}">

    @php
        $noticeCount = auth()->user()->unreadNotifications()->count();
        $siteName = $fbBrand['site_name'];
        $institutionName = $fbBrand['institution_name'];
        $navUser = auth()->user();
        $navPhoto = $navUser->profile_photo_path;
    @endphp

    {{-- HEADER / NAVBAR --}}

    

<header class="cu-header" id="fb-permanent-header">


        <div class="cu-nav-wrap">

            {{-- IDENTITAS APLIKASI DINAMIS --}}

            <a class="cu-brand"
               
href="{{ route('dashboard') }}" 

               aria-label="{{ $siteName }} - Beranda">
               
<img
    src="{{ \App\Support\AdminSettings::logoUrl($fbBrand) }}"
    alt=""
    width="46"
    height="46"
    loading="eager"
    fetchpriority="high"
    decoding="async"
>

                <span>
                    {{ $siteName }}
                    <small>{{ $institutionName }}</small>
                </span>
            </a>

            {{-- NAVIGASI UTAMA --}}

            <nav class="cu-nav" id="cu-nav" aria-label="Navigasi utama">

                {{-- BERANDA --}}

                <a
                    class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"
                    href="{{ route('dashboard') }}"
                    @if (request()->routeIs('dashboard')) aria-current="page" @endif
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
        
<a href="{{ route('user.reports.lost.create') }}" >

            Barang hilang
        </a>

        
<a href="{{ route('user.reports.found.create') }}" >

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

                <details class="cu-dropdown cu-notice-preview">
                    <summary class="cu-bell" aria-label="Notifikasi, {{ $noticeCount }} belum dibaca">
                        @include('user.components.icon', ['icon' => 'bell'])
                        @if ($noticeCount)
                            <b aria-hidden="true">{{ min($noticeCount, 99) }}</b>
                        @endif
                    </summary>
                    <section class="cu-notice-panel fb-notice-preview" aria-labelledby="cu-notice-heading">
                        <h2 id="cu-notice-heading" class="fb-notice-preview-head">Notifikasi</h2>
                        <ul class="fb-notice-preview-list">
                            @forelse ($navUser->notifications()->latest()->limit(4)->get() as $notice)
                                @php
                                    $noticeData = $notice->data;
                                    $noticeTitle = \App\Support\WorkflowStatus::notificationText($noticeData['title'] ?? 'Pemberitahuan');
                                @endphp
                                <li data-notice-id="{{ $notice->id }}" class="fb-notice-preview-row {{ $notice->read_at === null ? 'is-unread' : '' }}">
                                    <a class="fb-notice-preview-row-link" href="{{ route('user.notifications.open', $notice->id) }}" aria-label="Buka notifikasi: {{ $noticeTitle }}">
                                        <div class="fb-notice-preview-copy">
                                            <strong>{{ $noticeTitle }}</strong>
                                            @if (!empty($notice->data['message']))
                                                <p>{{ \App\Support\WorkflowStatus::notificationText($notice->data['message']) }}</p>
                                            @endif
                                            <div class="cu-notice-meta">
                                                <time datetime="{{ $notice->created_at->toIso8601String() }}">{{ $notice->created_at->locale('id')->diffForHumans() }}</time>
                                                @if ($notice->read_at === null)
                                                    <span class="cu-notice-unread">Belum dibaca</span>
                                                @endif
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            @empty
                                <li>Belum ada notifikasi.</li>
                            @endforelse
                        </ul>
                        <a class="cu-notice-all fb-notice-preview-all" href="{{ route('user.notifications.index') }}">Lihat semua notifikasi <span aria-hidden="true">→</span></a>
                    </section>
                </details>

                {{-- PROFIL USER --}}

                <details class="cu-dropdown cu-profile">
                    <summary aria-label="Menu akun {{ $navUser->name }}">
                        <span class="cu-avatar" aria-hidden="true">

                            @if ($navPhoto && \Illuminate\Support\Facades\Storage::disk('public')->exists($navPhoto))
                                <img
                                    src="{{ route('user.profile.photo', ['v' => basename($navPhoto)]) }}"
                                    alt=""
                                >
                            @else
                                {{ mb_strtoupper(mb_substr(trim($navUser->name), 0, 1)) }}
                            @endif

                        </span>
                        <span class="cu-name">
                            {{ $navUser->name }}
                            <small>Civitas Akademika</small>
                        </span>
                        <svg class="cu-profile-chevron" width="14" height="14" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                             stroke-linejoin="round" aria-hidden="true">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </summary>
                    <div>
                        <a href="{{ route('user.account.index') }}">
                            Akun
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

    @include('user.layouts.mobile-navigation')

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

        @yield('content')    </main>

    {{-- FOOTER DINAMIS --}}

    <footer class="cu-footer">
        <a class="cu-brand" href="{{ route('dashboard') }}">
            {{ $siteName }}
        </a>
        <span>{{ $institutionName }}</span>
        <small>© {{ date('Y') }}</small>
    </footer>
   
    <script src="{{ asset('js/shared/responsive-viewport.js') }}?v=20261111" defer></script>


{{-- Navigasi standar digunakan pada semua halaman agar status navbar
     desktop dan mobile selalu dirender konsisten oleh server. --}}

</body>
</html>
