<?php

use App\Http\Controllers\Admin\ClaimController as AdminClaimController;
// ============================================================
// AUTHENTICATION CONTROLLERS
// ============================================================

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\FoundReportController as AdminFoundReportController;
use App\Http\Controllers\Admin\ItemController as AdminItemController;
use App\Http\Controllers\Admin\LostReportController as AdminLostReportController;
// ============================================================
// USER CONTROLLERS
// ============================================================

use App\Http\Controllers\Admin\MatchingController as AdminMatchingController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\ReportVerificationController as AdminReportVerificationController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\StatisticsController as AdminStatisticsController;
// ============================================================
// ADMIN CONTROLLERS
// ============================================================

use App\Http\Controllers\Admin\UserManagementController as AdminUserManagementController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\User\FoundReportController as UserFoundReportController;
use App\Http\Controllers\User\ItemController as UserItemController;
use App\Http\Controllers\User\LostReportController;
use App\Http\Controllers\User\MyClaimController;
use App\Http\Controllers\User\MyReportController;
use Illuminate\Support\Facades\Route;

// ============================================================
// 01. PUBLIC ROUTES
// ============================================================

Route::get('/', function () {
    return view('welcome');
})->name('home');

// ============================================================
// 02. GUEST ROUTES
// ============================================================

Route::middleware('guest')->group(function () {
    // REGISTER
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');

    Route::post('/register', [AuthController::class, 'register'])->name('register.store');

    // LOGIN
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');

    Route::post('/login', [AuthController::class, 'login'])->name('login.store');

    // GOOGLE OAUTH
    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('google.redirect');

    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name(
        'google.callback',
    );

    // FORGOT PASSWORD
    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name(
        'password.request',
    );

    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.email');

    // RESET PASSWORD
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name(
        'password.reset',
    );

    Route::post('/reset-password', [ResetPasswordController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.update');
});

// ============================================================
// 03. AUTHENTICATED ROUTES
// ============================================================

Route::middleware('auth')->group(function () {
    // LOGOUT ADMIN DAN USER
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// ============================================================
// 04. USER ROUTES
// ============================================================

Route::middleware(['auth', 'role:user'])->group(function () {
    // --------------------------------------------------------
    // 01. DASHBOARD USER
    // --------------------------------------------------------

    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

    // --------------------------------------------------------
    // 02. CARI BARANG
    // --------------------------------------------------------

    Route::get('/cari-barang', [UserItemController::class, 'index'])->name('user.items.index');

    // --------------------------------------------------------
    // 03. DETAIL BARANG
    // --------------------------------------------------------

    Route::get('/cari-barang/{item}', [UserItemController::class, 'show'])
        ->whereNumber('item')
        ->name('user.items.show');

   // --------------------------------------------------------
    // 04. LAPORKAN BARANG HILANG
    // --------------------------------------------------------

    Route::get('/laporkan-barang-hilang', [LostReportController::class, 'create'])->name(
        'user.reports.lost.create',
    );

    Route::post('/laporkan-barang-hilang', [LostReportController::class, 'store'])->name(
        'user.reports.lost.store',
    );

    Route::get('/laporkan-barang-hilang/tambah', [LostReportController::class, 'form'])->name(
        'user.reports.lost.form',
    );

    // --------------------------------------------------------
    // 05. LAPORKAN BARANG DITEMUKAN
    // --------------------------------------------------------

    Route::get('/laporkan-barang-ditemukan', [UserFoundReportController::class, 'create'])->name(
        'user.reports.found.create',
    );

    Route::post('/laporkan-barang-ditemukan', [UserFoundReportController::class, 'store'])->name(
        'user.reports.found.store',
    );

    Route::get('/laporkan-barang-ditemukan/tambah', [UserFoundReportController::class, 'form'])->name(
        'user.reports.found.form',
    );
    
    // --------------------------------------------------------
    // 06. LAPORAN SAYA
    // --------------------------------------------------------

    Route::get('/laporan-saya', [MyReportController::class, 'index'])->name('user.reports.index');
});
// --------------------------------------------------------
// 07. KLAIM BARANG SAYA
// --------------------------------------------------------

Route::get('/klaim-saya', [MyClaimController::class, 'index'])
    ->middleware(['auth', 'role:user'])
    ->name('user.claims.index');

Route::get('/klaim-saya/{claim}', [MyClaimController::class, 'show'])
    ->whereNumber('claim')
    ->middleware(['auth', 'role:user'])
    ->name('user.claims.show');

// ============================================================
// 05. ADMIN ROUTES
// ============================================================

Route::middleware(['auth', 'role:admin', 'admin-tab'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // ----------------------------------------------------
        // 01. NOTIFIKASI
        // ----------------------------------------------------

        Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');

        Route::get('/notifications/stats', [AdminNotificationController::class, 'stats'])
            ->name('notifications.stats');

        Route::get('/notifications/feed', [AdminNotificationController::class, 'feed'])
            ->name('notifications.feed');

        Route::post('/notifications/read-all', [
            AdminNotificationController::class,
            'markAllAsRead',
        ])->name('notifications.read-all');

        Route::patch('/notifications/{id}/read', [AdminNotificationController::class, 'markAsRead'])
            ->whereUuid('id')
            ->name('notifications.read');

        // ----------------------------------------------------
        // 02. DASHBOARD ADMIN
        // ----------------------------------------------------

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // ----------------------------------------------------
        // 03. DATA BARANG
        // ----------------------------------------------------

        Route::get('/reports/items', [AdminItemController::class, 'index'])->name('items.index');
        // Kompatibilitas tautan lama, pertahankan seluruh query filter.
        Route::get('/items', function (\Illuminate\Http\Request $request) {
            return redirect()->route('admin.items.index', $request->query(), 302);
        });

        // Admin menambah barang hilang/ditemukan dan langsung mempublikasikannya.
        Route::post('/items', [AdminItemController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('items.store');

        Route::get('/items/{item}', [AdminItemController::class, 'show'])->name('items.show');

        // ----------------------------------------------------
        // 04. LAPORAN BARANG HILANG
        // ----------------------------------------------------

        Route::get('/reports/lost', [AdminLostReportController::class, 'index'])->name(
            'reports.lost',
        );

        // ----------------------------------------------------
        // 05. LAPORAN BARANG DITEMUKAN
        // ----------------------------------------------------

        Route::get('/reports/found', [AdminFoundReportController::class, 'index'])->name(
            'reports.found',
        );

        // ----------------------------------------------------
        // 06. VERIFIKASI LAPORAN
        // ----------------------------------------------------

        Route::get('/reports/verification', [
            AdminReportVerificationController::class,
            'index',
        ])->name('reports.verification');

        Route::get('/reports/verification/{item}', [
            AdminReportVerificationController::class,
            'show',
        ])->name('reports.verification.show');

        // Permintaan revisi laporan
        Route::post('/reports/verification/{item}/revision', [
            AdminReportVerificationController::class,
            'requestRevision',
        ])->name('reports.verification.revision');

        // Pembatalan permintaan revisi
        Route::post('/reports/verification/{item}/revision/cancel', [
            AdminReportVerificationController::class,
            'cancelRevision',
        ])->name('reports.verification.revision.cancel');

        // Persetujuan atau penolakan laporan
        Route::post('/reports/verification/{item}/review', [
            AdminReportVerificationController::class,
            'review',
        ])
            ->middleware('throttle:10,1')
            ->name('reports.verification.review');

        // ----------------------------------------------------
        // 07. KLAIM BARANG
        // ----------------------------------------------------

        Route::get('/claims', [AdminClaimController::class, 'index'])->name('claims.index');

        Route::get('/claims/{claim}', [AdminClaimController::class, 'show'])->name('claims.show');

        // Persetujuan atau penolakan klaim
        Route::post('/claims/{claim}/review', [AdminClaimController::class, 'review'])
            ->middleware('throttle:10,1')
            ->name('claims.review');

        // Dokumen serah terima PDF
        Route::get('/claims/{claim}/handover/pdf', [AdminClaimController::class, 'handoverPdf'])
            ->middleware('throttle:10,1')
            ->name('claims.handover.pdf');

        // Bukti serah terima
        Route::get('/claims/{claim}/handover/evidence', [
            AdminClaimController::class,
            'handoverEvidence',
        ])
            ->middleware('throttle:20,1')
            ->name('claims.handover.evidence');

        // Konfirmasi pengembalian barang
        Route::post('/claims/{claim}/return', [AdminClaimController::class, 'markReturned'])
            ->middleware('throttle:10,1')
            ->name('claims.return');

        // ----------------------------------------------------
        // 08. PENCOCOKAN BARANG
        // ----------------------------------------------------

        Route::get('/matching', [AdminMatchingController::class, 'index'])->name('matching.index');

        // Generate pencocokan otomatis
        Route::post('/matching/generate', [AdminMatchingController::class, 'generate'])
            ->middleware('throttle:3,1')
            ->name('matching.generate');

        // Detail pencocokan
        Route::get('/matching/{match}', [AdminMatchingController::class, 'show'])->name(
            'matching.show',
        );

        // Review hasil pencocokan
        Route::post('/matching/{match}/review', [AdminMatchingController::class, 'review'])
            ->middleware('throttle:10,1')
            ->name('matching.review');

        // ----------------------------------------------------
        // 09. LAPORAN DAN STATISTIK
        // ----------------------------------------------------

        // Export PDF
        Route::get('/statistics/export/pdf', [AdminStatisticsController::class, 'downloadPdf'])
            ->middleware('throttle:10,1')
            ->name('statistics.export.pdf');

        // Export Board
        Route::get('/statistics/export/board', [AdminStatisticsController::class, 'downloadBoard'])
            ->middleware('throttle:10,1')
            ->name('statistics.export.board');

        // Export CSV
        Route::get('/statistics/export/csv', [AdminStatisticsController::class, 'exportCsv'])
            ->middleware('throttle:10,1')
            ->name('statistics.export.csv');

        // Print laporan
        Route::get('/statistics/print', [AdminStatisticsController::class, 'printReport'])
            ->middleware('throttle:10,1')
            ->name('statistics.print');

        // Dashboard statistik
        Route::get('/statistics', [AdminStatisticsController::class, 'index'])->name(
            'statistics.index',
        );

        // ----------------------------------------------------
        // 10. MANAJEMEN AKUN
        // ----------------------------------------------------

        Route::view('/profil', 'admin.users.profile')->name('profile.index');

        // Daftar pengguna
        Route::get('/users', [AdminUserManagementController::class, 'index'])->name('users.index');

        Route::get('/users/{user}/foto-profil', [AdminUserManagementController::class, 'photo'])
            ->name('users.photo');
        Route::post('/users/foto-profil', [\App\Http\Controllers\User\CampusController::class, 'updateAdminPhoto'])
            ->middleware('throttle:10,1')
            ->name('users.profile-photo.update');

        // Detail pengguna
        Route::get('/users/{user}', [AdminUserManagementController::class, 'show'])->name(
            'users.show',
        );

        // Perubahan role pengguna
        Route::patch('/users/{user}/role', [AdminUserManagementController::class, 'updateRole'])
            ->middleware('throttle:10,1')
            ->name('users.update-role');

        // ----------------------------------------------------
        // 11. PENGATURAN SISTEM
        // ----------------------------------------------------

        Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings.index');

        Route::put('/settings', [AdminSettingsController::class, 'update'])
            ->middleware('throttle:10,1')
            ->name('settings.update');
    });

/*
|--------------------------------------------------------------------------
| FINDBACK CHAT - USER
|--------------------------------------------------------------------------
*/


Route::middleware(['auth', 'role:user'])
    ->prefix('chat')
    ->name('user.chat.')
    ->group(function () {

        // Halaman utama percakapan
        Route::get(
            '/',
            [\App\Http\Controllers\User\ChatController::class, 'index']
        )->name('index');

        // Mengirim pesan
        Route::post(
            '/send',
            [\App\Http\Controllers\User\ChatController::class, 'store']
        )
            ->middleware('throttle:30,1')
            ->name('store');

        // Mengedit pesan
        Route::patch(
            '/messages/{message}',
            [\App\Http\Controllers\User\ChatController::class, 'update']
        )
            ->whereNumber('message')
            ->middleware('throttle:30,1')
            ->name('messages.update');

        // Menarik pesan untuk semua
        Route::patch(
            '/messages/{message}/recall',
            [\App\Http\Controllers\User\ChatController::class, 'recall']
        )
            ->whereNumber('message')
            ->middleware('throttle:30,1')
            ->name('messages.recall');

        // Menyembunyikan pesan dari pengguna
        Route::patch(
            '/messages/{message}/hide',
            [\App\Http\Controllers\User\ChatController::class, 'hide']
        )
            ->whereNumber('message')
            ->middleware('throttle:30,1')
            ->name('messages.hide');
    });


/*
|--------------------------------------------------------------------------
| FINDBACK CHAT - ADMIN
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin', 'admin-tab'])
    ->prefix('admin/chat')
    ->name('admin.chat.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ChatController::class, 'index'])->name(
            'index',
        );

        Route::get('/{user}', [\App\Http\Controllers\Admin\ChatController::class, 'show'])
            ->whereNumber('user')
            ->name('show');

        Route::patch('/messages/{message}/recall', [\App\Http\Controllers\Admin\ChatController::class, 'recall'])
            ->whereNumber('message')->name('recall');

        Route::patch('/messages/{message}/hide', [\App\Http\Controllers\Admin\ChatController::class, 'hide'])
            ->whereNumber('message')->name('hide');

        Route::post('/{user}/send', [\App\Http\Controllers\Admin\ChatController::class, 'store'])
            ->whereNumber('user')
            ->middleware('throttle:30,1')
            ->name('store');
    });
// ============================================================
// FINDBACK LIVE CHAT - AUTO REFRESH
// ============================================================

Route::middleware(['auth', 'role:user'])
    ->match(['GET', 'POST'], '/chat/messages', [\App\Http\Controllers\User\ChatController::class, 'messages'])
    ->name('user.chat.messages');

Route::middleware(['auth', 'role:admin', 'admin-tab'])
    ->match(['GET', 'POST'], '/admin/chat/{user}/messages', [
        \App\Http\Controllers\Admin\ChatController::class,
        'messages',
    ])
    ->name('admin.chat.messages');

// User campus experience. Existing admin routes are unchanged.
Route::middleware(['auth', 'role:user'])->group(function () {
    Route::post('/laporan-saya/{report}/perbaikan', [
        \App\Http\Controllers\User\ReportRevisionController::class,
        'update',
    ])
        ->whereNumber('report')
        ->middleware('throttle:6,1')
        ->name('user.reports.revision');
    Route::get('/laporan-saya/{report}', [
        \App\Http\Controllers\User\CampusController::class,
        'report',
    ])
        ->whereNumber('report')
        ->name('user.reports.show');
    Route::get('/cari-barang/{item}/klaim', [
        \App\Http\Controllers\User\CampusController::class,
        'claim',
    ])
        ->whereNumber('item')
        ->name('user.claims.create');
    Route::post('/cari-barang/{item}/klaim', [
        \App\Http\Controllers\User\CampusController::class,
        'storeClaim',
    ])
        ->whereNumber('item')
        ->middleware('throttle:6,1')
        ->name('user.claims.store');
    Route::get('/notifikasi', [
        \App\Http\Controllers\User\CampusController::class,
        'notifications',
    ])->name('user.notifications.index');
    Route::get('/notifikasi/{id}/buka', [
        \App\Http\Controllers\User\CampusController::class,
        'openNotification',
    ])
        ->whereUuid('id')
        ->name('user.notifications.open');
    Route::post('/notifikasi/read-all', [
        \App\Http\Controllers\User\CampusController::class,
        'readAll',
    ])->name('user.notifications.readAll');
    Route::post('/notifikasi/{id}/read', [
        \App\Http\Controllers\User\CampusController::class,
        'read',
    ])
        ->whereUuid('id')
        ->name('user.notifications.read');
    // Halaman utama tab Profil berisi pusat menu akun.
    // Detail profil dan keamanan akun memiliki URL tersendiri.
    Route::get('/kelola-profil/foto', [
        \App\Http\Controllers\User\CampusController::class,
        'profilePhoto',
    ])->name('user.profile.photo');

    // Navbar bawah "Akun" menuju pusat menu akun.
    Route::get('/akun', function () {
        return view('user.account.index');
    })->name('user.account.index');

    // Alihkan URL lama agar bookmark/link lama tetap menuju halaman Akun.
    Route::redirect('/profil', '/akun', 302);

    // Menu "Kelola Profil" membuka detail profil pengguna.
    Route::get('/kelola-profil', [
        \App\Http\Controllers\User\CampusController::class,
        'profile',
    ])->name('user.profile.index');

    Route::get('/kelola-profil/edit', [
        \App\Http\Controllers\User\CampusController::class,
        'editProfile',
    ])->name('user.profile.edit');

    Route::post('/kelola-profil', [
        \App\Http\Controllers\User\CampusController::class,
        'updateProfile',
    ])->middleware('throttle:10,1')->name('user.profile.update');

    // Menu "Kelola Akun" membuka pengaturan keamanan/kata sandi.
    Route::get('/kelola-akun', function () {
        return view('user.account.security');
    })->name('user.account.security');

    Route::post('/kelola-akun/password', [
        \App\Http\Controllers\User\CampusController::class,
        'password',
    ])->middleware('throttle:6,1')->name('user.profile.password');
});
