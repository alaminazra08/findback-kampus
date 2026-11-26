FINDBACK KAMPUS - PROFILE CARD SIZE & RESPONSIVE ALIGNMENT PATCH

Tujuan:
- Menyamakan lebar kartu halaman Profil dengan halaman Edit Profil (maksimum 1040px pada desktop).
- Menyamakan header, radius, border, dan shadow.
- Mengurangi ruang kosong berlebihan pada kartu Profil.
- Merapikan proporsi kolom identitas dan informasi di desktop.
- Menjaga layout mobile satu kolom, responsif, dan nyaman dibaca.
- Tidak mengubah database, route, controller, proses edit, atau fitur lain.

File yang diperbarui:
resources/views/user/profile/index.blade.php

Pasang patch dari folder root Laravel findback-kampus.
Sebelum pemasangan, buat cadangan file lama menggunakan perintah CMD yang diberikan.
Setelah ekstraksi, jalankan php artisan view:clear lalu refresh browser dengan Ctrl + F5.
