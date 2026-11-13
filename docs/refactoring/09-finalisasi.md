# Finalisasi tambahan refactoring FindBack Kampus

Tanggal: 13 November 2026

## Ruang lingkup patch

Patch finalisasi ini ditujukan untuk branch `refactor/cleanup-project-structure` setelah paket refactoring tahap sebelumnya terpasang. Patch tidak mengubah route, controller, model, migration, skema database, validasi, status workflow, atau hak akses.

1. Mengarsipkan `public/js/admin/admin-items-v21.js`. Pencarian statis pada `app`, `routes`, `resources`, dan aset runtime tidak menemukan referensi yang memuat file tersebut. Script ini tidak dimuat oleh layout atau halaman aktif.
2. Menghapus 51 selector CSS `.fb-v21-*` yang hanya mendukung script tersebut dan tidak direferensikan markup atau JavaScript aktif. 41 rule khusus dihapus dan satu daftar selector campuran dipertahankan hanya untuk selector non-v21. Aturan native select `.fb-v20-*` tetap dipertahankan.
3. Menghapus endpoint diagnostik `public/cek-php.php` jika hash-nya sama dengan file pemeriksaan versi PHP yang dibuat sementara. Endpoint itu hanya menampilkan versi PHP dan tidak boleh tertinggal di `public`.
4. Mengarsipkan file terminal `an routelist` bila file tersebut ada dan hash-nya cocok dengan artefak `less` help yang diaudit. File ini bukan source aplikasi.

## Pemeriksaan paket

- CSS diparse ulang menggunakan PostCSS setelah perubahan.
- Tidak ada selector `.fb-v21-*` yang tersisa pada stylesheet `admin-items.css`.
- Referensi ke `admin-items-v21.js` tidak ditemukan pada source runtime.
- File JavaScript lama dipertahankan di `archive/legacy-assets/js/admin/` dan tidak lagi disajikan dari `public`.
- Tidak ada perubahan database atau operasi migration.
- Installer menggunakan PHP CLI `C:\php83\php.exe`, memeriksa PHP minimal 8.2, branch Git, hash source, backup, route list, dan kompilasi Blade.

## Batasan yang belum dinyatakan selesai

Hal-hal berikut tidak diubah otomatis karena membutuhkan keputusan alur bisnis, migrasi file, atau pengujian langsung pada lingkungan pengguna:

1. **17 masalah baseline PHPUnit** yang dicatat dalam `07-pengujian.md` (9 error dan 8 failure). Perubahan ekspektasi tes tanpa persetujuan pemilik proses revisi/chat/laporan aktif berisiko mengubah bisnis.
2. **Privasi bukti klaim dan lampiran chat.** Source yang diaudit masih menggunakan disk `public`. Memindahkan file lama ke penyimpanan privat memerlukan migrasi file, endpoint unduh dengan otorisasi, dan strategi kompatibilitas URL.
3. **Overflow 320 px** pada halaman barang (sekitar 9 px dalam hasil audit) serta beberapa kondisi zoom 200%. Perbaikannya harus divalidasi pada browser dengan data halaman nyata agar tabel, dialog, dan menu tidak terpotong.
4. **Apache XAMPP.** Komputer pengguna masih melaporkan PHP Apache 8.0.30, sedangkan CLI PHP 8.3.36. Patch ini hanya menggunakan PHP CLI dan tidak mengganti konfigurasi Apache. Aplikasi dapat diuji melalui `php artisan serve`; penggunaan Apache memerlukan runtime PHP yang kompatibel (TS/Apache module atau FastCGI).
5. Pengujian native MySQL concurrency, browser zoom 200%, serta perangkat Android/iOS belum dapat disertifikasi oleh paket ini.

## Prosedur verifikasi setelah pemasangan

Installer menjalankan `php artisan optimize:clear`, `php artisan route:list`, `php artisan view:cache`, lalu `php artisan view:clear`. Installer tidak menjalankan PHPUnit penuh karena baseline yang tercatat memiliki 17 kegagalan/error yang belum disepakati sebagai perubahan kontrak.

Hasil pemasangan hanya membuktikan patch terpasang, route dapat dimuat, dan Blade dapat dikompilasi pada PHP CLI yang dipilih. Pengujian visual dan fungsional Admin/User tetap harus dicatat sebagai QA terpisah.
