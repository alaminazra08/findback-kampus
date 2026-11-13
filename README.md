<div align="center">

<img src="public/images/logo-findback.png" alt="Logo FindBack Kampus" width="125" />

# FindBack Kampus

**Sistem Informasi Barang Hilang dan Ditemukan Berbasis Web**

Fakultas Sains dan Teknologi · UIN Imam Bonjol Padang

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)](https://laravel.com/)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Database](https://img.shields.io/badge/Database-MySQL%20%2F%20MariaDB-4479A1?logo=mysql&logoColor=white)](https://mariadb.org/)
[![Status](https://img.shields.io/badge/Status-Pengembangan-informational)](#status-dan-batasan-pengujian)

**[Fitur](#fitur-utama) · [Teknologi](#teknologi) · [Instalasi](#menjalankan-proyek-secara-lokal) · [Tim](#tim-dan-kontribusi)**

</div>

---

## Tentang proyek

**FindBack Kampus** adalah aplikasi *lost and found* berbasis Laravel 12 untuk membantu sivitas akademika Fakultas Sains dan Teknologi UIN Imam Bonjol Padang melaporkan barang hilang, menelusuri barang yang ditemukan, serta mengelola proses klaim secara terpusat.

Aplikasi menyediakan dua ruang kerja: **Pengguna** untuk pelaporan dan pencarian barang, serta **Administrator** untuk verifikasi, pencocokan, pengelolaan klaim, dan pemantauan layanan.

## Tampilan aplikasi

<div align="center">
  <img src="public/images/login-fst-premium.jpg" alt="Tampilan halaman login FindBack Kampus" width="760" />
  <br />
  <sub>Pratinjau halaman login. Screenshot dashboard dan fitur lainnya akan ditambahkan setelah diverifikasi.</sub>
</div>

## Fitur utama

| Pengguna | Administrator |
| --- | --- |
| Melaporkan barang hilang atau ditemukan | Memverifikasi laporan barang |
| Mencari dan menelusuri laporan barang | Mengelola pencocokan barang |
| Mengajukan dan memantau klaim | Memproses klaim dan serah terima |
| Melihat aktivitas dan notifikasi | Memantau laporan dan statistik |
| Mengelola profil | Mengelola akun dan pengaturan |
| Berkomunikasi sesuai izin klaim | Mengelola komunikasi terkait klaim |

> Ketersediaan dan perilaku setiap fitur perlu divalidasi kembali pada lingkungan instalasi yang digunakan. Daftar ini merangkum cakupan aplikasi, bukan pernyataan bahwa semua alur telah lulus uji produksi.

## Teknologi

| Komponen | Teknologi |
| --- | --- |
| Framework backend | Laravel 12 |
| Bahasa pemrograman | PHP 8.2 atau lebih baru |
| Tampilan | Laravel Blade, CSS, JavaScript |
| Database aplikasi | MySQL / MariaDB |
| Database pengujian terisolasi | SQLite *in-memory* |
| PDF | `barryvdh/laravel-dompdf` |
| Autentikasi sosial (dependensi) | `laravel/socialite` |
| Manajemen dependensi | Composer (`composer.lock`) |

## Menjalankan proyek secara lokal

**Prasyarat:** PHP 8.2+, Composer, MySQL/MariaDB, serta lingkungan pengembangan seperti XAMPP. Node.js/npm diperlukan bila aset frontend perlu dibangun menggunakan Vite.

```bash
git clone https://github.com/alaminazra08/findback-kampus.git
cd findback-kampus
composer install
```

Untuk instalasi baru, buat konfigurasi lokal dari contoh yang tersedia, kemudian sesuaikan pengaturan koneksi database di `.env`:

```bash
cp .env.example .env
php artisan key:generate
```

> Di Windows CMD, gunakan `copy .env.example .env` sebagai pengganti `cp`. Jangan menimpa `.env` pada instalasi yang sudah berjalan. Siapkan database sesuai konfigurasi lokal sebelum menjalankan aplikasi.

Jika skema database memang perlu dibuat pada **database baru yang telah diverifikasi**, jalankan:

```bash
php artisan migrate
```

Jangan menjalankan migrasi, `migrate:fresh`, atau perintah penghapusan data pada database proyek tim tanpa pemeriksaan dan backup terlebih dahulu.

Jalankan aplikasi:

```bash
php artisan serve
```

Buka `http://127.0.0.1:8000`. Jika proyek menggunakan aset Vite yang belum dibangun, jalankan `npm install` dan `npm run dev` di terminal lain sesuai kebutuhan konfigurasi frontend.

## Struktur proyek

```text
app/
  Http/Controllers/       # Controller dan otorisasi HTTP
  Http/Requests/          # Validasi request
  Services/               # Logika layanan dan statistik
  Models/                 # Model data
resources/views/          # Template Blade
public/css/               # Stylesheet aplikasi
public/js/                # JavaScript aplikasi
public/images/            # Aset gambar
routes/                   # Definisi route
config/                   # Konfigurasi Laravel
database/                 # Migrasi dan komponen database
tests/                    # Pengujian aplikasi
docs/refactoring/         # Dokumentasi audit/refactoring
archive/                  # Aset historis, bukan runtime
```

## Kualitas kode dan pengujian

Proyek menyediakan konfigurasi Laravel Pint (`pint.json`), Blade Formatter (`.bladeformatterrc.json`), dan Prettier (`.prettierrc.json`). Contoh pemeriksaan sintaks dan tes:

```bash
php artisan test
php vendor/bin/pint --test --config=pint.json app routes config bootstrap/app.php bootstrap/providers.php tests
```

Jalankan pengujian pada **lingkungan atau database terisolasi**, bukan pada database produksi. Perintah dan dependensi yang tersedia dapat berbeda antar lingkungan.

## Status dan batasan pengujian

Proyek masih dalam pengembangan dan validasi. Dokumentasi refactoring mencatat adanya masalah pada baseline pengujian serta pekerjaan lanjutan yang belum diselesaikan. **README ini tidak menyatakan aplikasi sudah siap produksi** atau bahwa seluruh alur admin–pengguna telah lulus pengujian end-to-end.

Rincian audit dan batas pengujian tersedia di [`docs/refactoring/09-finalisasi.md`](docs/refactoring/09-finalisasi.md).

## Tim dan kontribusi

FindBack Kampus merupakan proyek kolaborasi tim. Kontribusi pengembangan dapat ditelusuri melalui [riwayat commit](https://github.com/alaminazra08/findback-kampus/commits/main/) dan [halaman contributors](https://github.com/alaminazra08/findback-kampus/graphs/contributors).

| Kontributor | Akun GitHub |
| --- | --- |
| Al Amin Azra | [@alaminazra08](https://github.com/alaminazra08) |
| Pisa Kesuma Dewi | [@pisadewi](https://github.com/pisadewi) |
| Nazwa | [@nazwa8067-rgb](https://github.com/nazwa8067-rgb) |
| Anggota kolaborator lainnya | Lihat halaman contributors |

> Identitas kontributor mengikuti riwayat Git yang telah diperbaiki. Peran dan rincian pembagian tugas dapat dilengkapi setelah disetujui seluruh tim.

## Keamanan dan penggunaan data

Jangan mengunggah `.env`, kredensial, database pribadi, unggahan pengguna, atau backup ke repository publik. Gunakan data pengujian yang aman dan verifikasi hak akses sebelum penerapan nyata.

---

<div align="center">

**FindBack Kampus** · Fakultas Sains dan Teknologi · UIN Imam Bonjol Padang

*Helping lost items find their way back.*

</div>
