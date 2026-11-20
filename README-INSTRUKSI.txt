FINDBack KAMPUS - PATCH TAHAP 1: NOTIFIKASI REGISTRASI

Isi patch:
- app/Http/Controllers/AuthController.php
- app/Http/Controllers/GoogleAuthController.php
- app/Http/Controllers/Admin/NotificationController.php
- app/Services/RegistrationAdminNotifier.php (file baru)

Fungsi:
1. Registrasi email/password baru mengirim notifikasi ke semua akun admin.
2. Pembuatan akun Google baru mengirim notifikasi ke semua akun admin.
3. Login Google untuk akun yang sudah ada tidak mengirim notifikasi ulang.
4. Tombol Buka pada notifikasi registrasi diarahkan ke detail akun di Manajemen Akun.
5. Menggunakan tabel notifications dan AdminMessageNotification yang sudah ada.
6. Tidak menambahkan migration atau tabel baru.

CARA MEMASANG DI WINDOWS:
1. Tutup dulu proses yang sedang mengedit file project.
2. Pastikan ZIP ini sudah berada di folder Downloads.
3. Buka CMD, lalu jalankan:

   cd /d C:\xampp\htdocs\findback-kampus
   mkdir "%TEMP%\findback-notif-backup"
   copy app\Http\Controllers\AuthController.php "%TEMP%\findback-notif-backup\AuthController.php.bak"
   copy app\Http\Controllers\GoogleAuthController.php "%TEMP%\findback-notif-backup\GoogleAuthController.php.bak"
   copy app\Http\Controllers\Admin\NotificationController.php "%TEMP%\findback-notif-backup\NotificationController.php.bak"
   tar -xf "%USERPROFILE%\Downloads\findback-registration-notification-step1.zip" -C .
   php artisan optimize:clear

4. Jika perintah selesai tanpa error, tahap pemasangan selesai.
5. Tidak perlu menjalankan php artisan migrate.

PENGUJIAN:
- Daftarkan akun baru dengan email/password; admin harus menerima notifikasi "Pengguna baru terdaftar".
- Daftarkan akun Google yang belum pernah ada; admin harus menerima notifikasi yang sama.
- Login kembali dengan akun Google yang sudah terdaftar; tidak boleh membuat notifikasi registrasi baru.
- Klik Buka pada notifikasi registrasi; harus menuju detail akun di Manajemen Akun.

Catatan:
Patch ini belum membuat halaman Pusat Notifikasi penuh. Halaman tersebut menjadi tahap berikutnya setelah notifikasi registrasi berhasil diuji.
