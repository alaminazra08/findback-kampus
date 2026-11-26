FINDBACK KAMPUS — DIRECT NATIVE PHOTO PICKER PATCH

Basis patch:
findback-kampus(20261126-125326).zip

File yang diganti:
resources/views/user/profile/edit.blade.php

Perubahan:
- Menghapus markup modal "Atur Foto Profil".
- Menghapus JavaScript crop/zoom/rotasi.
- Tombol foto profil dan tombol "Pilih foto" membuka pemilih file bawaan browser/perangkat.
- Input menggunakan accept="image/*" agar browser/perangkat dapat menawarkan sumber foto yang tersedia.
- Setelah foto dipilih, pratinjau avatar langsung diperbarui.
- File asli tetap menjadi file yang dikirim oleh form.
- Validasi JPG/PNG/WebP dan ukuran maksimal 2 MB tetap ada.

PENTING:
Website tidak dapat memaksa UI pemilih foto native agar identik pada semua sistem operasi/browser.
Pada iPhone, Safari/iOS menentukan tampilan pilihan Kamera/Foto. Pada desktop, browser membuka file picker sistem.
Yang dihilangkan dari website adalah modal crop buatan sendiri.

CARA PASANG:
1. Backup file resources/views/user/profile/edit.blade.php.
2. Ekstrak ZIP ini ke folder root proyek C:\xampp\htdocs\findback-kampus.
3. Izinkan Replace saat diminta.
4. Jalankan:
   cd /d C:\xampp\htdocs\findback-kampus
   php artisan view:clear
5. Refresh browser dengan Ctrl + F5.
