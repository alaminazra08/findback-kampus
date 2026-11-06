# Tahap 5 — Controller, Model dan Service

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
- app/Models/AppSetting.php
- app/Models/ChatMessage.php
- app/Models/ClaimEvidenceRequest.php
- app/Models/ItemClaim.php
- app/Models/ItemMatch.php
- app/Models/LostFoundItem.php
- app/Models/ReportAuditEvent.php
- app/Models/ReportRevisionRequest.php
- app/Models/User.php
- app/Notification/AdminMessageNotification.php
- app/Notification/ReportReviewResultNotification.php
- app/Notifications/AdminMessageNotification.php
- app/Notifications/ClaimEvidenceRequestNotification.php
- app/Notifications/ClaimReviewResultNotification.php
- app/Notifications/HandoverResultNotification.php
- app/Notifications/ReportReviewResultNotification.php
- app/Notifications/ReportRevisionNotification.php
- app/Notifications/UserClaimStatusNotification.php
- app/Observers/ItemClaimObserver.php
- app/Observers/LostFoundItemObserver.php
- app/Providers/AppServiceProvider.php
- app/Services/ClaimHandoverService.php
- app/Services/MatchingScoreService.php
- app/Services/ReportSubmissionService.php
- app/Services/StatisticsReportService.php
- app/Support/AdminAlerts.php
- app/Support/AdminSettings.php
- app/Support/FindBackReportAnalytics.php
- app/Support/ReportAudit.php
- app/Support/WorkflowStatus.php

## File yang diperbaiki/ditambahkan

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
- app/Models/ClaimEvidenceRequest.php
- app/Models/ItemClaim.php
- app/Models/ItemMatch.php
- app/Models/LostFoundItem.php
- app/Models/ReportAuditEvent.php
- app/Models/ReportRevisionRequest.php
- app/Models/User.php
- app/Notifications/AdminMessageNotification.php
- app/Notifications/ClaimEvidenceRequestNotification.php
- app/Notifications/ClaimReviewResultNotification.php
- app/Notifications/HandoverResultNotification.php
- app/Notifications/ReportReviewResultNotification.php
- app/Notifications/ReportRevisionNotification.php
- app/Notifications/UserClaimStatusNotification.php
- app/Observers/ItemClaimObserver.php
- app/Observers/LostFoundItemObserver.php
- app/Providers/AppServiceProvider.php
- app/Services/ClaimHandoverService.php
- app/Services/MatchingScoreService.php
- app/Services/ReportSubmissionService.php
- app/Services/StatisticsReportService.php
- app/Support/AdminAlerts.php
- app/Support/AdminSettings.php
- app/Support/FindBackReportAnalytics.php
- app/Support/ReportAudit.php
- app/Support/WorkflowStatus.php
- bootstrap/app.php
- bootstrap/providers.php
- config/app.php
- config/auth.php
- config/cache.php
- config/database.php
- config/filesystems.php
- config/logging.php
- config/mail.php
- config/queue.php
- config/services.php
- config/session.php
- routes/web.php

## File yang dipindahkan

Tidak ada.

## File yang dihapus

Tidak ada file asli yang dihapus. Arsip dan backup asli dipertahankan.

## Pengujian

PSR-12 melalui Pint dengan aturan tambahan keterbacaan. Empat service: ReportSubmissionService, ClaimHandoverService, MatchingScoreService, StatisticsReportService. Dua Form Request: StoreReportRequest dan CompleteHandoverRequest. Hasil validasi, disk upload, lock/transaksi, rumus skor, notifikasi, kontrak route dan status dipertahankan. Tes pengiriman dua jenis laporan, rollback audit/upload dan seluruh tes serah terima dijalankan.

## Masalah tersisa

Controller lain yang kecil tetap dipertahankan agar tidak membuat service/DTO berlebihan. Wrapper calculateScore pada controller dipertahankan sebagai delegasi kompatibel dengan tes lama. Tidak ada perubahan file migration maupun skema database.
