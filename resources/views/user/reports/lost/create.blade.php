<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#750d34">

    <title>Laporkan Barang Hilang | FindBack Kampus</title>

    <link rel="icon" href="{{ asset('favicon.png') }}">

    <link rel="stylesheet" href="{{ asset('css/user/user-dashboard.css') }}?v=3">

    <link rel="stylesheet" href="{{ asset('css/user/user-report.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/user/premium.css') }}?v=1">
    <script src="{{ asset('js/user/premium.js') }}?v=1" defer></script>
    <link rel="stylesheet" href="{{ asset('css/shared/view-utilities.css') }}">
    <script src="{{ asset('js/shared/view-interactions.js') }}"></script>
</head>

<body class="fb-user-body fp-body fp-existing">

    @include('user.partials.premium-shell')

    <main id="fp-content" class="fb-user-main">

        <div class="fb-user-breadcrumb">
            Beranda <span>/</span>
            <strong>Laporkan Barang Hilang</strong>
        </div>

        <section class="fb-report-hero">
            <span class="fb-report-kicker">✦ FINDBACK KAMPUS</span>

            <h1>Laporkan Barang Hilang</h1>

            <p>
                Isi informasi barang yang hilang dengan lengkap dan benar.
                Laporan akan diperiksa oleh Admin sebelum ditampilkan
                kepada pengguna lainnya.
            </p>
        </section>

        <section class="fb-report-panel">

            <div class="fb-report-heading">
                <div>
                    <span>FORMULIR PELAPORAN</span>
                    <h2>Informasi Barang</h2>
                    <p>Lengkapi seluruh informasi berikut.</p>
                </div>

                <span class="fb-report-required">* Wajib diisi</span>
            </div>

            @if ($errors->any())
                <div class="fb-report-alert">
                    <strong>Periksa kembali formulir kamu.</strong>

                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('user.reports.lost.store') }}" method="POST" enctype="multipart/form-data">

                @csrf

                <div class="fb-report-grid">

                    <div class="fb-report-field">
                        <label for="title">Nama Barang <span>*</span></label>

                        <input type="text" id="title" name="title" value="{{ old('title') }}"
                            placeholder="Contoh: Dompet hitam" maxlength="255" required>
                    </div>

                    <div class="fb-report-field">
                        <label for="location">Lokasi Kehilangan <span>*</span></label>

                        <input type="text" id="location" name="location" value="{{ old('location') }}"
                            placeholder="Contoh: Gedung Fakultas Sains" maxlength="255" required>
                    </div>

                    <div class="fb-report-field">
                        <label for="event_date">Tanggal Kehilangan <span>*</span></label>

                        <input type="date" id="event_date" name="event_date" value="{{ old('event_date') }}"
                            max="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="fb-report-field fb-report-full">
                        <label for="description">Deskripsi Barang <span>*</span></label>

                        <textarea id="description" name="description" rows="5" maxlength="3000"
                            placeholder="Jelaskan ciri-ciri barang, warna, merek, dan informasi lainnya..." required>{{ old('description') }}</textarea>
                    </div>

                    <div class="fb-report-field fb-report-full">
                        <label for="photo">Foto Barang</label>

                        <div class="fb-report-upload">
                            <input type="file" id="photo" name="photo" accept=".jpg,.jpeg,.png,.webp,image/*">

                            <small>
                                Format JPG, JPEG, PNG, atau WEBP.
                                Maksimal 5 MB.
                            </small>
                        </div>
                    </div>

                </div>

                <div class="fb-report-notice">
                    <strong>Informasi penting</strong>

                    <p>
                        Pastikan informasi yang diberikan benar.
                        Setelah dikirim, laporan akan berstatus
                        menunggu verifikasi Admin.
                    </p>
                </div>

                <div class="fb-report-actions">

                    <a href="{{ route('dashboard') }}" class="fb-report-cancel">
                        Batal
                    </a>

                    <button type="submit" class="fb-report-submit">
                        Kirim Laporan →
                    </button>

                </div>

            </form>

        </section>

        <footer class="fb-user-footer">
            © {{ date('Y') }} FindBack Kampus ·
            Fakultas Sains dan Teknologi ·
            UIN Imam Bonjol Padang
        </footer>

    </main>

</body>

</html>
