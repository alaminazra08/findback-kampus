# Tahap 7 — Pengujian dan responsivitas

## File yang diperiksa

- resources/views/admin/chat/index.blade.php
- resources/views/admin/chat/show.blade.php
- resources/views/admin/claims/handover-pdf.blade.php
- resources/views/admin/claims/index.blade.php
- resources/views/admin/claims/show.blade.php
- resources/views/admin/dashboard.blade.php
- resources/views/admin/items/index.blade.php
- resources/views/admin/items/show.blade.php
- resources/views/admin/layouts/items.blade.php
- resources/views/admin/matching/index.blade.php
- resources/views/admin/matching/show.blade.php
- resources/views/admin/partials/account.blade.php
- resources/views/admin/partials/brand.blade.php
- resources/views/admin/partials/chat-link.blade.php
- resources/views/admin/partials/main-nav.blade.php
- resources/views/admin/partials/notification-bell.blade.php
- resources/views/admin/reports/found/index.blade.php
- resources/views/admin/reports/lost/index.blade.php
- resources/views/admin/reports/verification/index.blade.php
- resources/views/admin/reports/verification/show.blade.php
- resources/views/admin/settings/index.blade.php
- resources/views/admin/statistics/board.blade.php
- resources/views/admin/statistics/index.blade.php
- resources/views/admin/statistics/pdf.blade.php
- resources/views/admin/statistics/print.blade.php
- resources/views/admin/users/index.blade.php
- resources/views/admin/users/show.blade.php
- resources/views/auth/forgot-password.blade.php
- resources/views/auth/login.blade.php
- resources/views/auth/register.blade.php
- resources/views/auth/reset-password.blade.php
- resources/views/shared/report-history.blade.php
- resources/views/user/chat/index.blade.php
- resources/views/user/claims/create.blade.php
- resources/views/user/claims/index.blade.php
- resources/views/user/components/card.blade.php
- resources/views/user/components/icon.blade.php
- resources/views/user/components/pages.blade.php
- resources/views/user/components/status.blade.php
- resources/views/user/dashboard.blade.php
- resources/views/user/items/index.blade.php
- resources/views/user/items/show.blade.php
- resources/views/user/layouts/campus.blade.php
- resources/views/user/notifications/index.blade.php
- resources/views/user/partials/premium-icon.blade.php
- resources/views/user/partials/premium-shell.blade.php
- resources/views/user/profile/index.blade.php
- resources/views/user/reports/form.blade.php
- resources/views/user/reports/found-create.blade.php
- resources/views/user/reports/index.blade.php
- resources/views/user/reports/lost-create.blade.php
- resources/views/user/reports/lost/create.blade.php
- resources/views/user/reports/show.blade.php
- resources/views/welcome.blade.php
- tests/Feature/AdminFoundationTest.php
- tests/Feature/AdminSettingsAccountRegressionTest.php
- tests/Feature/AdminWorkflowTest.php
- tests/Feature/CampusExperienceTest.php
- tests/Feature/ClaimEvidenceRequestTest.php
- tests/Feature/ClaimEvidenceResponseCycleTest.php
- tests/Feature/ClaimHandoverTest.php
- tests/Feature/ExampleTest.php
- tests/Feature/PremiumUserTest.php
- tests/Feature/RefactorContractTest.php
- tests/Feature/RefactorRenderTest.php
- tests/Feature/ReportCycleTest.php
- tests/Feature/ReportRevisionTest.php
- tests/Feature/StatisticsExportTest.php
- tests/Feature/StatisticsPdfTest.php
- tests/TestCase.php
- tests/Unit/ExampleTest.php

## File yang diperbaiki/ditambahkan

- tests/Feature/AdminFoundationTest.php
- tests/Feature/AdminSettingsAccountRegressionTest.php
- tests/Feature/AdminWorkflowTest.php
- tests/Feature/CampusExperienceTest.php
- tests/Feature/ClaimEvidenceRequestTest.php
- tests/Feature/ClaimEvidenceResponseCycleTest.php
- tests/Feature/ClaimHandoverTest.php
- tests/Feature/PremiumUserTest.php
- tests/Feature/RefactorContractTest.php
- tests/Feature/RefactorRenderTest.php
- tests/Feature/ReportCycleTest.php
- tests/Feature/ReportRevisionTest.php
- tests/Feature/StatisticsExportTest.php
- tests/Feature/StatisticsPdfTest.php

## File yang dipindahkan

Tidak ada.

## File yang dihapus

Tidak ada file asli yang dihapus. Arsip dan backup asli dipertahankan.

## Pengujian

Lingkungan: PHP 8.3.6, SQLite in-memory, Chromium headless 131; bukan Windows/XAMPP/MySQL pengguna.

| Pemeriksaan | Hasil |
|---|---|
| Baseline sebelum refactor | 72 tes; 476 assertion; 9 error + 8 failure |
| Setelah refactor | 76 tes; 644 assertion; 9 error + 8 failure yang sama |
| Tes baru khusus refactor | 4 tes lulus, 168 assertion |
| Tes final keseluruhan | 59 lulus, 17 baseline bermasalah; 0 skip; tidak ada tambahan kasus gagal |
| PHP | 111 file diperiksa di lingkungan audit, termasuk cache; lulus sintaks |
| Blade | 74 hasil kompilasi termasuk view dependensi; lulus sintaks |
| Route | 76 kontrak method/URI/name/action/middleware sama dengan baseline |
| Browser | 46 halaman × 10 viewport × sebelum/sesudah; 460 pasangan perbandingan |
| Interaksi | 8 skenario × sebelum/sesudah; seluruh 16 pemeriksaan lulus |
| Reflow | 8 pasangan CSS zoom 200%; bukan bukti zoom browser native |
| Error JavaScript | 0 pada pengujian browser |
| Tambahan overflow | 0 |
| PDF laporan & board | Raster halaman contoh identik sebelum/sesudah; tidak ada piksel berbeda |
| Format | Pint PSR-12, Blade formatter, Prettier lulus |
| Pemasang | Parser PowerShell diperiksa; simulasi lintas platform dicatat terpisah |

Viewport: 320, 360, 375, 390, 430, 768, 1024, 1366, 1440 dan 1920 px. Interaksi: toggle password, menu admin, menu user, modal klaim, pilihan keputusan, fallback foto, wizard laporan dan pembatalan konfirmasi.

92 tangkapan viewport awal diperbandingkan secara internal pada 360 dan 1440 px. Dua halaman mengalami koreksi raw directive yang disebut di tahap 3; perbedaan sangat kecil antialias ikon/font tidak dinyatakan sebagai perubahan desain. Screenshot tidak disertakan sebagai keluaran pengguna.

Overflow awal yang tetap ada:

| Halaman | Viewport | Mode | Lebar sebelum | Lebar sesudah |
|---|---:|---|---:|---:|
| items.html | 320 | 100% | 329 | 329 |
| dashboard | 1440 | CSS 200% | 1539 | 1539 |
| verification-detail | 1440 | CSS 200% | 1447 | 1447 |
| items | 1440 | CSS 200% | 2280 | 2280 |
| user-dashboard | 1440 | CSS 200% | 1486 | 1486 |

## Masalah tersisa

Belum dinyatakan seluruh fitur lulus. Installer menolak jika jumlah/identitas/jenis kegagalan berbeda dari baseline; ini deteksi regresi yang terbatas, bukan sertifikasi bebas bug. Perlu uji native Chrome zoom 200%, Android/iOS, PHP 8.2 di XAMPP, MySQL concurrency, dan perbaikan 17 kasus baseline bersama keputusan alur bisnis. Browser menggunakan HTML hasil render Laravel pada server statis; transaksi dan otorisasi diuji lewat PHPUnit, bukan sesi browser end-to-end ke MySQL.
