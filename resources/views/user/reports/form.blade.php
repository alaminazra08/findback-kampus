{{-- FindBack Kampus - Formulir Pelaporan Barang --}}

<style>
    /* Barang hilang = marun */
    .cu-form-wrap {
        --main: #8b0a33;
        --dark: #6d0726;
        --soft: #fbe6ec;
        --line: #dcb4c1;
        --glow: rgba(139, 10, 51, 0.16);
        --title: #3b0a1a;
    }
    /* Barang ditemukan = HIJAU terang */
    .cu-form-wrap.found {
        --main: #16a34a;
        --dark: #15803d;
        --soft: #d5f5e0;
        --line: #a6d8b6;
        --glow: rgba(22, 163, 74, 0.18);
        --title: #14401f;
    }

    /* Hilangkan biru bawaan browser */
    .cu-form-wrap { accent-color: var(--main); }
    .cu-form-wrap input,
    .cu-form-wrap textarea { caret-color: var(--main); }
    .cu-form-wrap *:focus-visible { outline: 2px solid var(--main) !important; outline-offset: 2px; }
    .cu-form-wrap ::selection { background: var(--soft); color: var(--dark); }

    .cu-form-wrap .cu-svg {
        width: 20px; height: 20px; flex: none;
        fill: none; stroke: currentColor; stroke-width: 1.8;
        stroke-linecap: round; stroke-linejoin: round;
    }
    .cu-form-wrap .req { color: #e11d1d !important; font-weight: 700; }

    /* Judul halaman */
    .cu-form-wrap .cu-title h1 { color: var(--title) !important; }
    .cu-form-wrap .cu-round,
    .cu-form-wrap .cu-round.green,
    .cu-form-wrap .cu-round.red { background: var(--main) !important; color: #fff !important; }

    /* Kartu putih */
    .cu-form-wrap form.cu-report-form,
    .cu-form-wrap .cu-panel {
        background: #ffffff !important;
        border: 1px solid #e5e5e5 !important;
        border-radius: 22px !important;
        box-shadow: 0 8px 28px rgba(0, 0, 0, 0.06) !important;
    }

    /* Langkah 1-2-3 */
    .cu-form-wrap .cu-steps li { color: #222 !important; }
    .cu-form-wrap .cu-steps li span { background: var(--soft) !important; color: var(--main) !important; }
    .cu-form-wrap .cu-steps .active {
        color: var(--main) !important;
        background: transparent !important;
        background-color: transparent !important;
        background-image: none !important;
        box-shadow: none !important;
    }
    .cu-form-wrap .cu-steps .active span { background: var(--main) !important; color: #fff !important; }
    .cu-form-wrap .cu-steps li::before,
    .cu-form-wrap .cu-steps li::after { border-color: var(--main) !important; background-color: var(--main) !important; }

    /* Judul bagian */
    .cu-form-wrap .cu-sechead {
        display: flex; align-items: center; gap: 14px;
        background: var(--soft);
        border-left: 5px solid var(--main);
        border-radius: 12px;
        padding: 14px 18px;
        margin-bottom: 20px;
    }
    .cu-form-wrap .cu-sechead .cu-chip {
        width: 46px; height: 46px; border-radius: 12px;
        background: #fff; color: var(--main);
        display: grid; place-items: center;
    }
    .cu-form-wrap .cu-sechead h2 { margin: 0 !important; color: var(--main) !important; font-size: 20px; }
    .cu-form-wrap .cu-sechead p { margin: 2px 0 0 !important; color: #444 !important; font-size: 14px; }

    /* Label & kotak isian putih dengan ikon */
    .cu-form-wrap .cu-fields label { color: #1f1f1f !important; font-weight: 700; }
    .cu-form-wrap .cu-ic { position: relative; display: block; margin-top: 8px; font-weight: 400; }
    .cu-form-wrap .cu-ic > .cu-svg {
        position: absolute; left: 14px; top: 15px;
        color: var(--main); pointer-events: none;
    }
    .cu-form-wrap .cu-fields input,
    .cu-form-wrap .cu-fields textarea {
        width: 100%;
        background: #ffffff !important;
        background-color: #ffffff !important;
        border: 1.5px solid var(--line) !important;
        border-radius: 14px !important;
        color: #222 !important;
        padding: 13px 14px 13px 46px !important;
        box-shadow: none !important;
    }
    .cu-form-wrap .cu-fields input::placeholder,
    .cu-form-wrap .cu-fields textarea::placeholder { color: #8a8f98 !important; }
    .cu-form-wrap .cu-fields input:focus,
    .cu-form-wrap .cu-fields textarea:focus {
        outline: none !important;
        border-color: var(--main) !important;
        box-shadow: 0 0 0 4px var(--glow) !important;
    }

    /* Upload foto */
    .cu-form-wrap .cu-upload {
        background: #fff !important;
        border: 2px dashed var(--line) !important;
        color: var(--main) !important;
    }
    .cu-form-wrap .cu-upload strong { color: var(--main) !important; }
    .cu-form-wrap .cu-upload:hover { background: var(--soft) !important; border-color: var(--main) !important; }

    /* Tombol */
    .cu-form-wrap .cu-btn:not(.light),
    .cu-form-wrap .cu-btn.green:not(.light) {
        background: var(--main) !important;
        background-image: none !important;
        border-color: var(--main) !important;
        color: #fff !important;
        border-radius: 14px !important;
        box-shadow: 0 3px 10px var(--glow) !important;
    }
    .cu-form-wrap .cu-btn:not(.light):hover { background: var(--dark) !important; }
    .cu-form-wrap .cu-btn.light {
        background: #fff !important;
        border: 1.5px solid var(--line) !important;
        color: var(--main) !important;
        border-radius: 14px !important;
        font-weight: 700;
    }

    /* Kolom kosong = merah */
    .cu-form-wrap .cu-fields input.is-invalid,
    .cu-form-wrap .cu-fields textarea.is-invalid {
        border: 1.5px solid #d92d20 !important;
        background: #fff5f5 !important;
        box-shadow: 0 0 0 3px rgba(217, 45, 32, 0.12) !important;
    }
    .cu-form-wrap .cu-upload.is-invalid { border-color: #d92d20 !important; background: #fff5f5 !important; }
    .cu-field-error { display: block; margin-top: 4px; color: #d92d20; font-size: 12px; font-weight: 500; }

    /* ===== HIJAU TERANG: khusus laporan ditemukan (paksa menimpa CSS lain) ===== */

    /* Langkah 1-2-3: yang aktif (Isi informasi) */
    .cu-form-wrap.found .cu-steps li.active,
    .cu-form-wrap.found .cu-steps .active {
        background: transparent !important;
        background-color: transparent !important;
        background-image: none !important;
        box-shadow: none !important;
        color: #15803d !important;
    }
    .cu-form-wrap.found .cu-steps li.active span {
        background: #16a34a !important;
        color: #fff !important;
    }
    /* Nomor langkah yang belum aktif */
    .cu-form-wrap.found .cu-steps li:not(.active) span {
        background: #e4f5e9 !important;
        color: #16a34a !important;
    }

    /* Kotak "Informasi Barang" */
    .cu-form-wrap.found .cu-sechead {
        background: #d5f5e0 !important;
        background-color: #d5f5e0 !important;
        background-image: none !important;
        border-left: 5px solid #16a34a !important;
    }
    .cu-form-wrap.found .cu-sechead .cu-chip {
        background: #ffffff !important;
        color: #16a34a !important;
    }
    .cu-form-wrap.found .cu-sechead h2 {
        color: #15803d !important;
    }
    .cu-form-wrap.found .cu-sechead p,
    .cu-form-wrap.found .cu-sechead .cu-muted {
        color: #3f5f4a !important;
    }
</style>

<div class="cu-form-wrap {{ $kind === 'found' ? 'found' : '' }}">
    <header class="cu-title cu-title-icon"><span
            class="cu-round {{ $kind === 'found' ? 'green' : 'red' }}">@include('user.components.icon', ['icon' => $kind === 'found' ? 'box' : 'search'])</span>
        <div>
            <h1>{{ $heading }}</h1>
            <p>{{ $kind === 'found' ? 'Bantu barang yang kamu temukan kembali ke pemiliknya.' : 'Ceritakan barang yang hilang di lingkungan Fakultas Sains dan Teknologi.' }}
            </p>
        </div>
    </header>
    <form class="cu-panel cu-report-form" data-wizard method="POST" enctype="multipart/form-data"
        action="{{ route('user.reports.' . $kind . '.store') }}">@csrf
        @if ($errors->any())
            <div class="cu-note" role="alert">
                <strong>Periksa kembali isian laporanmu</strong>
                <ul>
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <ol class="cu-steps" aria-label="Tahap pelaporan">
            <li class="active"><span>1</span>Isi informasi</li>
            <li><span>2</span>Upload foto</li>
            <li><span>3</span>Periksa & kirim</li>
        </ol>

        <section data-step="0">
            <div class="cu-sechead">
                <span class="cu-chip"><svg class="cu-svg" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h8"/></svg></span>
                <div>
                    <h2>Informasi Barang</h2>
                    <p class="cu-muted">Kolom bertanda <span class="req">*</span> wajib diisi.</p>
                </div>
            </div>
            <div class="cu-fields">
                <label>Nama barang <span class="req">*</span>
                    <span class="cu-ic"><svg class="cu-svg" viewBox="0 0 24 24"><path d="M20.6 13.4l-7.2 7.2a2 2 0 01-2.8 0L3 13V3h10l7.6 7.6a2 2 0 010 2.8z"/><circle cx="7.5" cy="7.5" r="1.5"/></svg>
                    <input name="title" required maxlength="255" value="{{ old('title') }}"
                        placeholder="Contoh: Dompet kulit cokelat merek Eiger"></span>
                </label>
                <label class="full">Deskripsi barang <span class="req">*</span>
                    <span class="cu-ic"><svg class="cu-svg" viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z"/></svg>
                    <textarea name="description" required maxlength="3000" rows="4"
                        placeholder="Tulis warna, merek, ukuran, dan ciri khas (stiker, goresan, isi barang)...">{{ old('description') }}</textarea></span>
                </label>
                <label>Lokasi {{ $kind === 'found' ? 'ditemukan' : 'terakhir' }} <span class="req">*</span>
                    <span class="cu-ic"><svg class="cu-svg" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 1118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <input name="location" required maxlength="255" value="{{ old('location') }}"
                        placeholder="Contoh: Lab Komputer lantai 2, dekat tangga"></span>
                </label>
                <label>Tanggal {{ $kind === 'found' ? 'ditemukan' : 'hilang' }} <span class="req">*</span>
                    <span class="cu-ic"><svg class="cu-svg" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                    <input type="date" name="event_date" max="{{ now()->format('Y-m-d') }}" required
                        value="{{ old('event_date') }}"></span>
                </label>
            </div>
        </section>

        <section data-step="1">
            <div class="cu-sechead">
                <span class="cu-chip"><svg class="cu-svg" viewBox="0 0 24 24"><path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z"/><circle cx="12" cy="13" r="4"/></svg></span>
                <div>
                    <h2>Foto Barang</h2>
                    <p class="cu-muted">Gunakan foto yang jelas dan tidak buram. JPG, PNG, atau WEBP, maksimal 5 MB.</p>
                </div>
            </div>
            <label class="cu-upload">@include('user.components.icon', ['icon' => 'upload'])<strong>Pilih foto barang <span class="req">*</span></strong><span>Foto membantu
                    admin memeriksa laporanmu.</span><input type="file" name="photo"
                    accept="image/jpeg,image/png,image/webp" required data-preview data-max="5242880"></label><img
                class="cu-preview" alt="Pratinjau foto barang" hidden>
            <p class="cu-file-info" role="status"></p>
        </section>

        <section data-step="2">
            <div class="cu-sechead">
                <span class="cu-chip"><svg class="cu-svg" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg></span>
                <div>
                    <h2>Periksa Laporanmu</h2>
                    <p class="cu-muted">Pastikan semua informasi sudah benar sebelum dikirim.</p>
                </div>
            </div>
            <div class="cu-review" data-review></div>
            <div class="cu-note"><strong>Menunggu verifikasi admin</strong>
                <p>Laporan belum langsung dipublikasikan. Admin akan memeriksa informasi dan foto terlebih dahulu.</p>
            </div><label class="cu-check"><input type="checkbox" name="confirmation" value="1" required> Saya
                memastikan informasi laporan ini benar.</label>
        </section>

        <div class="cu-actions"><a class="cu-btn light"
                href="{{ route('user.reports.' . $kind . '.create') }}">Batal</a><button type="button"
                class="cu-btn light" data-prev hidden>Kembali</button><button type="button"
                class="cu-btn {{ $kind === 'found' ? 'green' : '' }}" data-next hidden>Lanjutkan →</button><button
                type="submit" class="cu-btn {{ $kind === 'found' ? 'green' : '' }}" data-submit>Kirim laporan
                →</button>
        </div>
    </form>
</div>