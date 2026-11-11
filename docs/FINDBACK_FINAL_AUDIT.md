# Hasil audit FindBack Kampus — 11 November 2026

Sumber: findback-kampus(20261111-001628).zip. Laravel 12, PHP minimum 8.2. Tidak memodifikasi .env, APP_KEY asli, data atau migrasi lama. Daftar temuan, penyebab, perbaikan, dan rollback selengkapnya ada pada README.md paket.

## Hasil terukur

| Pemeriksaan | Hasil | Batas bukti |
|---|---|---|
| Suite awal | 76 test: 58 lulus, 9 failure, 9 error | Beberapa ekspektasi sudah tidak sesuai alur sumber terkini |
| Suite akhir | **87 test lulus, 832 assertions, 0 failure, 0 error, 0 skipped** | PHP 8.4.27 + SQLite memory, PHPUnit 11.5.57 |
| Syntax PHP | **98 file lulus php -l** | app, routes, tests dan database/migrations |
| Syntax JavaScript berubah | **4 file lulus node --check** | profile, settings-score, inbox chat, interaksi bersama |
| Render halaman | **46 HTML + 2 PDF berhasil dibuat** oleh RefactorRenderTest | Respons HTTP dan HTML/PDF; bukan inspeksi visual |
| Desktop 1366/1440/1920 | **BELUM TERVERIFIKASI** | Browser gagal diluncurkan: socket() Operation not permitted |
| Mobile 320/360/375/390/430 dan tablet 768 | **BELUM TERVERIFIKASI** | Penghalang browser yang sama |
| Installer Windows/Forms/XAMPP dan rollback | **BELUM TERVERIFIKASI** | Tinjauan statis; tidak ada Windows/PowerShell runtime |
| MySQL 10.4, dua request bersamaan, deadlock nyata | **BELUM TERVERIFIKASI** | SQLite tidak memvalidasi perilaku lock MySQL |
| Google OAuth, email reset password, hosting/HTTPS | **BELUM TERVERIFIKASI** | Tidak memakai akun/kredensial eksternal |

Perintah yang berhasil dijalankan pada salinan proyek:

- `php vendor/bin/phpunit --configuration phpunit.xml --log-junit ...` dengan APP_KEY testing dan konfigurasi SQLite memory.
- `php -l` pada 98 file PHP terkait.
- `node --check` pada empat file JavaScript yang berubah.

PHP disediakan secara terisolasi tanpa mengubah proyek. Test memakai factory dan Storage::fake pada pengujian upload, bukan unggahan asli. Data untuk render juga sintetis. Pengujian registrasi memastikan injeksi role admin diabaikan; pengujian chat memeriksa ownership, klaim pending/returned, lampiran, pagination, receipt per klaim, polling JSON dua arah, dan cleanup upload ketika status stale.

## Nilai berdasarkan bukti

**Nilai kelulusan automated test: 100/100 (87 dari 87 test). Ini bukan nilai kelayakan produksi.**

Untuk membuat cakupan bukti dapat dihitung, gunakan 10 gerbang dengan bobot sama:

| Gerbang penerimaan | Status |
|---|---|
| Registrasi/login/otorisasi dan ownership yang dicakup test | PASS |
| Pelaporan, penolakan, revisi historis dan kirim ulang | PASS |
| Pengajuan/keputusan klaim dan pencegahan duplikat | PASS |
| Bukti tambahan dan serah terima yang dicakup suite | PASS |
| Isolasi chat, status baca dan notifikasi yang dicakup test | PASS |
| Syntax, render halaman dan ekspor yang dicakup suite | PASS |
| Responsif desktop dengan browser | BELUM TERVERIFIKASI |
| Responsif mobile/tablet dengan browser | BELUM TERVERIFIKASI |
| Installer dan rollback Windows aktual | BELUM TERVERIFIKASI |
| MySQL/concurrency dan integrasi produksi | BELUM TERVERIFIKASI |

**Skor bukti penerimaan: 60/100 (6 dari 10 gerbang telah dibuktikan).** Ini mengukur kelengkapan verifikasi pada sesi ini, bukan perkiraan bahwa kualitas aplikasinya hanya 60. Nilai kelayakan produksi keseluruhan **belum dapat ditetapkan**. Empat gerbang yang belum diverifikasi tidak boleh dihitung lulus.

## Uji manual Admin → User dan User → Admin

Gunakan data uji di lingkungan staging/local. Siapkan Admin A, User U dan User V. Gunakan sesi browser terpisah agar role tidak tercampur.

1. U mendaftar/login. Pastikan akses /admin ditolak. Buat laporan hilang dan ditemukan dengan foto valid. U melihat pending dan Admin melihat antreannya. Coba foto SVG/berkas terlalu besar/field wajib kosong; server menolak.
2. Admin verifikasi laporan. U menerima notifikasi dan melihat status; barang muncul di pencarian. Tolak laporan kedua dengan alasan wajib. U melihat alasan, memperbaiki judul/kategori/lokasi/foto, mengirim ulang; Admin menerima notifikasi, melihat riwayat, lalu memverifikasi. Status pending kembali hanya setelah kirim ulang.
3. Buka tab Laporan Ditolak, isi filter pencarian lalu kirim. Halaman tetap pada history dan tidak error validasi revision.
4. V membuka ID laporan U melalui /laporan-saya/{id}; akses ditolak. Coba POST perbaikan milik U serta notifikasi milik U memakai akun V; ditolak dan data tidak berubah.
5. U klaim barang ditemukan milik V. Coba klaim ulang melalui dua tab; hanya satu klaim aktif. Coba barang pending/lost/returned atau laporan sendiri; ditolak. Sebelum approved, pengiriman chat ditolak.
6. Admin meminta bukti tambahan. U melihat permintaan dan mengirim jawaban/foto; Admin melihat riwayat. Coba request_id lama, milik orang lain, input kosong. Admin tidak boleh menyetujui sebelum permintaan terbuka selesai.
7. Admin menyetujui klaim dengan evidence_reviewed. U menerima tepat satu notifikasi keputusan. Klaim kedua pada barang sama tidak boleh ikut disetujui. Penolakan harus memiliki alasan yang terlihat pada U.
8. Buat dua klaim approved untuk U pada dua barang berbeda. Tanpa pesan awal sekalipun, pemilih klaim U harus terlihat. Kirim chat dan gambar U→Admin serta Admin→U. Tiap percakapan hanya menampilkan claim_id sendiri. Buka inbox tanpa memilih klaim: pesan belum dibaca tidak boleh otomatis habis. Buka klaim pertama: klaim kedua tetap unread.
9. Buka detail chat lebih dari 50 pesan; pindah halaman, konteks klaim tetap ada. Dari inbox buka seluruh riwayat. Saat tetap di inbox, kirim balasan dari sesi lain dan cek polling, urutan, gambar, serta scroll. Putuskan jaringan: pesan kegagalan polling tampil dan pulih setelah tersambung.
10. Buka Arsip Chat Lama dengan hanya satu klaim aktif. Arsip tetap terpilih; pesan baru tidak dikirim ke arsip. Arsip yang terlihat ditandai dibaca.
11. Admin isi serah terima beserta lampiran wajib. U melihat returned dan notifikasi. Admin dapat membuka bukti/PDF. Pengulangan return ditolak; klaim pending lain ditutup sesuai aturan dan riwayat dipertahankan. Pengiriman chat untuk klaim returned ditolak.
12. Di MySQL staging, jalankan approve/return/send hampir bersamaan pada dua sesi. Pastikan tidak ada dua pemenang, pesan setelah penutupan, unggahan yatim atau status tidak konsisten. Langkah ini BELUM TERVERIFIKASI dalam sesi audit.
13. U ubah profil/password; injeksi role tidak mengubah role. Password lama salah harus ditolak. Login ulang dengan password baru. Periksa riwayat laporan/klaim serta unread notifikasi konsisten.

## Checklist responsif wajib

Semua ukuran berikut berstatus BELUM TERVERIFIKASI visual. Uji pada zoom 100%, kemudian keyboard terbuka di perangkat mobile nyata. Uji sidebar expanded/compact/open, form error, modal terbuka, teks panjang, foto portrait/landscape, data kosong dan data penuh.

| Lebar CSS px | Jenis | Halaman yang harus diuji |
|---|---|---|
| 1366, 1440, 1920 | Desktop | Seluruh Admin dan User, publik/auth |
| 768 | Tablet | Seluruh Admin dan User, sidebar dan chat |
| 320, 360, 375, 390, 430 | Mobile | Seluruh Admin dan User, publik/auth |

Admin: dashboard; Data Barang dan modal tambah; laporan hilang/ditemukan; antrean/detail verifikasi; riwayat ditolak; daftar/detail klaim pending/approved/rejected/returned dan modal bukti/serah terima; pencocokan/detail; statistik/filter/grafik/print; akun/detail; pengaturan; inbox dan detail chat.

User: dashboard; pencarian/filter/detail; form lapor hilang/ditemukan dan wizard; daftar/detail laporan serta form perbaikan; form klaim dan riwayat klaim; bukti tambahan; chat multi-klaim/arsip/pagination; notifikasi; profil/password. Publik: landing, login, register, lupa/reset password.

- [ ] `document.documentElement.scrollWidth <= window.innerWidth` pada semua halaman/state. Jangan meluluskan pemeriksaan hanya dengan overflow-x:hidden pada body.
- [ ] Tabel lebar hanya menggulir di kontainernya; halaman tetap tidak melebar.
- [ ] Sidebar dapat dibuka/ditutup, Escape dan overlay berfungsi; fokus tidak tertutup.
- [ ] Tombol/input penting minimal sekitar 44 px untuk disentuh; label dan error terbaca.
- [ ] Header, toolbar, pagination, kartu, grafik dan menu dropdown tidak bertumpuk/terpotong.
- [ ] Modal tidak melewati viewport; isi dapat discroll, tombol submit/batal tetap terjangkau.
- [ ] Foto proporsional, lampiran tidak melampaui bubble, nama/email/teks panjang membungkus.
- [ ] Chat nyaman saat keyboard mobile terbuka; pemilih klaim dan tombol kirim tetap terjangkau.
- [ ] Navigasi keyboard/fokus terlihat; form tetap tervalidasi ketika JS dimatikan.
- [ ] Tidak ada error console baru. Uji Chrome/Edge desktop dan Chrome Android/Safari iOS bila menjadi perangkat sasaran.

## Batas klaim hasil

Pengujian tidak membuktikan seluruh kombinasi data, perangkat, akses langsung ke seluruh URL berkas publik, beban server, email/OAuth atau konfigurasi hosting aman. Lampiran chat/proof masih memakai disk public sesuai struktur sumber; suite membuktikan scope endpoint percakapan, bukan autentikasi HTTP untuk URL file statis yang sudah diketahui. Jangan gunakan hasil ini sebagai sertifikasi keamanan/kelayakan produksi penuh.

## Verifikasi lanjutan installer Windows

Log pengguna membuktikan PHP XAMPP 8.2.34 dan syntax 13 file PHP patch lulus. Percobaan suite pada Windows berhenti sebelum feature test berjalan: 1 unit assertion lulus, 86 bootstrap errors akibat path cache tergandakan. Ini bukan 86 temuan bug fungsi yang berbeda. Tidak ada penerapan file proyek pada percobaan tersebut.

Perbaikan installer memakai path cache relatif untuk CONFIG/SERVICES/PACKAGES/ROUTES/EVENTS. Kelima kasus path Windows lama direproduksi menggunakan normalizer Laravel sumber, dan kelima nilai pengganti lulus pemeriksaan tanpa duplikasi drive. Konfigurasi QA serta bootstrap guard baru juga diuji pada salinan proyek terisolasi dengan folder mengandung spasi: 87 test lulus, 832 assertions pada PHP 8.4.27/SQLite. Eksekusi end-to-end installer baru pada Windows tetap BELUM TERVERIFIKASI sampai percobaan pengguna selesai.
