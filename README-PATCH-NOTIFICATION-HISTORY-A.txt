FINDBACK KAMPUS - PATCH PUSAT NOTIFIKASI RIWAYAT AKTIVITAS (MODEL A)

ISI PERBAIKAN
1. Setiap perubahan status laporan barang membuat notifikasi admin baru.
2. Setiap perubahan status klaim membuat notifikasi admin baru.
3. Notifikasi lama tidak lagi ditimpa oleh snapshot status terkini.
4. Pesan dan badge status ditampilkan dari data peristiwa yang tersimpan saat notifikasi dibuat.
5. Pencarian mendukung judul, pesan, kode BRG, dan status. Pencarian kode BRG juga mencakup notifikasi klaim terkait barang.
6. Pengurutan memakai waktu pembuatan dan ID sebagai urutan kedua agar stabil.
7. Keterangan waktu relatif diperbarui di browser setiap 30 detik.
8. Polling daftar tetap setiap 2 detik dan mempertahankan filter, halaman, serta posisi scroll yang sudah ada.
9. Tidak ada migrasi atau tabel baru.

FILE YANG DIGANTI
- app/Http/Controllers/Admin/NotificationController.php
- app/Notifications/AdminMessageNotification.php
- app/Support/AdminAlerts.php
- app/Observers/LostFoundItemObserver.php
- app/Observers/ItemClaimObserver.php
- public/js/admin/notification-stats-live.js
- resources/views/admin/notifications/partials/live-list.blade.php
- resources/views/admin/notifications/index.blade.php

CATATAN
Patch ini ditujukan untuk struktur project Laravel FindBack Kampus yang diberikan.
Ekstrak ZIP ke root project (folder yang berisi artisan), bukan ke dalam subfolder baru.
Patch tidak menyertakan .env, database, vendor, node_modules, maupun folder .git.
