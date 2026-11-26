PATCH FINDBACK KAMPUS - NOTIFIKASI AUTO-READ

Perubahan:
1. Seluruh baris notifikasi pengguna dapat diklik.
2. Saat baris notifikasi diklik, sistem otomatis mengubah read_at menjadi waktu saat ini.
3. Setelah ditandai dibaca, pengguna langsung diarahkan ke halaman laporan/klaim/barang terkait.
4. Indikator titik merah kecil di samping badge status dihilangkan.
5. Notifikasi yang belum dibaca tetap memiliki pembeda visual yang lembut.
6. Tombol "Tandai semua dibaca" tetap tersedia untuk menandai seluruh notifikasi sekaligus.

File yang diperbarui:
- routes/web.php
- app/Http/Controllers/User/CampusController.php
- resources/views/user/notifications/index.blade.php

Pemasangan:
Ekstrak ZIP ke folder Downloads, lalu jalankan perintah CMD yang disertakan pada instruksi ChatGPT.
Setelah pemasangan, jalankan php artisan optimize:clear dan refresh browser dengan Ctrl + F5.

Catatan:
Patch ini hanya mengubah perilaku notifikasi pada sisi USER. Halaman notifikasi ADMIN tidak diubah.
