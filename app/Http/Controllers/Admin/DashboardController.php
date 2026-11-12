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

        $userCounts = User::query()
            ->selectRaw(
                "COUNT(*) AS total"
                . ", COUNT(CASE WHEN role = 'admin' THEN 1 END) AS admins"
                . ", COUNT(CASE WHEN role = 'user' THEN 1 END) AS regular_users"
            )
            ->first();

        $totalUsers = (int) $userCounts->total;
        $totalAdmins = (int) $userCounts->admins;
        $totalRegularUsers = (int) $userCounts->regular_users;

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
            'pending' => 0,
            'rejected' => 0,
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

            $reportCounts = DB::table('lost_found_items')
                ->selectRaw(
                    "COUNT(CASE WHEN status IN ('verified', 'claimed', 'returned') THEN 1 END) AS total"
                    . ", COUNT(CASE WHEN status IN ('verified', 'claimed', 'returned') AND type = 'lost' THEN 1 END) AS lost"
                    . ", COUNT(CASE WHEN status IN ('verified', 'claimed', 'returned') AND type = 'found' THEN 1 END) AS found"
                    . ", COUNT(CASE WHEN status = 'returned' AND type = 'found' THEN 1 END) AS returned"
                    . ", COUNT(CASE WHEN status = 'pending' THEN 1 END) AS pending"
                    . ", COUNT(CASE WHEN status = 'rejected' THEN 1 END) AS rejected"
                )
                ->first();

            foreach (['total', 'lost', 'found', 'returned', 'pending', 'rejected'] as $metric) {
                $reportAnalytics[$metric] = (int) $reportCounts->{$metric};
            }

            if (Schema::hasTable('item_claims')) {
                $reportAnalytics['claim'] = DB::table('item_claims')
                    ->whereIn('status', ['pending', 'approved'])
                    ->count();
            }

            // =================================================
            // 06. GRAFIK TREN LAPORAN 7 HARI
            // =================================================

            $today = Carbon::now('Asia/Jakarta')->startOfDay();
            $startDate = $today->copy()->subDays(6);
            $databaseTimezone = config('app.timezone');
            $trendQuery = DB::table('lost_found_items')
                ->whereIn('status', ['verified', 'claimed', 'returned'])
                ->whereIn('type', ['lost', 'found'])
                ->where('created_at', '>=', $startDate->copy()->timezone($databaseTimezone))
                ->where('created_at', '<', $today->copy()->addDay()->timezone($databaseTimezone))
                ->select('type')
                ->groupBy('type');

            for ($day = 0; $day < 7; $day++) {
                $date = $startDate->copy()->addDays($day);
                $trendQuery->selectRaw(
                    "COUNT(CASE WHEN created_at >= ? AND created_at < ? THEN 1 END) AS day_{$day}",
                    [
                        $date->copy()->timezone($databaseTimezone),
                        $date->copy()->addDay()->timezone($databaseTimezone),
                    ],
                );
                $reportAnalytics['labels'][] = $date->locale('id')->translatedFormat('d M');
            }

            $trend = $trendQuery->get()->keyBy('type');

            for ($day = 0; $day < 7; $day++) {
                $column = "day_{$day}";
                $reportAnalytics['lostTrend'][] = (int) ($trend->get('lost')->{$column} ?? 0);
                $reportAnalytics['foundTrend'][] = (int) ($trend->get('found')->{$column} ?? 0);
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
