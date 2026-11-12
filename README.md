# FindBack Kampus

Aplikasi Laravel 12 untuk pelaporan barang hilang/ditemukan Fakultas Sains dan Teknologi UIN Imam Bonjol Padang. Panel Admin mengelola verifikasi, pencocokan, klaim, serah terima, statistik, akun, dan pengaturan. Panel User digunakan untuk laporan, pencarian, klaim, notifikasi, profil, dan komunikasi sesuai izin klaim.

## Lingkungan

- PHP 8.2 atau lebih baru; dependensi dikunci melalui composer.lock.
- MySQL/MariaDB untuk aplikasi XAMPP.
- SQLite in-memory untuk tes terisolasi.
- Blade, CSS dan JavaScript statis; stylesheet yang aktif masih mempertahankan urutan cascade desain sebelumnya.
- Pengujian refactoring ini dijalankan pada PHP 8.3.6, bukan pada Windows/XAMPP langsung.

## Pemasangan refactoring pada proyek yang sudah ada

Gunakan PASANG_CLEAN_CODE.cmd dari paket pemasang pada direktori proyek. Pemasang memeriksa hash source, menguji salinan terisolasi, membuat backup file, memasang perubahan, dan mengkompilasi Blade. Tidak ada migration atau perubahan database untuk paket ini. .env, vendor, unggahan pengguna, dan backup asli tidak ditimpa.

Salinan source-bersih dalam paket disediakan untuk inspeksi/pengembangan; tidak memuat .env, vendor, database pengguna atau unggahan. Jangan menyalin seluruh salinan tersebut ke proyek produksi.

## Struktur utama

- app/Http/Controllers: koordinasi HTTP dan otorisasi route/record yang sudah ada.
- app/Http/Requests: validasi laporan baru dan penyelesaian serah terima.
- app/Services: penyimpanan laporan, serah terima, skor pencocokan dan query statistik.
- app/Models, app/Observers, app/Notifications, app/Support: kontrak data dan pendukung aplikasi yang dipertahankan.
- resources/views: markup Blade, include dan data server.
- resources/views/admin/partials: bagian shell yang digunakan bersama.
- public/css/pages, public/js/pages: aset halaman yang diekstrak dari Blade.
- public/css/shared, public/js/shared: presentasi/interaksi yang memang berulang.
- archive: berkas historis yang dipindahkan, bukan bagian runtime. Backup asli dalam backup tetap dipertahankan.
- docs/refactoring: audit, peta dependensi, perubahan tiap tahap, pengujian dan pekerjaan tersisa.

JSON di script[type="application/json"] hanya memuat data dari server. Script eksekusi membaca JSON/data attribute; jangan memasukkan ekspresi Blade ke berkas JS statis.

Stylesheet PDF dibaca melalui file URI lokal dengan chroot hanya ke public; pemuatan remote tetap dimatikan. Jangan mengganti URL tersebut ke URL HTTP yang bergantung pada server aktif.

## Menjalankan aplikasi lokal

Dari direktori proyek yang telah dikonfigurasi:

    php artisan serve

Buka http://127.0.0.1:8000. MySQL harus aktif untuk penggunaan aplikasi.

## Kualitas kode

Konfigurasi PSR-12 tersedia di pint.json; Blade di .bladeformatterrc.json dan aset di .prettierrc.json.

    php vendor/bin/pint --config=pint.json app routes config bootstrap/app.php bootstrap/providers.php tests

Formatter yang digunakan: Laravel Pint bawaan composer.lock, blade-formatter 1.44.6, serta Prettier dan plugin PHP dengan target sintaks 8.2. Laporan memuat versi lengkap. Jangan memformat vendor, archive, backup, unggahan atau compiled views sebagai source aplikasi.

Jalankan pengujian pada salinan terisolasi. Pemasang menyediakan verify.php dan data baseline; skrip tersebut menolak salinan yang masih mempunyai .env asli. Tidak ada migrate:fresh pada database aplikasi.

## Status hasil audit

Refactoring tidak menyatakan seluruh aplikasi sudah siap produksi. Baseline memiliki 17 tes gagal/error sebelum perubahan; hasil akhir tetap mencatat 17 masalah yang sama, tanpa skip. Rincian dan batas pengujian terdapat di docs/refactoring. Perubahan alur bisnis, privasi lampiran lama, dan perbaikan desain responsif lanjutan harus dikerjakan sebagai pekerjaan tersendiri.


Nazwa ikut berkontribusi di project ini.