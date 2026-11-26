PATCH PROFIL FIND BACK KAMPUS - FIX STRUKTUR ZIP

Penting: ZIP ini sudah memiliki struktur relatif terhadap folder proyek. Ekstrak langsung ke C:\xampp\htdocs\findback-kampus. Tidak ada folder findback-kampus tambahan di dalam ZIP.

File yang diganti:
resources/views/user/profile/index.blade.php

CMD:
cd /d C:\xampp\htdocs\findback-kampus
mkdir "%TEMP%\findback-profile-backup"
copy resources\views\user\profile\index.blade.php "%TEMP%\findback-profile-backup\index.blade.php"
tar -xf "%USERPROFILE%\Downloads\FindBack-Profil-Responsive-Patch-FIX.zip" -C "C:\xampp\htdocs\findback-kampus"
php artisan view:clear
php artisan optimize:clear

Kemudian Ctrl+F5 di browser.
