# Pemasangan dan pemulihan

Paket hanya cocok dengan source ZIP 20261103-032904. Jika ada perubahan setelah ZIP dibuat, pemasang berhenti pada guard hash; kirim source terbaru untuk dibuatkan delta yang sesuai, jangan melewati guard.

## Cara pakai

1. Simpan ZIP paket.
2. Jalankan perintah CMD yang disertakan pada jawaban, dari C:\xampp\htdocs\findback-kampus.
3. Pilih FindBack_Clean_Code_Refactor_CMD.zip.
4. Tunggu tes, backup, pemasangan, pemeriksaan route dan kompilasi.
5. Status berhasil hanya jika CMD menampilkan PEMASANGAN REFACTORING SELESAI.
6. Buka aplikasi lalu Ctrl+F5.

Pemasang membuat backup file yang akan berubah pada C:\xampp\findback-backups\clean-code-TANGGAL-GUID. Backup lama tidak ditimpa. Tidak ada SQL/migration/data pengguna yang diubah, sehingga tidak ada perintah restore/migration database untuk paket ini.

Salinan uji memakai database SQLite in-memory, tanpa .env asli, upload dan database SQLite proyek. PHP bawaan XAMPP diprioritaskan. Jika DLL SQLite bawaan tersedia tetapi belum aktif di php.ini, verify.php memuatnya khusus pada proses pengujian dan proses anaknya; php.ini tidak diedit. Jika prasyarat lain tidak terpenuhi, pemasangan berhenti sebelum source diubah. Batas waktu tes 300 detik per proses; mesin lambat bisa berhenti aman.

## Pengujian pemasang yang benar-benar dilakukan

PowerShell 7.4.6 pada Linux dipakai untuk simulasi, dengan operasi salin Robocopy digantikan helper lokal. Ini bukan pengujian native Windows/Robocopy/PowerShell 5.1.

- Source cocok: 237 file perubahan/baru dan 12 pemindahan (sebelum penambahan dokumen akhir) diterapkan; hash hasil cocok, route/Blade berhasil, aplikasi keluar maintenance.
- Source diubah: perbedaan app/Models/User.php ditolak sebelum pemasangan.
- Kegagalan dipaksakan saat kompilasi sesudah pemasangan: semua file yang berubah dikembalikan, file baru dihapus, arsip dikembalikan, hash cocok dengan awal.
- .env, database/database.sqlite dan seluruh backup asli tetap identik pada simulasi sukses maupun gagal.
- Parser PowerShell memeriksa skrip pemasang.
- Aktivasi DLL SQLite sementara pada Windows belum dapat diuji native di lingkungan ini.

Jumlah perubahan final yang otoritatif ada di manifest.json; angka simulasi merupakan kondisi saat simulasi dijalankan.

## Jika pemasangan gagal sesudah source mulai berubah

Pemasang mencoba rollback file dan pemindahan arsip. Aplikasi tetap maintenance agar hasil pemulihan diperiksa sebelum dibuka kembali. Lokasi backup dan salinan/log uji dicetak pada CMD. Setelah memastikan rollback selesai:

    php artisan route:clear
    php artisan view:clear
    php artisan up

Tidak menjalankan migrate:rollback atau restore SQL karena paket ini tidak mengubah database. Jika rollback melaporkan hash berbeda, periksa file pada backup sebelum membuka aplikasi.

Pemasangan Windows/XAMPP di perangkat pengguna tetap harus diverifikasi melalui hasil CMD; simulasi lintas platform tidak menggantikan bukti tersebut.
