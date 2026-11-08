<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(): View
    {
        // ====================================================
        // 01. STATISTIK PENGGUNA
        // ====================================================

        $totalUsers = User::count();

        $totalAdmins = User::query()->where('role', 'admin')->count();

        $totalRegularUsers = User::query()->where('role', 'user')->count();

        $recentUsers = User::query()
            ->select(['id', 'name', 'email', 'role', 'created_at'])
            ->latest('created_at')
            ->limit(5)
            ->get();

        // ====================================================
        // 02. STRUKTUR DATA ANALITIK DASHBOARD
        // ====================================================

        $reportAnalytics = [
            'available' => false,
            'total' => 0,
            'lost' => 0,
            'found' => 0,
            'claim' => 0,
            'returned' => 0,
            'labels' => [],
            'lostTrend' => [],
            'foundTrend' => [],
            'recent' => [],
            'updatedAt' => null,
        ];

        // ====================================================
        // 03. PERIKSA TABEL LAPORAN BARANG
        // ====================================================

        if (Schema::hasTable('lost_found_items')) {
            $reportAnalytics['available'] = true;

            // =================================================
            // 04. TOTAL LAPORAN BARANG
            // =================================================

            $reportAnalytics['total'] = DB::table('lost_found_items')
                ->whereIn('status', ['verified', 'claimed', 'returned'])
                ->count();

            $reportAnalytics['lost'] = DB::table('lost_found_items')
                ->where('type', 'lost')
                ->whereIn('status', ['verified', 'claimed', 'returned'])
                ->count();

            $reportAnalytics['found'] = DB::table('lost_found_items')
                ->where('type', 'found')
                ->whereIn('status', ['verified', 'claimed', 'returned'])
                ->count();

            // =================================================
            // 05. STATISTIK KLAIM BARANG
            // =================================================

            if (Schema::hasTable('item_claims')) {
                // Klaim yang masih dalam proses:
                // pending  = menunggu pemeriksaan
                // approved = disetujui, belum diserahkan

                $reportAnalytics['claim'] = DB::table('item_claims')
                    ->whereIn('status', ['pending', 'approved'])
                    ->count();

                // Klaim yang telah selesai dikembalikan
                $reportAnalytics['returned'] = DB::table('item_claims')
                    ->where('status', 'returned')
                    ->count();
            } else {
                // Jika modul klaim belum tersedia,
                // gunakan status pengembalian pada laporan.

                $reportAnalytics['returned'] = DB::table('lost_found_items')
                    ->where('type', 'found')
                    ->where('status', 'returned')
                    ->count();
            }

            // =================================================
            // 06. GRAFIK TREN LAPORAN 7 HARI
            // =================================================

            $startDate = Carbon::now('Asia/Jakarta')->startOfDay()->subDays(6);

            $trendRows = DB::table('lost_found_items')
                ->whereIn('status', ['verified', 'claimed', 'returned'])
                ->select('type', 'created_at')
                ->where('created_at', '>=', $startDate->copy()->timezone(config('app.timezone')))
                ->whereIn('type', ['lost', 'found'])
                ->get();

            $trendBucket = [];

            foreach ($trendRows as $row) {
                if ($row->created_at === null) {
                    continue;
                }

                $dateKey = Carbon::parse($row->created_at)
                    ->timezone('Asia/Jakarta')
                    ->format('Y-m-d');

                if (!isset($trendBucket[$dateKey])) {
                    $trendBucket[$dateKey] = [
                        'lost' => 0,
                        'found' => 0,
                    ];
                }

                if ($row->type === 'lost') {
                    $trendBucket[$dateKey]['lost']++;
                }

                if ($row->type === 'found') {
                    $trendBucket[$dateKey]['found']++;
                }
            }

            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now('Asia/Jakarta')->startOfDay()->subDays($i);

                $key = $date->format('Y-m-d');

                $reportAnalytics['labels'][] = $date->locale('id')->translatedFormat('d M');

                $reportAnalytics['lostTrend'][] = $trendBucket[$key]['lost'] ?? 0;

                $reportAnalytics['foundTrend'][] = $trendBucket[$key]['found'] ?? 0;
            }

            // =================================================
            // 07. LAPORAN BARANG TERBARU
            // =================================================

            // Kolom title menjadi sumber nama barang; alias menjaga kontrak tampilan.

            $recentReports = DB::table('lost_found_items')
                ->whereIn('status', ['verified', 'claimed', 'returned'])
                ->select([
                    'id',
                    'title as name',
                    'photo_path',
                    'type',
                    'location',
                    'status',
                    'created_at',
                ])
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit(5)
                ->get();

            foreach ($recentReports as $report) {
                $reportAnalytics['recent'][] = [
                    'id' => $report->id,
                    'name' => $report->name,
                    'photo' => $report->photo_path,
                    'type' => $report->type,
                    'location' => $report->location ?: 'Tidak dicantumkan',
                    'date' => $report->created_at
                        ? Carbon::parse($report->created_at)
                            ->timezone('Asia/Jakarta')
                            ->locale('id')
                            ->translatedFormat('d M Y')
                        : '—',
                    'status' => $report->status,
                ];
            }

            // =================================================
            // 08. WAKTU PEMBARUAN STATISTIK
            // =================================================

            $reportAnalytics['updatedAt'] = Carbon::now('Asia/Jakarta')->toDateTimeString();
        }

        // Dashboard and statistics count returned goods, not approved claims.
        // A returned found item represents one completed handover, regardless
        // of how many claim records reference it.
        $reportAnalytics['returned'] = Schema::hasTable('lost_found_items')
            ? DB::table('lost_found_items')
                ->where('type', 'found')
                ->where('status', 'returned')
                ->count()
            : 0;
        $reportAnalytics['pending'] = Schema::hasTable('lost_found_items')
            ? DB::table('lost_found_items')->where('status', 'pending')->count()
            : 0;
        // Selaraskan kartu Dashboard dengan tab Verifikasi Laporan.
        // Antrean Verifikasi = pending; Laporan Ditolak = rejected.
        $reportAnalytics['rejected'] = Schema::hasTable('lost_found_items')
            ? DB::table('lost_found_items')->where('status', 'rejected')->count()
            : 0;
        // ====================================================
        // 09. KIRIM DATA KE DASHBOARD ADMIN
        // ====================================================

        return view('admin.dashboard', [
            'totalUsers' => $totalUsers,
            'totalAdmins' => $totalAdmins,
            'totalRegularUsers' => $totalRegularUsers,
            'recentUsers' => $recentUsers,
            'reportAnalytics' => $reportAnalytics,
        ]);
    }
}
