FINDBACK KAMPUS — NOTIFICATION TOOLBAR PATCH
=============================================

Perubahan:
- Toolbar desktop menggunakan satu baris profesional bila ruang mencukupi.
- Tab kategori tetap di kiri dan dapat digeser horizontal pada layar kecil.
- Pencarian, dropdown urutan, Terapkan, dan Atur Ulang memiliki tinggi 49px pada desktop.
- Tombol Terapkan dan Atur Ulang memiliki ukuran, radius, tipografi, serta alignment yang sama.
- Pada mobile, pencarian dan dropdown mengambil lebar penuh; dua tombol tampil berdampingan dengan lebar sama.
- Atur Ulang menghapus kategori, kata kunci, urutan khusus, dan kembali ke halaman pertama (default Semua + Terbaru).
- Tidak mengubah controller, database, notifikasi real-time, atau pagination.

CARA PASANG (Windows CMD)
1. Pastikan ZIP ini berada di folder Downloads.
2. Jalankan:
   cd /d C:\xampp\htdocs\findback-kampus
3. Ekstrak patch:
   tar -xf "%USERPROFILE%\Downloads\findback-notification-toolbar-responsive-v2.zip" -C "C:\xampp\htdocs\findback-kampus"
4. Bersihkan cache view:
   php artisan view:clear
5. Buka:
   http://127.0.0.1:8000/admin/notifications
6. Tekan Ctrl + F5.

Backup file sebelum perubahan tersedia di:
backup/notification-toolbar-before-20261120/

Catatan:
Patch hanya berisi file yang diperlukan. Tidak ada perintah Git, commit, atau push.
