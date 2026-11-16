<div align="center">

<img src="public/images/logo-findback.png" alt="Logo FindBack Kampus" width="105" />

# FindBack Kampus

**Sistem Informasi Barang Hilang dan Ditemukan Berbasis Web**

Fakultas Sains dan Teknologi · UIN Imam Bonjol Padang

[![Laravel 12](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)](https://laravel.com/)
[![PHP 8.2+](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![MySQL / MariaDB](https://img.shields.io/badge/Database-MySQL%20%2F%20MariaDB-4479A1?logo=mysql&logoColor=white)](https://mariadb.org/)
[![Status](https://img.shields.io/badge/Status-Pengembangan%20%26%20Pengujian-DA8B18)](#status-pengembangan-dan-pengujian)

[**Tentang**](#tentang-proyek) · [**Tampilan**](#tampilan-aplikasi) · [**Fitur**](#fitur-utama) · [**Instalasi**](#menjalankan-proyek-secara-lokal) · [**Tim**](#tim-pengembang)

*Temukan, laporkan, dan kembalikan. Untuk kampus yang lebih peduli.*

</div>

---

## Tentang proyek

**FindBack Kampus** adalah sistem informasi *lost and found* berbasis website untuk membantu sivitas akademika **Fakultas Sains dan Teknologi, UIN Imam Bonjol Padang** dalam melaporkan barang hilang atau ditemukan, mencari informasi barang, dan mengajukan klaim secara terpusat.

Proyek ini dikembangkan sebagai bagian dari **Mata Kuliah Manajemen Proyek Sistem Informasi** dengan metode **Prototype**. Sistem dirancang untuk menggantikan penyampaian informasi yang tersebar melalui komunikasi langsung dan grup percakapan menjadi proses yang lebih terstruktur, dapat ditelusuri, dan terdokumentasi.

Laporan barang diperiksa oleh administrator sebelum dipublikasikan. Hasil pencocokan otomatis merupakan **indikasi kemungkinan kecocokan**, bukan penetapan kepemilikan; konfirmasi dan verifikasi klaim tetap dilakukan oleh administrator.

## Tampilan aplikasi

<div align="center">
  <img src="public/images/login-fst-premium.jpg" alt="Pratinjau halaman login FindBack Kampus" width="520" />
  <p><sub>Pratinjau halaman login FindBack Kampus · Tampilan pada desktop dan perangkat seluler menyesuaikan ukuran layar.</sub></p>
</div>

> Cuplikan di atas merupakan pratinjau antarmuka, bukan bukti bahwa seluruh fungsi telah lulus pengujian. Dokumentasi visual halaman lainnya dapat ditambahkan setelah diverifikasi.

## Fitur utama

| Modul | Pengguna (Sivitas Akademika) | Administrator Fakultas |
| :--- | :--- | :--- |
| **Laporan barang** | Membuat laporan barang hilang atau ditemukan dengan informasi dan foto | Memeriksa, menyetujui, menolak, dan memublikasikan laporan |
| **Pencarian** | Menelusuri laporan berdasarkan kata kunci dan filter | Mengelola informasi dan status laporan |
| **Pencocokan** | Menerima informasi kecocokan setelah konfirmasi admin | Meninjau dan mengonfirmasi kemungkinan kecocokan |
| **Klaim dan pengembalian** | Mengajukan klaim dan memantau perkembangannya | Memverifikasi kepemilikan dan mengelola proses serah terima |
| **Komunikasi** | Menggunakan chat terkait klaim sesuai tahapan dan izin akses | Berkomunikasi dengan pengklaim untuk verifikasi dan koordinasi |
| **Notifikasi** | Memantau perubahan status laporan, klaim, dan pesan | Memantau tindak lanjut yang memerlukan penanganan |
| **Statistik dan laporan** | Memantau aktivitas dan status laporan sendiri | Melihat statistik serta menyiapkan laporan berkala dalam format PDF |
| **Akun dan hak akses** | Mengelola informasi akun sendiri | Mengelola akun, peran, dan pengaturan aplikasi |

**Alur layanan utama**

```text
Pengguna membuat laporan
          ↓
Admin memverifikasi informasi dan foto
          ↓
Laporan disetujui dan dipublikasikan
          ↓
Pencarian / identifikasi kemungkinan kecocokan
          ↓
Konfirmasi admin dan pengajuan klaim pengguna
          ↓
Verifikasi kepemilikan dan komunikasi klaim
          ↓
Serah terima barang serta pembaruan status
```

> Proses pencocokan dan klaim tetap memerlukan pemeriksaan administrator. Hak akses dibedakan antara pengguna dan administrator.

## Teknologi

| Komponen | Implementasi |
| :--- | :--- |
| **Backend** | Laravel 12 · PHP 8.2+ |
| **Frontend** | Laravel Blade · CSS · JavaScript · Vite |
| **Basis data** | MySQL / MariaDB |
| **Laporan PDF** | `barryvdh/laravel-dompdf` |
| **Autentikasi sosial** | `laravel/socialite` (dependensi tersedia; konfigurasi dan alur perlu diverifikasi) |
| **Pengelolaan dependensi** | Composer · npm |
| **Version control** | Git · GitHub |
| **Lingkungan pengembangan** | XAMPP / PHP lokal |
| **Pendekatan pengembangan** | Prototype |

## Menjalankan proyek secara lokal

### Persyaratan

- PHP **8.2 atau lebih baru**, Composer, MySQL/MariaDB, serta Node.js/npm untuk aset frontend.
- Database lokal yang disiapkan khusus untuk lingkungan pengembangan.
- Git dan terminal (Windows CMD/PowerShell atau terminal lain).

### 1. Ambil kode proyek

```bash
git clone https://github.com/alaminazra08/findback-kampus.git
cd findback-kampus
composer install
```

### 2. Konfigurasi lingkungan

Untuk **instalasi baru**, buat `.env` dari contoh. Di **Windows CMD**:

```bat
copy .env.example .env
php artisan key:generate
```

Di Linux/macOS gunakan `cp .env.example .env`. Sesuaikan `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` pada `.env` dengan database lokal.

**Jangan menimpa `.env` dari instalasi yang sudah berjalan.** Jangan mengunggah `.env`, kata sandi, database, file privat, atau cadangan ke repositori publik.

### 3. Siapkan database dan aset

Jalankan migrasi **hanya untuk database baru yang telah diperiksa**:

```bash
php artisan migrate
```

Jika aset Vite belum tersedia atau perlu dibangun:

```bash
npm install
npm run build
```

> Jangan menggunakan `migrate:fresh`, `db:wipe`, atau perintah penghapusan data pada database tim. Lakukan pemeriksaan dan pencadangan sebelum migrasi pada instalasi yang sudah berisi data.

### 4. Jalankan aplikasi

```bash
php artisan serve
```

Buka alamat lokal yang tampil pada terminal, umumnya **http://127.0.0.1:8000**. Port dapat berbeda bila sudah digunakan proses lain.

## Struktur proyek

```text
app/Http/Controllers/    # Controller dan logika permintaan HTTP
app/Http/Requests/       # Validasi permintaan
app/Models/              # Model data aplikasi
app/Services/            # Layanan dan logika pendukung
resources/views/         # Halaman Laravel Blade
public/css/              # Stylesheet
public/js/               # Interaksi frontend
public/images/           # Logo dan aset visual
routes/                  # Rute aplikasi
config/                  # Konfigurasi
database/               # Migrasi, seeder, dan factory
tests/                  # Pengujian
```

## Kualitas kode dan pengujian

Repositori memuat konfigurasi **Laravel Pint**, **Blade Formatter**, dan **Prettier**. Untuk menjalankan tes pada **database pengujian yang terisolasi**:

```bash
php artisan test
```

Pemeriksaan gaya kode menggunakan Laravel Pint (apabila dependensi pengembangan telah terpasang):

```bash
php vendor/bin/pint --test --config=pint.json app routes config tests
```

Pengujian mencakup kebutuhan pelaporan, verifikasi, pencarian, pencocokan, klaim, chat, pengelolaan hak akses, dan laporan. **Keberadaan kode dan skenario tes tidak berarti seluruh fungsi sudah lulus pengujian end-to-end.** Pastikan lingkungan tes tidak terhubung ke database proyek yang berisi data asli.

## Ruang lingkup

FindBack Kampus dirancang untuk **Fakultas Sains dan Teknologi UIN Imam Bonjol Padang**, dengan dua peran utama: **Pengguna** dan **Administrator**. Fokus implementasinya adalah **website responsif**, bukan aplikasi Android/iOS native. Integrasi dengan sistem akademik kampus tidak termasuk ruang lingkup proyek.

## Status pengembangan dan pengujian

**Status: pengembangan dan validasi.** Proyek belum dinyatakan siap produksi melalui README ini. Sebelum deployment, tim perlu memverifikasi alur pengguna–admin secara menyeluruh, hak akses, integritas data, kinerja, tampilan desktop/mobile, serta hasil uji fungsional, integrasi, dan penerimaan pengguna (UAT).

## Tim pengembang

Proyek disusun oleh **Kelompok II**, Program Studi Sistem Informasi, Fakultas Sains dan Teknologi, UIN Imam Bonjol Padang (2026).

| Nama | Peran proyek |
| :--- | :--- |
| **Pisa Kesuma Dewi** | Project Manager |
| **Nadia Triwardani** | System Analyst |
| **Nazwa Wanda Putri Siagian** | UI/UX Designer |
| **Al Amin Azra** | Programmer |
| **Mach Y Sa** | Quality Assurance |

**Dosen pengampu:** Haditya Prasetyo, S.Kom., M.Kom.

Aktivitas kolaborasi kode dapat dilihat melalui [riwayat commit](https://github.com/alaminazra08/findback-kampus/commits/main/) dan [kontributor GitHub](https://github.com/alaminazra08/findback-kampus/graphs/contributors). Riwayat Git mencatat kontribusi kode dan tidak selalu sama dengan pembagian tanggung jawab pada laporan proyek.

## Keamanan dan penggunaan data

Pastikan `.env`, kredensial, data pribadi, gambar atau bukti klaim pengguna, serta backup tidak dipublikasikan. Gunakan data uji untuk demonstrasi dan lakukan audit akses administrator, validasi unggahan, keamanan formulir, serta backup sebelum penerapan nyata.

---

<div align="center">

**FindBack Kampus**  
Fakultas Sains dan Teknologi · UIN Imam Bonjol Padang

*Helping lost items find their way back.*

</div>
