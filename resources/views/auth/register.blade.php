<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="theme-color" content="#850c2d">

    <title>Daftar Akun | {{ $fbBrand['site_name'] }}</title>

    <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png">

    <link rel="stylesheet" href="{{ asset('css/auth/auth.css') }}">

    <link rel="stylesheet" href="{{ asset('css/auth/register.css') }}?v=7">

    <link rel="stylesheet" href="{{ asset('css/shared/view-utilities.css') }}">
    <script src="{{ asset('js/shared/view-interactions.js') }}"></script>
</head>

<body class="fb-auth-page fb-register-page">

    <main class="fb-auth-shell">

        {{-- Desktop: konsisten dengan panel gedung pada halaman Login --}}

        <section class="fb-auth-brand fb-register-desktop-visual"
            aria-label="Fakultas Sains dan Teknologi UIN Imam Bonjol Padang">

            <img class="fb-register-desktop-photo" src="{{ asset('images/login-fst-premium.jpg') }}"
                alt="Gedung Fakultas Sains dan Teknologi UIN Imam Bonjol Padang" width="1086" height="1448"
                loading="eager" decoding="async">

            <div class="fb-register-desktop-veil" aria-hidden="true"></div>

            <div class="fb-register-desktop-brand">

                <span class="fb-register-desktop-logo"><img src="{{ asset('images/logo-findback.png') }}" alt=""
                        width="62" height="62"></span>

                <span class="fb-register-desktop-identity"><strong>FindBack <em>Kampus</em></strong><small>LOST &amp;
                        FOUND · FST UIN IB</small></span>

            </div>

            <div class="fb-register-desktop-message">

                <span class="fb-register-desktop-eyebrow">● &nbsp; BERGABUNG BERSAMA FINDBACK</span>

                <h2>Langkah pertama.<br><em>Bersama FindBack.</em></h2>

                <p>Mulai melaporkan barang hilang, membantu menemukan pemiliknya, dan terhubung melalui satu layanan
                    kampus.</p>

                <span class="fb-register-desktop-divider" aria-hidden="true"></span>

                <small>FAKULTAS SAINS DAN TEKNOLOGI · UIN IMAM BONJOL PADANG</small>

            </div>

        </section>

        {{-- Ilustrasi Premium Formal hanya untuk halaman Registrasi mobile --}}

        <div class="fb-register-mobile-hero">

            <div class="fb-register-mobile-brand" aria-label="FindBack Kampus">

                <img src="{{ asset('images/logo-findback.png') }}" alt="" width="36" height="36">

                <span><strong>{{ $fbBrand['site_name'] }}</strong><small>LOST &amp; FOUND · FST UIN IB</small></span>

            </div>

            <img class="fb-register-mobile-art" src="{{ asset('images/register-mobile-findback.svg') }}"
                alt="Ilustrasi mahasiswa pria berpakaian sopan membuat akun melalui formulir pendaftaran FindBack Kampus"
                width="520" height="360" loading="eager">

            <a class="fb-register-mobile-back" href="{{ route('home') }}" aria-label="Kembali ke beranda">

                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" aria-hidden="true">

                    <path d="M19 12H5" />
                    <path d="m12 19-7-7 7-7" />

                </svg>

            </a>

        </div>

        <section class="fb-auth-content" aria-labelledby="register-title">

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

                {{-- Identitas alternatif lama disembunyikan karena mobile sudah memiliki brand pada hero --}}

                <div class="fb-auth-mobile-brand" aria-label="FindBack Kampus">

                    <span class="fb-auth-mobile-brand__icon" aria-hidden="true">

                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round">

                            <circle cx="10.5" cy="10.5" r="6.5" />

                            <path d="m15.5 15.5 5 5" />

                            <path d="M8.5 8.5a2.5 2.5 0 0 1 4.4 1.7c0 1.5-2.4 1.8-2.4 3" />

                            <path d="M10.5 15.7h.01" />

                        </svg>

                    </span>

                    <span><strong>{{ $fbBrand['site_name'] }}</strong><small>LOST &amp; FOUND KAMPUS</small></span>

                </div>

                <header class="fb-auth-heading">

                    <span class="fb-auth-heading__eyebrow">BERGABUNG DENGAN FINDBACK</span>

                    <h1 id="register-title">Buat Akun Baru</h1>

                    <p>Isi data diri Anda untuk mulai menggunakan {{ $fbBrand['site_name'] }}.</p>

                </header>

                @if ($errors->any())
                    <div class="fb-auth-alert" role="alert">Mohon periksa kembali data pendaftaran Anda.</div>
                @endif

                <form class="fb-auth-form" method="POST" action="{{ route('register.store') }}">

                    @csrf

                    <div class="fb-auth-field">

                        <label for="name">Nama lengkap</label>

                        <div class="fb-auth-input-wrap">

                            <span class="fb-auth-input-icon" aria-hidden="true">

                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="8" r="4" />
                                    <path d="M4 21v-2a8 8 0 0 1 16 0v2" />
                                </svg>

                            </span>

                            <input id="name" name="name" type="text" value="{{ old('name') }}"
                                autocomplete="name" placeholder="Masukkan nama lengkap" maxlength="255" required
                                autofocus aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                                @error('name') aria-describedby="name-error" @enderror>

                        </div>

                        @error('name')
                            <p class="fb-auth-error" id="name-error">{{ $message }}</p>
                        @enderror

                    </div>

                    <div class="fb-auth-field">

                        <label for="email">Email</label>

                        <div class="fb-auth-input-wrap">

                            <span class="fb-auth-input-icon" aria-hidden="true">

                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="5" width="18" height="14" rx="2" />
                                    <path d="m3 7 9 6 9-6" />
                                </svg>

                            </span>

                            <input id="email" name="email" type="email" value="{{ old('email') }}"
                                autocomplete="email" placeholder="nama@email.com" maxlength="255" required
                                aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                                @error('email') aria-describedby="email-error" @enderror>

                        </div>

                        @error('email')
                            <p class="fb-auth-error" id="email-error">{{ $message }}</p>
                        @enderror

                    </div>

                    <div class="fb-auth-field">

                        <label for="password">Kata sandi</label>

                        <div class="fb-auth-input-wrap">

                            <span class="fb-auth-input-icon" aria-hidden="true">

                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="5" y="10" width="14" height="11" rx="2" />
                                    <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                                </svg>

                            </span>

                            <input id="password" name="password" type="password" autocomplete="new-password"
                                placeholder="Minimal 8 karakter" minlength="8" required
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

                    <div class="fb-auth-field">

                        <label for="password_confirmation">Ketik ulang kata sandi</label>

                        <div class="fb-auth-input-wrap">

                            <span class="fb-auth-input-icon" aria-hidden="true">

                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="5" y="10" width="14" height="11" rx="2" />
                                    <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                                </svg>

                            </span>

                            <input id="password_confirmation" name="password_confirmation" type="password"
                                autocomplete="new-password" placeholder="Konfirmasi kata sandi" minlength="8"
                                required>

                            <button class="fb-auth-password-toggle" type="button"
                                data-toggle-password="password_confirmation"
                                aria-label="Tampilkan konfirmasi kata sandi" aria-pressed="false"
                                title="Tampilkan konfirmasi kata sandi">

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

                    </div>

                    <button class="fb-auth-submit fb-register-submit" type="submit">

                        <span class="fb-register-submit__icon" aria-hidden="true">

                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"
                                stroke-linecap="round" stroke-linejoin="round">

                                <circle cx="9" cy="7" r="4" />

                                <path d="M2 21v-2a4 4 0 0 1 4-4h6a4 4 0 0 1 4 4v2" />

                                <path d="M20 8v6M17 11h6" />

                            </svg>

                        </span>

                        <span>Daftar Sekarang</span>

                    </button>

                </form>

                <p class="fb-auth-switch">Sudah punya akun? <a href="{{ route('login') }}">Login di sini</a></p>

                <p class="fb-auth-footnote">Dengan mendaftar, Anda menyetujui penggunaan data untuk layanan pelaporan
                    dan pencarian barang sesuai kebijakan {{ $fbBrand['site_name'] }}.</p>

            </div>

        </section>

    </main>

    <script src="{{ asset('js/pages/auth/register.js') }}"></script>

</body>

</html>
