# Tahap 6 — Keamanan dan validasi

## File yang diperiksa

- app/Http/Controllers/Admin/ChatController.php
- app/Http/Controllers/Admin/ClaimController.php
- app/Http/Controllers/Admin/DashboardController.php
- app/Http/Controllers/Admin/FoundReportController.php
- app/Http/Controllers/Admin/ItemController.php
- app/Http/Controllers/Admin/LostReportController.php
- app/Http/Controllers/Admin/MatchingController.php
- app/Http/Controllers/Admin/NotificationController.php
- app/Http/Controllers/Admin/ReportVerificationController.php
- app/Http/Controllers/Admin/SettingsController.php
- app/Http/Controllers/Admin/StatisticsController.php
- app/Http/Controllers/Admin/UserManagementController.php
- app/Http/Controllers/Auth/ForgotPasswordController.php
- app/Http/Controllers/Auth/ResetPasswordController.php
- app/Http/Controllers/AuthController.php
- app/Http/Controllers/Controller.php
- app/Http/Controllers/GoogleAuthController.php
- app/Http/Controllers/User/CampusController.php
- app/Http/Controllers/User/ChatController.php
- app/Http/Controllers/User/ClaimEvidenceResponseController.php
- app/Http/Controllers/User/FoundReportController.php
- app/Http/Controllers/User/ItemController.php
- app/Http/Controllers/User/LostReportController.php
- app/Http/Controllers/User/MyClaimController.php
- app/Http/Controllers/User/MyReportController.php
- app/Http/Controllers/User/ReportController.php
- app/Http/Controllers/User/ReportRevisionController.php
- app/Http/Middleware/EnsureUserHasRole.php
- app/Http/Requests/CompleteHandoverRequest.php
- app/Http/Requests/StoreReportRequest.php
- routes/console.php
- routes/web.php

## File yang diperbaiki/ditambahkan

- app/Http/Requests/CompleteHandoverRequest.php
- app/Http/Requests/StoreReportRequest.php
- app/Services/ClaimHandoverService.php
- app/Services/ReportSubmissionService.php

## File yang dipindahkan

Tidak ada.

## File yang dihapus

Tidak ada file asli yang dihapus. Arsip dan backup asli dipertahankan.

## Pengujian

Middleware auth/role, otorisasi pemilik record, validasi upload, pengecekan status dan transaksi tetap mengikuti source. Request baru tidak menerima role/status tambahan di service. Tes backend yang sudah lulus tetap lulus. PDF hanya boleh membaca CSS lokal dalam public; isRemoteEnabled tetap false. Tidak ada ekspresi Blade di file JS statis.

## Masalah tersisa

Risiko terbuka yang sudah ada: (1) bukti kepemilikan dan lampiran chat memakai disk public; memprivatkan file lama butuh migrasi berkas/URL terpisah, (2) keputusan laporan hanya memeriksa pending tanpa token identitas putaran, (3) 17 tes lama gagal/error, (4) kesiapan deployment dan beban/concurrency MySQL belum dibuktikan. Refactoring tidak mengklaim risiko ini selesai.
