
@extends('admin.layouts.items')

@section('title', 'Pengaturan Sistem')
@section('breadcrumb', 'Pengaturan')

@section('page-styles')
    <link rel="stylesheet" href="{{ asset('css/admin/findback-header-consistency-v2.css') }}?v=2">
    <link rel="stylesheet"
      href="{{ asset('css/admin/admin-settings.css') }}?v=41">
    <link rel="stylesheet" href="{{ asset('css/admin/settings-clean-save-v9.css') }}?v=9">
@endsection

@section('content')


<div class="fb-settings">

    {{-- PUSTAKA IKON SVG --}}
    <svg xmlns="http://www.w3.org/2000/svg"
         aria-hidden="true"
         width="0"
         height="0"
         class="fb-svg-symbols">

        <symbol id="fb-i-settings" viewBox="0 0 24 24">
            <path d="M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8Z"/>
            <path d="M19.4 15a1.7 1.7 0 0 0 .34 1.84l-1.9 1.9A1.7 1.7 0 0 0 16 18.4a1.7 1.7 0 0 0-1.5 1.6h-5A1.7 1.7 0 0 0 8 18.4a1.7 1.7 0 0 0-1.84.34l-1.9-1.9A1.7 1.7 0 0 0 4.6 15 1.7 1.7 0 0 0 3 13.5v-3A1.7 1.7 0 0 0 4.6 9a1.7 1.7 0 0 0-.34-1.84l1.9-1.9A1.7 1.7 0 0 0 8 5.6 1.7 1.7 0 0 0 9.5 4h5A1.7 1.7 0 0 0 16 5.6a1.7 1.7 0 0 0 1.84-.34l1.9 1.9A1.7 1.7 0 0 0 19.4 9a1.7 1.7 0 0 0 1.6 1.5v3a1.7 1.7 0 0 0-1.6 1.5Z"/>
        </symbol>

        <symbol id="fb-i-app" viewBox="0 0 24 24">
            <rect x="3" y="3" width="18" height="18" rx="3"/>
            <path d="M3 9h18"/>
            <path d="M8 6h.01M12 6h.01"/>
            <rect x="7" y="13" width="4" height="4" rx="1"/>
            <path d="M15 13h3M15 17h3"/>
        </symbol>

        <symbol id="fb-i-search" viewBox="0 0 24 24">
            <circle cx="10.5" cy="10.5" r="6.5"/>
            <path d="m16 16 5 5"/>
            <path d="m8 10 2 2 4-4"/>
        </symbol>

        <symbol id="fb-i-bell" viewBox="0 0 24 24">
            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>
            <path d="M10 21h4"/>
            <path d="M19.5 3.5a9 9 0 0 1 2 4"/>
            <path d="M4.5 3.5a9 9 0 0 0-2 4"/>
        </symbol>

        <symbol id="fb-i-report" viewBox="0 0 24 24">
            <path d="M8 3h8l5 5v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h4"/>
            <path d="M16 3v5h5"/>
            <path d="M7 13h10M7 17h7"/>
        </symbol>

        <symbol id="fb-i-claim" viewBox="0 0 24 24">
            <rect x="3" y="7" width="18" height="14" rx="2"/>
            <path d="M8 7V5a4 4 0 0 1 8 0v2"/>
            <path d="m9 14 2 2 4-4"/>
        </symbol>

        <symbol id="fb-i-match" viewBox="0 0 24 24">
            <path d="M8 3H5a2 2 0 0 0-2 2v3"/>
            <path d="M16 3h3a2 2 0 0 1 2 2v3"/>
            <path d="M3 16v3a2 2 0 0 0 2 2h3"/>
            <path d="M21 16v3a2 2 0 0 1-2 2h-3"/>
            <circle cx="11" cy="11" r="4"/>
            <path d="m14 14 4 4"/>
        </symbol>

        <symbol id="fb-i-info" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="9"/>
            <path d="M12 11v5M12 8h.01"/>
        </symbol>

        <symbol id="fb-i-layers" viewBox="0 0 24 24">
            <path d="m12 3 9 5-9 5-9-5 9-5Z"/>
            <path d="m3 12 9 5 9-5"/>
            <path d="m3 16 9 5 9-5"/>
        </symbol>

        <symbol id="fb-i-code" viewBox="0 0 24 24">
            <path d="m8 8-4 4 4 4"/>
            <path d="m16 8 4 4-4 4"/>
            <path d="m14 4-4 16"/>
        </symbol>

        <symbol id="fb-i-database" viewBox="0 0 24 24">
            <ellipse cx="12" cy="5" rx="9" ry="3"/>
            <path d="M3 5v14c0 1.7 4 3 9 3s9-1.3 9-3V5"/>
            <path d="M3 12c0 1.7 4 3 9 3s9-1.3 9-3"/>
        </symbol>

        <symbol id="fb-i-save" viewBox="0 0 24 24">
            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/>
            <path d="M7 3v6h10V4"/>
            <rect x="7" y="14" width="10" height="7" rx="1"/>
        </symbol>

        <symbol id="fb-i-check" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="9"/>
            <path d="m8 12 3 3 5-6"/>
        </symbol>

        <symbol id="fb-i-warning" viewBox="0 0 24 24">
            <path d="m12 3 10 18H2L12 3Z"/>
            <path d="M12 9v5M12 17h.01"/>
        </symbol>

    </svg>

    {{-- HERO --}}
    <div class="fb-settings-hero">
        <div>
            <span class="fb-settings-eyebrow">
                ADMINISTRASI · FINDBACK KAMPUS
            </span>

            <h1>Pengaturan <span>Sistem</span></h1>

            <p>
                Kelola informasi aplikasi dan preferensi sistem
                melalui satu tempat.
            </p>
        </div>

        <span class="fb-settings-hero-icon" aria-hidden="true">
            <svg class="fb-settings-svg">
                <use href="#fb-i-settings"/>
            </svg>
        </span>
    </div>

    <div id="fb-settings-toast" class="fb-settings-toast" data-flash-success="{{ session('success') ? '1' : '0' }}" role="status" aria-live="polite" aria-atomic="true" hidden>
        <span class="fb-settings-toast-icon" aria-hidden="true">✓</span>
        <span class="fb-settings-toast-copy"><strong id="fb-settings-toast-title"></strong><small id="fb-settings-toast-message"></small></span>
        <button type="button" id="fb-settings-toast-close" aria-label="Tutup notifikasi">×</button>
    </div>

    {{-- PESAN ERROR --}}
    @if ($errors->any())
        <div class="fb-settings-alert error" role="alert">
            <svg class="fb-settings-inline-icon" aria-hidden="true">
                <use href="#fb-i-warning"/>
            </svg>

            <div>
                <strong>Periksa kembali data yang dimasukkan:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- FORM --}}
    <form method="POST"
          action="{{ route('admin.settings.update') }}"
          class="fb-settings-form">

        @csrf
        @method('PUT')

        {{-- INFORMASI APLIKASI --}}
        <section class="fb-settings-card"
                 aria-labelledby="general-title">

            <header>
                <span class="fb-settings-section-icon" aria-hidden="true">
                    <svg class="fb-settings-svg">
                        <use href="#fb-i-app"/>
                    </svg>
                </span>

                <div>
                    <h2 id="general-title">Informasi Aplikasi</h2>
                    <p>
                        Identitas yang disimpan dalam database
                        {{ $settings['site_name'] }}.
                    </p>
                </div>
            </header>

            <div class="fb-settings-fields">

                <label class="fb-settings-field">
                    <span>Nama aplikasi <b></b></span>
                    <input type="text"
                           name="site_name"
                           maxlength="100"
                           required
                           value="{{ old('site_name', $settings['site_name']) }}">
                </label>

                <label class="fb-settings-field">
                    <span>Nama institusi <b></b></span>
                    <input type="text"
                           name="institution_name"
                           maxlength="150"
                           required
                           value="{{ old('institution_name', $settings['institution_name']) }}">
                </label>

                <label class="fb-settings-field full">
                    <span>Deskripsi aplikasi</span>
                    <small class="fb-settings-help">
                        Ditampilkan pada halaman utama publik,
                        dashboard pengguna, dan dashboard admin
                        setelah disimpan.
                    </small>

                    <textarea name="site_description"
                              maxlength="500"
                              rows="3">{{ old('site_description', $settings['site_description']) }}</textarea>
                </label>

                <label class="fb-settings-field full">
                    <span>Email kontak (opsional)</span>
                    <input type="email"
                           name="contact_email"
                           maxlength="255"
                           value="{{ old('contact_email', $settings['contact_email']) }}"
                           placeholder="admin@kampus.ac.id">
                </label>

            </div>
        </section>

        {{-- PREFERENSI PENCOCOKAN --}}
        <section class="fb-settings-card"
                 aria-labelledby="matching-title">

            <header>
                <span class="fb-settings-section-icon" aria-hidden="true">
                    <svg class="fb-settings-svg">
                        <use href="#fb-i-search"/>
                    </svg>
                </span>

                <div>
                    <h2 id="matching-title">Preferensi Pencocokan</h2>
                    <p>
                        Atur batas skor kandidat pencocokan
                        barang hilang dan ditemukan.
                    </p>
                </div>
            </header>

            <div class="fb-settings-matching-layout">

                <label class="fb-settings-field compact">
                    <span>
                        Skor minimum kandidat (0–100%)
                        <b></b>
                    </span>

                    <div class="fb-settings-score">
                        <input type="number"
                               name="matching_min_score"
                               min="0"
                               max="100"
                               step="1"
                               required
                               value="{{ old('matching_min_score', $settings['matching_min_score']) }}">

                        <strong>%</strong>
                    </div>
                </label>

                <div class="fb-settings-score-preview">
                    <div class="fb-settings-score-heading">
                        <span>Ambang batas kandidat</span>
                        <strong data-fb-score-value>
                            {{ old('matching_min_score', $settings['matching_min_score']) }}%
                        </strong>
                    </div>

                    <progress
                        class="fb-settings-progress"
                        data-fb-score-progress
                        max="100"
                        value="{{ old('matching_min_score', $settings['matching_min_score']) }}">
                    </progress>

                    <div class="fb-settings-score-scale">
                        <span>0% · Longgar</span>
                        <span>100% · Ketat</span>
                    </div>
                </div>

            </div>

            <div class="fb-settings-note fb-settings-matching-note" role="note" aria-label="Informasi Pencocokan">
                <strong class="fb-settings-matching-note-title">
                    <svg class="fb-settings-svg" aria-hidden="true"><use href="#fb-i-info"/></svg>
                    Informasi Pencocokan
                </strong>
                <p>Nilai ini digunakan oleh modul pencocokan pada proses berikutnya. Hasil pencocokan lama tidak dihitung ulang otomatis.</p>
            </div>

        </section>

        {{-- PREFERENSI NOTIFIKASI --}}
        <section class="fb-settings-card"
                 aria-labelledby="notifications-title">

            <header>
                <span class="fb-settings-section-icon" aria-hidden="true">
                    <svg class="fb-settings-svg">
                        <use href="#fb-i-bell"/>
                    </svg>
                </span>

                <div>
                    <h2 id="notifications-title">Preferensi Notifikasi</h2>
                    <p>
                        Atur pemberitahuan admin untuk laporan,
                        klaim, dan kandidat hasil pencocokan.
                    </p>
                </div>
            </header>

            @foreach ([
                'notification_reports' => [
                    'Laporan Barang',
                    'Pemberitahuan laporan hilang dan ditemukan',
                    'fb-i-report'
                ],
                'notification_claims' => [
                    'Klaim Barang',
                    'Pemberitahuan terkait pengajuan klaim',
                    'fb-i-claim'
                ],
                'notification_matching' => [
                    'Pencocokan Barang',
                    'Pemberitahuan kandidat hasil pencocokan',
                    'fb-i-match'
                ],
            ] as $key => $info)

                @php
                    $enabled = (string) old($key, $settings[$key]) === '1';
                @endphp

                <div class="fb-settings-toggle-row">

                    <div class="fb-settings-notification-info">

                        <span class="fb-settings-row-icon" aria-hidden="true">
                            <svg class="fb-settings-svg">
                                <use href="#{{ $info[2] }}"/>
                            </svg>
                        </span>

                        <div>
                            <strong>{{ $info[0] }}</strong>
                            <small>{{ $info[1] }}</small>
                        </div>

                    </div>

                    <select name="{{ $key }}"
                            aria-label="{{ $info[0] }}">

                        <option value="1" @selected($enabled)>
                            Aktif
                        </option>

                        <option value="0" @selected(!$enabled)>
                            Nonaktif
                        </option>

                    </select>

                </div>

            @endforeach

            <div class="fb-settings-note fb-settings-notification-note" role="note" aria-label="Informasi Notifikasi">
                <strong class="fb-settings-notification-note-title">
                    <svg class="fb-settings-svg" aria-hidden="true"><use href="#fb-i-info"/></svg>
                    Informasi Notifikasi
                </strong>
                <p>Pengaturan ini mengatur pemberitahuan yang diterima administrator terkait laporan, klaim, dan pencocokan barang. Menonaktifkan pemberitahuan tidak menghentikan proses layanan maupun pengiriman pesan keputusan kepada pengguna.</p>
            </div>

        </section>

        {{-- TOMBOL SIMPAN --}}
        <div class="fb-settings-footer-actions">

            <button type="submit" class="fb-settings-button">
                <svg class="fb-settings-inline-icon" aria-hidden="true">
                    <use href="#fb-i-save"/>
                </svg>
                <span>Simpan Pengaturan</span>
            </button>

        </div>

    </form>

    <link rel="stylesheet" href="{{ asset('css/admin/settings-notifications-v6.css') }}?v=6">
    <link rel="stylesheet" href="{{ asset('css/admin/settings-matching-v7.css') }}?v=7">
    <link rel="stylesheet" href="{{ asset('css/admin/settings-smart-save-v5.css') }}?v=5">
    <script src="{{ asset('js/admin/settings-smart-save-v5.js') }}?v=5" defer></script>

    {{-- INFORMASI SISTEM --}}
    <section class="fb-settings-card fb-settings-system"
             aria-labelledby="system-title">

        <header>
            <span class="fb-settings-section-icon" aria-hidden="true">
                <svg class="fb-settings-svg">
                    <use href="#fb-i-info"/>
                </svg>
            </span>

            <div>
                <h2 id="system-title">Informasi Sistem</h2>
                <p>
                    Informasi dasar lingkungan aplikasi
                    tanpa menampilkan data rahasia.
                </p>
            </div>
        </header>

        <div class="fb-settings-system-grid">

            <div>
                <span class="fb-settings-system-icon" aria-hidden="true">
                    <svg class="fb-settings-svg">
                        <use href="#fb-i-layers"/>
                    </svg>
                </span>
                <small>Framework</small>
                <strong>Laravel {{ $system['laravel'] }}</strong>
            </div>

            <div>
                <span class="fb-settings-system-icon" aria-hidden="true">
                    <svg class="fb-settings-svg">
                        <use href="#fb-i-code"/>
                    </svg>
                </span>
                <small>Versi PHP</small>
                <strong>{{ $system['php'] }}</strong>
            </div>

            <div>
                <span class="fb-settings-system-icon" aria-hidden="true">
                    <svg class="fb-settings-svg">
                        <use href="#fb-i-database"/>
                    </svg>
                </span>
                <small>Penyimpanan pengaturan</small>
                <strong>Tabel app_settings</strong>
            </div>

        </div>

    </section>

</div>

@endsection
