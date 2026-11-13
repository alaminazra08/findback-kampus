<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#850c2d">
    <meta name="description"
        content="Masuk ke FindBack Kampus untuk melaporkan dan menemukan kembali barang di lingkungan Fakultas Sains dan Teknologi.">
    <title>FindBack Kampus</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/auth/auth.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth/login-recovery-modal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/view-utilities.css') }}">
    <script src="{{ asset('js/shared/view-interactions.js') }}"></script>
<!-- FINDBACK_TYPOGRAPHY_V1 -->
<link rel="stylesheet" href="{{ asset('css/shared/findback-typography-v1.css') }}">
</head>

<body class="fb-responsive fb-auth-page fb-login-page">
    <main class="fb-auth-shell">
        {{-- Panel kiri khusus Login; gambar tidak berisi teks bawaan. --}}
        <section class="fb-auth-brand fb-login-visual" aria-label="Fakultas Sains dan Teknologi UIN Imam Bonjol Padang">
            <img class="fb-login-visual__photo" src="{{ asset('images/login-fst-premium.jpg') }}"
                alt="Gedung Fakultas Sains dan Teknologi dengan langit biru dan ornamen marun" width="1086"
                height="1448" loading="eager" decoding="async">
            <div class="fb-login-visual__veil" aria-hidden="true"></div>
            <div class="fb-login-visual__top">
                <span class="fb-login-visual__emblem">
                    <img src="{{ asset('images/logo-findback.png') }}" alt="" width="72" height="72"
                        loading="eager" decoding="async">
                </span>
                <span class="fb-login-visual__identity">
                    <strong>FindBack <span>Kampus</span></strong>
                    <small>LOST &amp; FOUND <span aria-hidden="true">•</span> FST UIN IB</small>
                </span>
            </div>
            <div class="fb-login-visual__message">
                <span class="fb-login-visual__eyebrow"><span></span> SELAMAT DATANG KEMBALI</span>
                <h2>Temukan kembali.<br><em>Bersama FindBack.</em></h2>
                <p>Satu tempat untuk melaporkan, mencari, dan membantu mengembalikan barang di lingkungan fakultas.</p>
                <span class="fb-login-visual__divider" aria-hidden="true"></span>
                <small>FAKULTAS SAINS DAN TEKNOLOGI · UIN IMAM BONJOL PADANG</small>
            </div>
        </section>
        <section class="fb-auth-content" aria-labelledby="login-title">
            <div class="fb-auth-form-wrap">
                <a class="fb-auth-back" href="{{ route('home') }}">
                    <span class="fb-auth-back__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 12H5" />
                            <path d="m12 19-7-7 7-7" />
                        </svg>
                    </span>
                    <span class="fb-auth-back__label">Kembali ke beranda</span>
                </a>
                <div class="fb-login-mobile-brand" aria-label="FindBack Kampus">
                    <img src="{{ asset('images/logo-findback.png') }}" alt="" width="30" height="30"
                        loading="lazy" decoding="async">
                    <span>FINDBACK KAMPUS</span>
                </div>
                <header class="fb-auth-heading">
                    <span class="fb-auth-heading__eyebrow">SELAMAT DATANG KEMBALI</span>
                    <h1 id="login-title">Masuk ke Akun Anda</h1>
                    <p>Masuk untuk melanjutkan pencarian dan pelaporan barang di {{ $fbBrand['site_name'] }}.</p>
                </header>
                @if (session('success'))
                    <div class="fb-login-success" role="status">{{ session('success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="fb-auth-alert" role="alert">Periksa kembali informasi login Anda.</div>
                @endif
                <form class="fb-auth-form" method="POST" action="{{ route('login.store') }}">
                    @csrf
                    <div class="fb-auth-field">
                        <label for="email">Email</label>
                        <div class="fb-auth-input-wrap">
                            <span class="fb-auth-input-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <rect x="3" y="5" width="18" height="14" rx="2" />
                                    <path d="m3 7 9 6 9-6" />
                                </svg></span>
                            <input id="email" name="email" type="email" autocomplete="username"
                                inputmode="email" value="{{ old('email') }}" placeholder="nama@email.com"
                                maxlength="255" required aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                                @error('email') aria-describedby="email-error" @enderror>
                        </div>
                        @error('email')
                            <p class="fb-auth-error" id="email-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="fb-auth-field">
                        <label for="password">Kata sandi</label>
                        <div class="fb-auth-input-wrap">
                            <span class="fb-auth-input-icon" aria-hidden="true"><svg viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <rect x="5" y="10" width="14" height="11" rx="2" />
                                    <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                                </svg></span>
                            <input id="password" name="password" type="password" autocomplete="current-password"
                                placeholder="Masukkan kata sandi" required
                                aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                                @error('password') aria-describedby="password-error" @enderror>
                            <button class="fb-auth-password-toggle" type="button" data-toggle-password="password"
                                aria-label="Tampilkan kata sandi" aria-pressed="false" title="Tampilkan kata sandi">
                                <svg class="fb-eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                                    aria-hidden="true">
                                    <path d="M2.2 12s3.6-7 9.8-7 9.8 7 9.8 7-3.6 7-9.8 7-9.8-7-9.8-7Z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                                <svg class="fb-eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                                    aria-hidden="true">
                                    <path d="m3 3 18 18" />
                                    <path d="M10.6 5.1A10 10 0 0 1 12 5c6.2 0 9.8 7 9.8 7a13.6 13.6 0 0 1-3.1 3.7" />
                                    <path d="M6.2 6.2C3.6 8 2.2 12 2.2 12s3.6 7 9.8 7a10 10 0 0 0 4.1-.9" />
                                    <path d="M10 10a3 3 0 0 0 4 4" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="fb-auth-error" id="password-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="fb-login-options">
                        <label class="fb-login-remember">
                            <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                            <span>Ingat saya</span>
                        </label>
                        @if (Route::has('password.request') && Route::has('password.email'))
                            <button class="fb-login-forgot-trigger" type="button" data-open-recovery
                                aria-haspopup="dialog" aria-controls="fb-recovery-modal">Lupa kata sandi?</button>
                        @endif
                    </div>
                    <button class="fb-auth-submit" type="submit">
                        <svg class="fb-auth-submit__login-icon" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"
                            aria-hidden="true">
                            <path d="M10 17l5-5-5-5" />
                            <path d="M15 12H3" />
                            <path d="M13 4h6a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-6" />
                        </svg>
                        <span>Masuk</span>
                    </button>
                </form>
                <div class="fb-login-divider" aria-hidden="true"><span>atau</span></div>
                @if (Route::has('google.redirect'))
                    <a class="fb-login-google" href="{{ route('google.redirect') }}">
                    @else
                        <button class="fb-login-google fb-login-google--disabled" type="button" disabled
                            title="Login Google belum diaktifkan">
                @endif
                <svg viewBox="0 0 48 48" width="22" height="22" aria-hidden="true">
                    <path fill="#EA4335"
                        d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z" />
                    <path fill="#4285F4"
                        d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6C44.4 38.03 46.98 31.88 46.98 24.55z" />
                    <path fill="#FBBC05"
                        d="M10.53 28.59A14.4 14.4 0 0 1 9.75 24c0-1.6.27-3.14.76-4.59l-7.98-6.2A23.9 23.9 0 0 0 0 24c0 3.86.93 7.5 2.56 10.78l7.97-6.19z" />
                    <path fill="#34A853"
                        d="M24 48c6.48 0 11.93-2.13 15.9-5.8l-7.73-6c-2.15 1.45-4.92 2.3-8.17 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z" />
                </svg>
                <span>Lanjutkan dengan Google</span>
                @if (Route::has('google.redirect'))
                    </a>
                @else
                    </button>
                    <p class="fb-login-google-note">Login Google belum diaktifkan. Gunakan email dan kata sandi
                        terlebih dahulu.</p>
                @endif
                <p class="fb-auth-switch">Belum punya akun? <a href="{{ route('register') }}">Daftar di sini</a></p>
            </div>
        </section>
    </main>
    @if (Route::has('password.email'))
        <div class="fb-recovery-overlay" id="fb-recovery-modal" hidden>
            <div class="fb-recovery-dialog" role="dialog" aria-modal="true" aria-labelledby="fb-recovery-title"
                aria-describedby="fb-recovery-description" tabindex="-1">
                <button class="fb-recovery-close" type="button" data-close-recovery
                    aria-label="Tutup pemulihan kata sandi">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M18 6 6 18M6 6l12 12" />
                    </svg>
                </button>
                <div class="fb-recovery-symbol" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                        stroke-linecap="round" stroke-linejoin="round">
                        <rect x="5" y="10" width="14" height="11" rx="2" />
                        <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                        <path d="m10 15 1.5 1.5L14 14" />
                    </svg>
                </div>
                <p class="fb-recovery-eyebrow">KEAMANAN AKUN</p>
                <h2 id="fb-recovery-title">Lupa kata sandi?</h2>
                <p class="fb-recovery-description" id="fb-recovery-description">Masukkan email akun {{ $fbBrand['site_name'] }}.
                    Kami akan mengirimkan tautan untuk membuat kata sandi baru.</p>
                @if (request()->query('recovery') === '1' && session('status'))
                    <div class="fb-recovery-feedback fb-recovery-feedback--success" role="status">
                        {{ session('status') }}</div>
                @endif
                @if (request()->query('recovery') === '1' && $errors->has('email'))
                    <div class="fb-recovery-feedback fb-recovery-feedback--error" role="alert">
                        {{ $errors->first('email') }}</div>
                @endif
                <form class="fb-recovery-modal-form" method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <label for="recovery-email">Alamat email</label>
                    <div class="fb-recovery-input-wrap">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="5" width="18" height="14" rx="2" />
                            <path d="m3 7 9 6 9-6" />
                        </svg>
                        <input id="recovery-email" name="email" type="email"
                            value="{{ request()->query('recovery') === '1' ? old('email') : '' }}"
                            autocomplete="email" inputmode="email" placeholder="nama@email.com" maxlength="255"
                            required>
                    </div>
                    <button class="fb-recovery-send" type="submit">
                        <span>Kirim tautan pemulihan</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M22 2 11 13" />
                            <path d="m22 2-7 20-4-9-9-4Z" />
                        </svg>
                    </button>
                </form>
                <button class="fb-recovery-return" type="button" data-close-recovery>
                    <span aria-hidden="true">←</span> Kembali ke Login
                </button>
            </div>
        </div>
    @endif

    <script src="{{ asset('js/pages/auth/login.js') }}"></script>

    <script src="{{ asset('js/pages/auth/recovery-modal.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('css/shared/responsive-all-devices.css') }}?v=20261111">
    <script src="{{ asset('js/shared/responsive-viewport.js') }}?v=20261111" defer></script>
</body>

</html>
