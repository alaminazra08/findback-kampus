{{-- FindBack Kampus - Formulir Pelaporan Barang --}}
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
        <ol class="cu-steps" aria-label="Tahap pelaporan">
            <li class="active"><span>1</span>Isi informasi</li>
            <li><span>2</span>Upload foto</li>
            <li><span>3</span>Periksa & kirim</li>
        </ol>
        <section data-step="0">
            <h2>Informasi barang</h2>
            <p class="cu-muted">Kolom bertanda * wajib diisi.</p>
            <div class="cu-fields"><label>Nama barang *<input name="title" required maxlength="255"
                        value="{{ old('title') }}" placeholder="Contoh: Dompet kulit cokelat"></label><label
                    class="full">Deskripsi barang *
                    <textarea name="description" required maxlength="3000" rows="4"
                        placeholder="Warna, merek, kondisi, dan ciri khas barang...">{{ old('description') }}</textarea>
                </label><label>Lokasi {{ $kind === 'found' ? 'ditemukan' : 'terakhir' }} *<input name="location"
                        required maxlength="255" value="{{ old('location') }}"
                        placeholder="Contoh: Lab Komputer, lantai 2"></label><label>Tanggal
                    {{ $kind === 'found' ? 'ditemukan' : 'hilang' }} *<input type="date" name="event_date"
                        max="{{ now()->format('Y-m-d') }}" required value="{{ old('event_date') }}"></label></div>
        </section>
        <section data-step="1">
            <h2>Foto barang</h2>
            <p class="cu-muted">Gunakan foto yang jelas. JPG, PNG, atau WEBP, maksimal 5 MB.</p><label
                class="cu-upload">@include('user.components.icon', ['icon' => 'upload'])<strong>Pilih foto barang *</strong><span>Foto membantu
                    admin memeriksa laporanmu.</span><input type="file" name="photo"
                    accept="image/jpeg,image/png,image/webp" required data-preview data-max="5242880"></label><img
                class="cu-preview" alt="Pratinjau foto barang" hidden>
            <p class="cu-file-info" role="status"></p>
        </section>
        <section data-step="2">
            <h2>Periksa laporanmu</h2>
            <div class="cu-review" data-review></div>
            <div class="cu-note"><strong>Menunggu verifikasi admin</strong>
                <p>Laporan belum langsung dipublikasikan. Admin akan memeriksa informasi dan foto terlebih dahulu.</p>
            </div><label class="cu-check"><input type="checkbox" name="confirmation" value="1" required> Saya
                memastikan informasi laporan ini benar.</label>
        </section>
        <div class="cu-actions"><a class="cu-btn light" href="{{ route('dashboard') }}">Batal</a><button type="button"
                class="cu-btn light" data-prev hidden>Kembali</button><button type="button"
                class="cu-btn {{ $kind === 'found' ? 'green' : '' }}" data-next hidden>Lanjutkan →</button><button
                type="submit" class="cu-btn {{ $kind === 'found' ? 'green' : '' }}" data-submit>Kirim laporan
                →</button>
        </div>
    </form>
</div>
