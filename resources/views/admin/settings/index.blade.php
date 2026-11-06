@extends('admin.layouts.items')

@section('title', 'Pengaturan Sistem')
@section('breadcrumb', 'Pengaturan')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/admin/admin-settings.css') }}?v=35">
    <div class="fb-settings">
        <div class="fb-settings-hero">
            <div>
                <span class="fb-settings-eyebrow">FINDBACK KAMPUS · ADMINISTRASI</span>
                <h1>Pengaturan <span>Sistem</span></h1>
                <p>Kelola informasi aplikasi dan preferensi sistem melalui satu tempat.</p>
            </div>
            <span class="fb-settings-hero-icon" aria-hidden="true">⚙</span>
        </div>

        @if (session('success'))
            <div class="fb-settings-alert success" role="status">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="fb-settings-alert error" role="alert">
                <strong>Periksa kembali data yang dimasukkan:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.settings.update') }}" class="fb-settings-form">
            @csrf
            @method('PUT')
            <section class="fb-settings-card" aria-labelledby="general-title">
                <header><span class="fb-settings-section-icon">✦</span>
                    <div>
                        <h2 id="general-title">Informasi Aplikasi</h2>
                        <p>Identitas yang disimpan dalam database FindBack Kampus.</p>
                    </div>
                </header>
                <div class="fb-settings-fields">
                    <label class="fb-settings-field"><span>Nama aplikasi <b>*</b></span>
                        <input name="site_name" maxlength="100" required
                            value="{{ old('site_name', $settings['site_name']) }}">
                    </label>
                    <label class="fb-settings-field"><span>Nama institusi <b>*</b></span>
                        <input name="institution_name" maxlength="150" required
                            value="{{ old('institution_name', $settings['institution_name']) }}">
                    </label>
                    <label class="fb-settings-field full"><span>Deskripsi aplikasi</span>
                        <textarea name="site_description" maxlength="500" rows="3">{{ old('site_description', $settings['site_description']) }}</textarea>
                    </label>
                    <label class="fb-settings-field full"><span>Email kontak (opsional)</span>
                        <input type="email" name="contact_email" maxlength="255"
                            value="{{ old('contact_email', $settings['contact_email']) }}" placeholder="admin@kampus.ac.id">
                    </label>
                </div>
            </section>

            <section class="fb-settings-card" aria-labelledby="matching-title">
                <header><span class="fb-settings-section-icon">⌕</span>
                    <div>
                        <h2 id="matching-title">Preferensi Pencocokan</h2>
                        <p>Nilai yang tersedia untuk dibaca modul Pencocokan.</p>
                    </div>
                </header>
                <label class="fb-settings-field compact"><span>Skor minimum kandidat (0–100%) <b>*</b></span>
                    <div class="fb-settings-score"><input type="number" name="matching_min_score" min="0"
                            max="100" step="1" required
                            value="{{ old('matching_min_score', $settings['matching_min_score']) }}"><strong>%</strong>
                    </div>
                </label>
                <p class="fb-settings-note">Perubahan angka ini baru memengaruhi pencocokan otomatis setelah
                    <code>MatchingController</code> membaca nilai <code>matching_min_score</code> dari tabel
                    <code>app_settings</code>. Saat ini pengaturan disimpan, bukan otomatis diterapkan.
                </p>
            </section>

            <section class="fb-settings-card" aria-labelledby="notifications-title">
                <header><span class="fb-settings-section-icon">♢</span>
                    <div>
                        <h2 id="notifications-title">Preferensi Notifikasi</h2>
                        <p>Berlaku untuk notifikasi admin atas laporan, klaim, dan hasil pencocokan baru. Pesan keputusan
                            kepada pelapor tetap dikirim.</p>
                    </div>
                </header>
                @foreach ([
            'notification_reports' => ['Laporan Barang', 'Pemberitahuan laporan hilang dan ditemukan'],
            'notification_claims' => ['Klaim Barang', 'Pemberitahuan terkait pengajuan klaim'],
            'notification_matching' => ['Pencocokan Barang', 'Pemberitahuan kandidat hasil pencocokan'],
        ] as $key => $info)
                    @php $enabled = (string) old($key, $settings[$key]) === '1'; @endphp
                    <div class="fb-settings-toggle-row">
                        <div><strong>{{ $info[0] }}</strong><small>{{ $info[1] }}</small></div>
                        <select name="{{ $key }}" aria-label="{{ $info[0] }}">
                            <option value="1" @selected($enabled)>Aktif</option>
                            <option value="0" @selected(!$enabled)>Nonaktif</option>
                        </select>
                    </div>
                @endforeach
                <p class="fb-settings-note">Preferensi ini tersimpan di database. Pengiriman notifikasi yang sebenarnya
                    harus dihubungkan secara terpisah agar mematuhi pengaturan.</p>
            </section>

            <div class="fb-settings-footer-actions">
                <span>Perubahan hanya berlaku setelah menekan Simpan.</span>
                <button type="submit" class="fb-settings-button">Simpan Pengaturan</button>
            </div>
        </form>

        <section class="fb-settings-card fb-settings-system" aria-labelledby="system-title">
            <header><span class="fb-settings-section-icon">ⓘ</span>
                <div>
                    <h2 id="system-title">Informasi Sistem</h2>
                    <p>Informasi dasar lingkungan aplikasi tanpa data rahasia.</p>
                </div>
            </header>
            <div class="fb-settings-system-grid">
                <div><small>Framework</small><strong>Laravel {{ $system['laravel'] }}</strong></div>
                <div><small>Versi PHP</small><strong>{{ $system['php'] }}</strong></div>
                <div><small>Status pengaturan</small><strong>Database app_settings</strong></div>
            </div>
        </section>
    </div>
@endsection
