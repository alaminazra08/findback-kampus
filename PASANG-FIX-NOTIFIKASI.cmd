@echo off
setlocal
cd /d C:\xampp\htdocs\findback-kampus
if not exist artisan (
  echo ERROR: Folder project Laravel tidak ditemukan.
  pause
  exit /b 1
)
if not exist "%USERPROFILE%\Downloads\findback-notification-history-A-fix1.zip" (
  echo ERROR: ZIP tidak ditemukan di folder Downloads.
  pause
  exit /b 1
)
if not exist backup\notification-history-A-fix1 mkdir backup\notification-history-A-fix1
if exist resources\views\admin\partials\notification-bell.blade.php copy /Y resources\views\admin\partials\notification-bell.blade.php backup\notification-history-A-fix1\notification-bell.blade.php >nul
tar -xf "%USERPROFILE%\Downloads\findback-notification-history-A-fix1.zip" -C "C:\xampp\htdocs\findback-kampus"
if errorlevel 1 (
  echo ERROR: Gagal mengekstrak patch.
  pause
  exit /b 1
)
php artisan view:clear
php artisan optimize:clear
echo.
echo FIX SELESAI. Buka kembali /admin/notifications dan tekan Ctrl+F5.
pause
