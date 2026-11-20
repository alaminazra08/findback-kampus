FINDBACK KAMPUS — SEARCH TYPING CONSISTENCY V5
================================================

Perubahan:
- Menonaktifkan pemeriksa ejaan pada seluruh 9 kolom pencarian admin.
- Menonaktifkan autocorrect dan autocapitalize pada kolom pencarian.
- Menyamakan warna seleksi teks pencarian menjadi pink lembut FindBack.
- Mempertahankan tombol Atur Ulang sebagai kontrol untuk mengosongkan pencarian.
- Tidak mengubah controller, route, database, atau mekanisme notifikasi real-time.
- Cache-busting stylesheet search-input-consistency.css diperbarui.

Pemasangan CMD (jalankan dari folder project):
1. cd /d C:\xampp\htdocs\findback-kampus
2. tar -xf "%USERPROFILE%\Downloads\findback-search-typing-consistency-v5.zip" -C "C:\xampp\htdocs\findback-kampus"
3. php artisan view:clear
4. Tekan Ctrl+F5 pada browser.

File yang diperbarui:
- resources/views/admin/chat/index.blade.php
- resources/views/admin/claims/index.blade.php
- resources/views/admin/items/index.blade.php
- resources/views/admin/matching/index.blade.php
- resources/views/admin/notifications/index.blade.php
- resources/views/admin/reports/found/index.blade.php
- resources/views/admin/reports/lost/index.blade.php
- resources/views/admin/reports/verification/results.blade.php
- resources/views/admin/users/index.blade.php
- resources/views/admin/layouts/items.blade.php
- public/css/admin/search-input-consistency.css

Backup file asli tersedia di folder _backup_before_patch.
Tidak ada perintah git commit/push.
