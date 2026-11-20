FINDBACK KAMPUS - FIX 500 PUSAT NOTIFIKASI (MODEL A)

PENYEBAB
notification-bell.blade.php masih memanggil NotificationController::currentStatusSnapshots(),
sedangkan controller pada patch Model A tidak lagi menyediakan fungsi tersebut karena
riwayat notifikasi harus menampilkan pesan peristiwa yang tersimpan, bukan snapshot status terbaru.

PERBAIKAN
- Menghapus pemanggilan currentStatusSnapshots() dari partial lonceng notifikasi.
- Menampilkan message yang tersimpan pada notifikasi masing-masing.
- Tidak mengubah database, tabel, riwayat, route, atau controller.

INSTALASI
Ekstrak ZIP ke root project (folder yang berisi artisan), lalu jalankan:
php artisan view:clear
php artisan optimize:clear

Tidak perlu migrasi database.
