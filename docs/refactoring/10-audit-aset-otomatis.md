# Audit aset statis otomatis — tahap verifikasi

Paket ini **bukan** refactoring final dan tidak mengubah fitur, CSS, JS, Blade, route, atau database. Paket menambahkan pemeriksaan baca-saja untuk mendeteksi referensi CSS/JS literal yang hilang dan nama file aset yang masih menggunakan suffix versi `-vN`.

Jalankan: `php tools/findback-audit-assets.php`.

**Batasan:** pemeriksaan regex statis dapat memberikan false positive atau melewatkan pemanggilan dinamis. Pemeriksaan browser, PHPUnit, AJAX, akses Admin–User, dan responsivitas tetap diperlukan. Kode keluar 1 bila ditemukan temuan; ini bukan bukti bahwa aplikasi rusak.

Paket pemasangan melakukan backup sebelum perubahan, menolak perubahan lokal Git yang belum disimpan, tidak menjalankan migration, tidak mengubah Git, dan mencoba rollback bila pemasangan gagal. File baru perlu di-commit secara terpisah setelah review.
