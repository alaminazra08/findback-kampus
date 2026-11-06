# Audit refactoring — sumber 20261103-032904

Tujuan: pemisahan tanggung jawab dan keterbacaan tanpa mengubah kontrak route, desain, hasil validasi, transaksi, status, ataupun database.

Inventaris awal: 54 berkas aplikasi, 54 berkas dalam resources/views (termasuk 2 backup), 54 stylesheet, 6 JavaScript, 15 berkas tes. Ditemukan 10 blok style, 20 blok script (termasuk data JSON), 47 atribut style, dan 19 event handler inline.

## Prioritas dan dependensi

1. Simpan sumber unggahan utuh sebagai pembanding. Jangan gunakan payload audit dari versi terdahulu.
2. Pisahkan arsip yang berada di resources/views dari source aktif setelah pencarian referensi. Folder backup asli tidak diubah atau ditimpa.
3. Ekstrak CSS dan script sesuai halaman, pertahankan urutan pemuatan agar cascade dan waktu eksekusi tetap sama.
4. Pertahankan data JSON dari server di Blade; script eksekusi membaca data melalui DOM/data attribute.
5. Satukan handler konfirmasi, fallback gambar, dan navigasi yang benar-benar identik.
6. Pisahkan partial shell admin yang diduplikasi di dashboard dan layout.
7. Pisahkan penyimpanan laporan hilang/ditemukan yang identik ke satu service dan satu Form Request. Pindahkan algoritme skor pencocokan ke service; pertahankan rumus.
8. Format PHP menggunakan Laravel Pint/PSR-12 dan format Blade/CSS/JS dengan formatter khusus. Vendor, migration historis, file upload, dan backup asli tidak diformat.
9. Bandingkan hasil tes dan tampilan sebelum/sesudah; kegagalan awal dicatat, bukan disembunyikan.

## Temuan keamanan di luar perubahan perilaku

Source menyimpan bukti klaim/lampiran chat pada disk public. Perbaikan privasi membutuhkan migrasi file dan perubahan kontrak URL, sehingga dicatat sebagai pekerjaan terpisah. Refactoring ini tidak mengklaim risiko tersebut selesai.

## Baseline pengujian

PHPUnit pada SQLite terisolasi: 72 tes, 476 assertion, 9 error dan 8 failure sebelum refactoring. Detail dan perbandingan akhir dilampirkan. Mayoritas perbedaan ekspektasi menyangkut alur revisi lama, akses chat, serta daftar/ekspor laporan aktif. Tidak mengubah alur bisnis demi membuat tes lama hijau.


## Berkas diperiksa dan hasil tahap 1

Daftar lengkap ada di inventaris-source-awal.json dan dependensi-awal.json; pemetaan view/aset akhir ada di asset-dan-view-map.json. Source pihak ketiga dalam vendor tidak direfactor.

- File diperbaiki/dipindahkan/dihapus pada tahap audit: tidak ada. Perubahan dicatat terpisah pada tahap 2–7.
- Tes baseline benar-benar dijalankan pada SQLite in-memory; 72 tes, 476 assertion, 9 error dan 8 failure.
- Masalah tersisa: 17 kasus baseline dan temuan keamanan yang dijabarkan pada tahap 6.
