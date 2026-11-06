# Tahap 2 — Folder dan arsip

## File yang diperiksa

- backup-sidebar-premium-20261030-212515/resources/views/admin/dashboard.blade.php
- backup-sidebar-premium-20261030-212515/resources/views/admin/layouts/items.blade.php
- group(function
- id())
- perbaiki-sidebar-dashboard.ps1
- powershell
- public/css/admin/claim-detail-responsive-v14-backup.css
- public/css/admin/claim-layout-gap-v9-backup.css
- resources/views/admin/dashboard.blade.php.backup-20261030-212135
- resources/views/admin/items/show.blade.php.backup-20261102-001204
- setup-admin.ps1
- upgrade-sidebar-premium.ps1

## File yang diperbaiki/ditambahkan

- .gitignore

## File yang dipindahkan

- resources/views/admin/dashboard.blade.php.backup-20261030-212135 → archive/legacy-views/admin/dashboard.blade.php.backup-20261030-212135 — Not a .blade.php view and no source reference; retained byte-for-byte outside runtime view tree.
- resources/views/admin/items/show.blade.php.backup-20261102-001204 → archive/legacy-views/admin/items/show.blade.php.backup-20261102-001204 — Not a .blade.php view and no source reference; retained byte-for-byte outside runtime view tree.
- public/css/admin/claim-detail-responsive-v14-backup.css → archive/legacy-assets/css/admin/claim-detail-responsive-v14-backup.css — No reference in runtime source; retained outside public assets.
- public/css/admin/claim-layout-gap-v9-backup.css → archive/legacy-assets/css/admin/claim-layout-gap-v9-backup.css — No reference in runtime source; retained outside public assets.
- backup-sidebar-premium-20261030-212515/resources/views/admin/dashboard.blade.php → archive/legacy-sidebar-backup/resources/views/admin/dashboard.blade.php — Historical root backup; no runtime reference; bytes preserved.
- backup-sidebar-premium-20261030-212515/resources/views/admin/layouts/items.blade.php → archive/legacy-sidebar-backup/resources/views/admin/layouts/items.blade.php — Historical root backup; no runtime reference; bytes preserved.
- group(function → archive/legacy-tools/group(function — Empty shell artifact
- id()) → archive/legacy-tools/id()) — Empty shell artifact
- powershell → archive/legacy-tools/powershell — Empty shell artifact
- setup-admin.ps1 → archive/legacy-tools/setup-admin.ps1 — Historical one-off installer; no runtime source reference. Retained byte-for-byte.
- perbaiki-sidebar-dashboard.ps1 → archive/legacy-tools/perbaiki-sidebar-dashboard.ps1 — Historical one-off installer; no runtime source reference. Retained byte-for-byte.
- upgrade-sidebar-premium.ps1 → archive/legacy-tools/upgrade-sidebar-premium.ps1 — Historical one-off installer; no runtime source reference. Retained byte-for-byte.

## File yang dihapus

Tidak ada file asli yang dihapus. Arsip dan backup asli dipertahankan.

## Pengujian

Pencarian referensi pada app, route, view, konfigurasi, JavaScript, README dan manifest dependensi. Hash setiap file tujuan arsip harus identik dengan sumber ZIP. Folder backup asli tidak dipindah/ditimpa.

## Masalah tersisa

Stylesheet bernomor versi yang masih aktif dipertahankan; nama versi tidak membuktikan file sudah tidak digunakan.
