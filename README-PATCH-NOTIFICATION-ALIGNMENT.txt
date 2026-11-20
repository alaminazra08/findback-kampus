PATCH FINDBACK KAMPUS - NOTIFICATION ROW ALIGNMENT

Perubahan:
1. Baris notifikasi memakai layout dua tingkat: judul/pesan/waktu di atas, badge kategori/status dan tombol aksi di bawah.
2. Badge kategori selalu rata kiri dan tombol aksi konsisten di kanan; notifikasi tanpa status (misalnya Registrasi) tidak meninggalkan kolom kosong.
3. Pesan laporan menggunakan kode barang BRG-0001, bukan format #1.
4. Pesan klaim menyebut kode barang BRG-0001 dan tidak menampilkan nomor dengan tanda #.
5. Polling real-time, filter, status dibaca, statistik, dan pagination tidak diubah; daftar tetap dirender melalui partial yang sama pada halaman utama dan endpoint live.

File patch:
- app/Http/Controllers/Admin/NotificationController.php
- public/css/admin/admin-notifications-center.css
- resources/views/admin/notifications/index.blade.php
- resources/views/admin/notifications/partials/live-list.blade.php

Cara memasang:
Ekstrak ZIP ini ke folder root project FindBack Kampus sehingga folder app, public, dan resources bergabung dengan folder yang sudah ada.
Setelah itu jalankan:
  php artisan view:clear
  php artisan optimize:clear

Tidak ada perintah Git, commit, atau push dalam patch ini.
