PATCH V8 - PUSAT NOTIFIKASI FINDBACK KAMPUS

Isi patch:
- app/Http/Controllers/Admin/NotificationController.php
  Menyertakan snapshot status terkini pada respons sinkronisasi daftar notifikasi.
- resources/views/admin/notifications/index.blade.php
  Menambahkan identitas state per admin, penanda form/tab, reset yang tetap memiliki fallback navigasi, dan cache-busting aset V8.
- public/js/admin/notification-stats-live.js
  Terapkan filter, kategori, pagination, dan reset melalui AJAX tanpa reload seluruh halaman; menjaga posisi scroll; menyimpan filter, halaman, dan posisi scroll di sessionStorage per tab; memulihkan kondisi saat kembali dari menu lain; sinkronisasi status dan daftar otomatis setiap 2 detik.
- public/css/admin/admin-notifications-center.css
  Menjaga toolbar responsif desktop/mobile tanpa scrollbar kategori; tombol detail seragam; input pencarian tetap putih dengan selection warna lembut.

CARA PASANG:
1. Tutup tab aplikasi FindBack Kampus atau biarkan terbuka.
2. Ekstrak ZIP ini ke folder project C:\xampp\htdocs\findback-kampus dan izinkan file ditimpa.
3. Jalankan perintah CMD pada petunjuk ChatGPT.
4. Buka ulang halaman Pusat Notifikasi, lalu tekan Ctrl+F5.

CATATAN:
- Filter Terapkan/Atur Ulang dan perpindahan kategori/pagination memakai AJAX. Posisi layar tidak dikembalikan ke atas.
- State filter dan posisi scroll dipertahankan per tab browser ketika berpindah menu lalu kembali ke Pusat Notifikasi.
- Sinkronisasi latar belakang menggunakan interval 2 detik untuk menghindari polling database berlebihan. Aksi Terapkan/Atur Ulang diproses langsung melalui request AJAX; waktu respons 200 ms tidak dapat dijamin karena bergantung pada server dan database.
- Tidak mengubah desain halaman admin lain.
