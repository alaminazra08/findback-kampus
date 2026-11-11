<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="theme-color" content="#7A0F2B">
    <meta name="description"
        content="{{ trim((string) ($fbBrand['site_description'] ?? '')) ?: 'Sistem informasi barang hilang dan ditemukan di kampus' }}">

    <title>FindBack Kampus | UIN Imam Bonjol Padang</title>

    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    <link rel="stylesheet" href="{{ asset('css/public/landing.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/view-utilities.css') }}">
    <script src="{{ asset('js/shared/view-interactions.js') }}"></script>
</head>

<body class="fb-responsive loading">

    <a class="skip-link" href="#konten-utama">Lewati ke konten utama</a>

    <!-- ==================================================
         SPLASH SCREEN / ANIMASI PEMBUKA
    =================================================== -->
    <div id="splash-screen" role="status" aria-label="Memuat FindBack Kampus">
        <div class="splash-content">

            <div class="splash-logo">
                <img src="{{ asset('images/logo-findback.png') }}" alt="Logo FindBack Kampus" width="112"
                    height="112">
            </div>

            <p class="splash-overline">
                SISTEM INFORMASI KAMPUS
            </p>

            <h1>
                FindBack <span>Kampus</span>
            </h1>

            <p class="splash-tagline">
                Lost &amp; Found Kampus
            </p>

            <div class="splash-loader" role="progressbar" aria-label="Memuat halaman">
                <span></span>
            </div>

            <small>
                Menyiapkan pengalaman terbaik untukmu...
            </small>

        </div>
    </div>

    <!-- ==================================================
         NAVBAR UTAMA
    =================================================== -->

    <header class="site-header">
        <nav class="container nav-inner" aria-label="Navigasi utama">

            <!-- BRAND -->
            <a href="{{ url('/') }}" class="brand" aria-label="FindBack Kampus - Beranda">
                <img class="brand-icon" src="{{ asset('images/logo-findback.png') }}" alt="Logo FindBack Kampus">

                <span class="brand-text">
                    <span class="brand-name">FindBack Kampus</span>
                    <span class="brand-caption">Lost &amp; Found Kampus</span>
                </span>
            </a>

            <!-- TOMBOL MENU MOBILE -->
            <button class="menu-toggle" id="menu-toggle" type="button" aria-label="Buka menu navigasi"
                aria-expanded="false" aria-controls="nav-links">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <!-- NAVIGASI TENGAH -->
            <div class="nav-links" id="nav-links">
                <a href="#beranda" class="active">Beranda</a>
                <a href="#fitur">Fitur</a>
                <a href="#cara-kerja">Cara Kerja</a>
            </div>

            <!-- TOMBOL AKSI KANAN -->
            <div class="nav-actions">
                <a href="{{ url('/login') }}" class="btn-login">
                    Masuk
                </a>

                <a href="{{ url('/register') }}" class="btn-register">
                    Daftar Sekarang
                    <span class="register-arrow" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                            <path d="M7 17 17 7" />
                            <path d="M8 7h9v9" />
                        </svg>
                    </span>
                </a>
            </div>

        </nav>
    </header>

    <!-- ==================================================
         KONTEN UTAMA
    =================================================== -->
    <main id="konten-utama">

        <!-- HERO SECTION -->
        <section class="hero" id="beranda">

            <!-- Dekorasi latar -->
            <div class="hero-orb orb-one" aria-hidden="true"></div>

            <div class="hero-orb orb-two" aria-hidden="true"></div>

            <div class="container hero-grid">

                <!-- Bagian kiri -->
                <div class="hero-copy">

                    <div class="eyebrow">
                        <span class="eyebrow-dot" aria-hidden="true"></span>

                        Layanan Barang Hilang dan Ditemukan
                    </div>

                    <h1>
                        Barang Hilang?<br>
                        Temukan Kembali<br>
                        Bersama <span>FindBack.</span>
                    </h1>

                    {{-- Deskripsi publik mengikuti Admin > Pengaturan Sistem --}}
                    <p class="hero-description fb-wrap-anywhere">
                        {{ trim((string) ($fbBrand['site_description'] ?? '')) ?: 'FindBack Kampus membantu civitas akademika melaporkan, mencari, dan menemukan barang yang hilang maupun ditemukan.' }}
                    </p>

                    <div class="hero-actions">

                        <a href="{{ url('/register') }}" class="btn btn-primary">
                            Daftar Sekarang
                            <span class="register-arrow" aria-hidden="true">
                                <svg viewBox="0 0 24 24" width="17" height="17" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" focusable="false">
                                    <path d="M7 17 17 7" />
                                    <path d="M8 7h9v9" />
                                </svg>
                            </span>
                        </a>

                        <a href="#fitur" class="btn btn-outline btn-explore">
                            <svg class="explore-icon" xmlns="http://www.w3.org/2000/svg" width="18"
                                height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"
                                aria-hidden="true">
                                <circle cx="11" cy="11" r="7" />
                                <path d="m20 20-4-4" />
                            </svg>

                            <span>Jelajahi Fitur</span>
                        </a>

                    </div>

                </div>

                <!-- Bagian kanan -->
                <aside class="hero-card" aria-labelledby="hero-card-title">

                    <div class="hero-card-heading">

                        <div class="heading-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle cx="12" cy="12" r="9" stroke="currentColor"
                                    stroke-width="1.7" />
                                <path d="m14.8 9.2-1.9 4-3.7 1.6 1.7-3.8 3.9-1.8Z" stroke="currentColor"
                                    stroke-width="1.7" stroke-linejoin="round" />
                            </svg>
                        </div>

                        <div>
                            <h2 id="hero-card-title">
                                Bagaimana FindBack Membantu?
                            </h2>

                            <p>
                                Dukungan pelaporan dan pencarian barang di lingkungan fakultas.
                            </p>
                        </div>

                    </div>

                    <div class="feature">

                        <div class="feature-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle cx="10.8" cy="10.8" r="6.8" stroke="currentColor"
                                    stroke-width="1.7" />
                                <path d="m16 16 4.5 4.5" stroke="currentColor" stroke-width="1.7"
                                    stroke-linecap="round" />
                            </svg>
                        </div>

                        <div>
                            <h3>Cari Barang</h3>

                            <p>
                                Telusuri informasi barang hilang atau ditemukan di lingkungan Fakultas Sains dan
                                Teknologi.
                            </p>
                        </div>

                    </div>

                    <div class="feature">

                        <div class="feature-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path
                                    d="M13.5 4H6.8A1.8 1.8 0 0 0 5 5.8v12.4A1.8 1.8 0 0 0 6.8 20h10.4a1.8 1.8 0 0 0 1.8-1.8V10"
                                    stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
                                <path d="m12 15 1-3.2L18.9 6a1.8 1.8 0 0 1 2.5 2.5l-5.8 5.9L12 15Z"
                                    stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" />
                            </svg>
                        </div>

                        <div>
                            <h3>Buat Laporan</h3>

                            <p>
                                Catat nama barang, lokasi, tanggal,
                                ciri-ciri, dan informasi pendukung lainnya.
                            </p>
                        </div>

                    </div>

                    <div class="feature">

                        <div class="feature-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 3 19 6v5.1c0 4.6-2.9 7.8-7 9.9-4.1-2.1-7-5.3-7-9.9V6l7-3Z"
                                    stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" />
                                <path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="1.7"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>

                        <div>
                            <h3>Verifikasi Klaim</h3>

                            <p>
                                Proses verifikasi membantu memastikan
                                barang dikembalikan kepada pemilik yang tepat.
                            </p>
                        </div>

                    </div>

                </aside>

            </div>

        </section>

        <!-- ==================================================
     RINGKASAN MANFAAT FINDBACK KAMPUS
=================================================== -->
        <section class="status-strip" aria-label="Ringkasan manfaat FindBack">

            <div class="status-heading">
                <span class="status-kicker">RINGKASAN MANFAAT</span>

                <h2>Manfaat untuk Civitas Akademika</h2>

                <p>
                    Solusi praktis untuk membantu pelaporan dan pencarian
                    barang di lingkungan kampus.
                </p>
            </div>

            <div class="container status-inner">

                <!-- 1. Laporan Terstruktur -->
                <div class="status-item">
                    <span class="status-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" focusable="false">
                            <path d="M7 3.75h7l4 4V20.25H7a1.5 1.5 0 0 1-1.5-1.5V5.25A1.5 1.5 0 0 1 7 3.75Z"
                                stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" />
                            <path d="M14 3.75v4h4M9 12h6M9 15.5h6" stroke="currentColor" stroke-width="1.7"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>

                    <div>
                        <strong>Laporan Terstruktur</strong>
                        <small>Laporan barang lebih teratur</small>
                    </div>
                </div>

                <!-- 2. Pencarian Praktis -->
                <div class="status-item">
                    <span class="status-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" focusable="false">
                            <circle cx="10.5" cy="10.5" r="6.25" stroke="currentColor"
                                stroke-width="1.7" />
                            <path d="m15.1 15.1 4.4 4.4M8 10.5h5" stroke="currentColor" stroke-width="1.7"
                                stroke-linecap="round" />
                        </svg>
                    </span>

                    <div>
                        <strong>Pencarian Praktis</strong>
                        <small>Cari barang di lingkungan fakultas</small>
                    </div>
                </div>

                <!-- 3. Verifikasi Admin -->
                <div class="status-item">
                    <span class="status-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" focusable="false">
                            <path d="M12 3.25 19 6v5.1c0 4.3-2.7 7.4-7 9.65-4.3-2.25-7-5.35-7-9.65V6l7-2.75Z"
                                stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" />
                            <path d="m8.6 12.1 2.2 2.2 4.7-4.8" stroke="currentColor" stroke-width="1.7"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>

                    <div>
                        <strong>Verifikasi Admin</strong>
                        <small>Mendukung proses pengembalian</small>
                    </div>
                </div>

            </div>

        </section>

        <!-- ==================================================
             FITUR UTAMA
        =================================================== -->
        <section class="section" id="fitur">

            <div class="container">

                <div class="section-heading">

                    <span class="section-kicker">
                        Fitur Utama
                    </span>

                    <h2>
                        Mendukung Civitas Akademika Fakultas Sains dan Teknologi
                    </h2>

                    <p>
                        Satu sistem untuk mendukung pelaporan, pencarian, dan verifikasi barang hilang dan barang
                        ditemukan di lingkungan Fakultas Sains dan Teknologi UIN Imam Bonjol Padang.
                    </p>

                </div>

                <div class="cards">

                    <!-- 1. Melaporkan Barang -->
                    <article class="card">
                        <div class="card-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none">
                                <path d="m12 3 8 4.3v9.4L12 21l-8-4.3V7.3L12 3Z" stroke="currentColor"
                                    stroke-width="1.7" stroke-linejoin="round" />
                                <path d="m4.5 7.5 7.5 4 7.5-4M12 12v8.5" stroke="currentColor" stroke-width="1.7"
                                    stroke-linejoin="round" />
                            </svg></div>

                        <h3>Melaporkan Barang Hilang/Ditemukan</h3>

                        <p>
                            Membantu pengguna melaporkan barang hilang atau
                            ditemukan beserta informasi dan fotonya.
                        </p>

                    </article>

                    <!-- 2. Kecocokan Barang Otomatis -->
                    <article class="card">
                        <div class="card-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none">
                                <circle cx="10.5" cy="10.5" r="6.5" stroke="currentColor"
                                    stroke-width="1.7" />
                                <path d="m15.5 15.5 4.5 4.5M8 10.5l1.7 1.7 3.3-3.5" stroke="currentColor"
                                    stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                            </svg></div>

                        <h3>Pencocokan Barang Cerdas</h3>

                        <p>
                            Membantu menemukan kemungkinan kecocokan berdasarkan
                            informasi barang untuk ditinjau dan dikonfirmasi oleh admin.
                        </p>

                    </article>

                    <!-- 3. Pencarian dan Filter Barang -->
                    <article class="card">
                        <div class="card-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none">
                                <circle cx="10.5" cy="10.5" r="6.5" stroke="currentColor"
                                    stroke-width="1.7" />
                                <path d="m15.5 15.5 4.5 4.5" stroke="currentColor" stroke-width="1.7"
                                    stroke-linecap="round" />
                            </svg></div>

                        <h3>Pencarian dan Filter Barang</h3>

                        <p>
                            Memudahkan pencarian barang berdasarkan kata kunci,
                            kategori, lokasi, tanggal, dan status.
                        </p>

                    </article>

                    <!-- 4. Klaim Barang -->
                    <article class="card">
                        <div class="card-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none">
                                <path
                                    d="M13.5 4H6.8A1.8 1.8 0 0 0 5 5.8v12.4A1.8 1.8 0 0 0 6.8 20h10.4a1.8 1.8 0 0 0 1.8-1.8V10"
                                    stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
                                <path d="m12 15 1-3.2L18.9 6a1.8 1.8 0 0 1 2.5 2.5l-5.8 5.9L12 15Z"
                                    stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" />
                            </svg></div>

                        <h3>Klaim Barang</h3>

                        <p>
                            Digunakan untuk mengajukan klaim atas barang
                            yang diduga milik pengguna.
                        </p>

                    </article>

                    <!-- 5. Chat Klaim -->
                    <article class="card">
                        <div class="card-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none">
                                <path d="M20 11.5a7.5 7.5 0 0 1-7.5 7.5H6l-3 2v-6.5A7.5 7.5 0 1 1 20 11.5Z"
                                    stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" />
                                <path d="M8 11.5h.01M12 11.5h.01M16 11.5h.01" stroke="currentColor" stroke-width="2.3"
                                    stroke-linecap="round" />
                            </svg></div>

                        <h3>Chat Klaim</h3>

                        <p>
                            Memudahkan komunikasi dengan admin terkait
                            verifikasi kepemilikan dan pengembalian barang.
                        </p>

                    </article>

                    <!-- 6. Notifikasi -->
                    <article class="card">
                        <div class="card-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none">
                                <path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9ZM10 21h4"
                                    stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg></div>

                        <h3>Notifikasi</h3>

                        <p>
                            Memberikan informasi tentang kecocokan barang
                            dan perkembangan laporan maupun klaim.
                        </p>

                    </article>

                    <!-- 7. Status Proses Barang -->
                    <article class="card">
                        <div class="card-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none">
                                <path d="M4 20V11M10 20V5M16 20v-7M22 20H2" stroke="currentColor" stroke-width="1.7"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg></div>

                        <h3>Status Proses Barang</h3>

                        <p>
                            Membantu pengguna memantau proses laporan
                            hingga barang dikembalikan kepada pemiliknya.
                        </p>

                    </article>

                </div>

            </div>

        </section>

        <!-- ==================================================
            CARA KERJA FINDBACK KAMPUS
        =================================================== -->
        <section class="how-section" id="cara-kerja">

            <div class="container">

                <!-- Judul Section -->
                <div class="section-heading">
                    <span class="section-kicker">CARA PENGGUNAAN</span>

                    <h2>Alur Penggunaan FindBack</h2>

                    <p>
                        Panduan penggunaan FindBack Kampus untuk melaporkan,
                        mencari, dan mengurus klaim barang di lingkungan
                        Fakultas Sains dan Teknologi UIN Imam Bonjol Padang.
                    </p>
                </div>

                <!-- Ringkasan Alur Penggunaan -->

                <!-- Ringkasan Alur Penggunaan: 8 Langkah -->
                <div class="steps">

                    <article class="step">
                        <div class="step-number">01</div>
                        <h3>Login ke Akun</h3>
                        <p>
                            Masuk ke website FindBack Kampus menggunakan akun
                            yang dimiliki untuk mengakses fitur pelaporan,
                            pencarian, klaim, dan riwayat laporan.
                        </p>
                    </article>

                    <article class="step">
                        <div class="step-number">02</div>
                        <h3>Laporkan Barang</h3>
                        <p>
                            Pilih menu Barang Hilang jika kehilangan barang
                            atau Barang Ditemukan jika menemukan barang.
                            Lengkapi formulir dengan informasi barang,
                            lokasi, tanggal, deskripsi, dan foto.
                        </p>
                    </article>

                    <article class="step">
                        <div class="step-number">03</div>
                        <h3>Tunggu Verifikasi</h3>
                        <p>
                            Tunggu admin memeriksa laporan. Jika disetujui,
                            laporan akan dipublikasikan. Jika ditolak,
                            periksa status laporan sesuai informasi sistem.
                        </p>
                    </article>

                    <article class="step">
                        <div class="step-number">04</div>
                        <h3>Cari Barang</h3>
                        <p>
                            Gunakan fitur pencarian berdasarkan kata kunci.
                            Manfaatkan filter kategori, lokasi, tanggal,
                            atau status jika tersedia untuk mempermudah
                            pencarian barang.
                        </p>
                    </article>

                    <article class="step">
                        <div class="step-number">05</div>
                        <h3>Ajukan Klaim</h3>
                        <p>
                            Jika menemukan barang yang diduga milikmu,
                            buka detail laporan barang ditemukan,
                            pilih tombol Klaim Barang, lalu lengkapi
                            informasi yang diminta.
                        </p>
                    </article>

                    <article class="step">
                        <div class="step-number">06</div>
                        <h3>Chat Klaim</h3>
                        <p>
                            Gunakan fitur chat klaim untuk berkomunikasi
                            dengan admin mengenai verifikasi kepemilikan
                            dan pengaturan serah terima barang.
                        </p>
                    </article>

                    <article class="step">
                        <div class="step-number">07</div>
                        <h3>Pantau Status</h3>
                        <p>
                            Periksa riwayat laporan dan status klaim.
                            Pantau notifikasi untuk mengetahui informasi
                            atau perubahan status yang berkaitan
                            dengan proses pengembalian barang.
                        </p>
                    </article>

                    <article class="step">
                        <div class="step-number">08</div>
                        <h3>Terima Kembali Barang</h3>
                        <p>
                            Setelah klaim diverifikasi dan serah terima
                            disepakati, terima kembali barang milikmu.
                            Status proses diperbarui sesuai perkembangan
                            pengembalian barang.
                        </p>
                    </article>

                </div>

            </div>

        </section>

        <!-- ==================================================
     FOOTER FINDBACK KAMPUS
=================================================== -->
        <footer class="site-footer">
            <div class="container">

                <div class="footer-main">

                    <!-- KOLOM 1: IDENTITAS DAN DESKRIPSI -->
                    <div class="footer-info">

                        <a href="{{ url('/') }}" class="footer-brand">
                            <img src="{{ asset('images/logo-findback.png') }}" alt="Logo FindBack Kampus"
                                class="footer-logo" width="42" height="42">
                            FindBack Kampus
                        </a>

                        <p class="footer-description fb-wrap-anywhere">
                            {{ trim((string) ($fbBrand['site_description'] ?? '')) ?: 'Sistem informasi untuk mendukung pelaporan, pencarian, dan pengelolaan barang hilang dan ditemukan di lingkungan kampus.' }}
                        </p>

                        <p class="footer-description footer-programs">
                            Melayani civitas akademika Program Studi
                            <strong>Sistem Informasi</strong>,
                            <strong>Matematika</strong>, dan
                            <strong>Ilmu Aktuaria</strong>.
                        </p>

                    </div>

                    <!-- KOLOM 2: NAVIGASI -->
                    <nav class="footer-links" aria-label="Navigasi footer">
                        <a href="#beranda">Beranda</a>
                        <a href="#fitur">Fitur</a>
                        <a href="#cara-kerja">Cara Kerja</a>
                        <a href="{{ url('/login') }}">Masuk</a>
                        <a href="{{ url('/register') }}">Daftar</a>
                    </nav>

                    <!-- KOLOM 3: MEDIA SOSIAL -->
                    <div class="footer-social">

                        <h3>Media Sosial</h3>
                        <p>Ikuti informasi dan kegiatan program studi kami.</p>

                        <div class="social-links">

                            <!-- INSTAGRAM SISTEM INFORMASI -->
                            <a href="https://www.instagram.com/fstuinib?srtk=MWZoOGg0NHd0MTQwOQ==" target="_blank"
                                rel="noopener noreferrer">
                                <img src="{{ asset('images/instagram.png') }}" alt="Instagram" class="social-logo">
                                <span>Instagram FST</span>
                            </a>

                            <!-- YOUTUBE FAKULTAS -->
                            <a href="https://www.youtube.com/@fakultassainsdanteknologiu1975" target="_blank"
                                rel="noopener noreferrer">
                                <img src="{{ asset('images/youtube.png') }}" alt="YouTube" class="social-logo">
                                <span>YouTube FST</span>
                            </a>

                            <!-- EMAIL RESMI FST UIN IMAM BONJOL PADANG -->
                            <a href="mailto:lab-publikasi.saintek@uinib.ac.id"
                                aria-label="Kirim email ke Lab Publikasi Saintek">
                                <img src="{{ asset('images/email.png') }}" alt="Email" class="social-logo">
                                <span>Email FST</span>
                            </a>

                        </div>

                    </div>

                </div>

                <!-- COPYRIGHT -->
                <div class="footer-bottom">
                    © {{ date('Y') }} FindBack Kampus —
                    Layanan Barang Hilang dan Ditemukan.
                </div>

            </div>
        </footer>

        <!-- ==================================================
         JAVASCRIPT
         Splash screen dan menu mobile
    =================================================== -->
        <script src="{{ asset('js/pages/welcome.js') }}"></script>

    <link rel="stylesheet" href="{{ asset('css/shared/responsive-all-devices.css') }}?v=20261111">
    <script src="{{ asset('js/shared/responsive-viewport.js') }}?v=20261111" defer></script>
</body>

</html>
