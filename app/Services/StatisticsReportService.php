<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\{DB, Schema};

class StatisticsReportService
{
    public function since(string $period): ?Carbon
    {
        return $period === 'all'
            ? null
            : now()
                ->subDays((int) $period - 1)
                ->startOfDay();
    }

    public function itemsForPeriod(string $period): \Illuminate\Database\Query\Builder
    {
        $since = $this->since($period);
        return DB::table('lost_found_items')
            ->whereIn('status', ['verified', 'claimed', 'returned'])
            ->when($since !== null, fn ($query) => $query->where('created_at', '>=', $since));
    }

    public function reportData(string $period): array
    {
        $since = $this->since($period);
        $items = $this->itemsForPeriod($period);

        $itemCounts = (clone $items)
            ->selectRaw('status, type, COUNT(*) AS total')
            ->groupBy('status', 'type')
            ->get();

        $total = $itemCounts->sum('total');
        $lost = $itemCounts->where('type', 'lost')->sum('total');
        $found = $itemCounts->where('type', 'found')->sum('total');
        $statuses = $itemCounts->groupBy('status')
            ->map(fn ($rows) => $rows->sum('total'))
            ->all();

        // ====================================================
        // 03. STATISTIK KLAIM BARANG
        // ====================================================

        $claimTotal = 0;
        $claimStatuses = [];

        if (Schema::hasTable('item_claims')) {
            $claims = DB::table('item_claims');

            if ($since !== null) {
                $claims->where('created_at', '>=', $since);
            }

            $claimStatuses = $claims
                ->select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status')
                ->all();
            $claimTotal = array_sum($claimStatuses);
        }

        // ====================================================
        // 04. STATISTIK PENCOCOKAN BARANG
        // ====================================================

        $matchTotal = 0;
        $matchStatuses = [];

        if (Schema::hasTable('item_matches')) {
            $matches = DB::table('item_matches');

            if ($since !== null) {
                $matches->where('created_at', '>=', $since);
            }

            $matchStatuses = $matches
                ->select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status')
                ->all();
            $matchTotal = array_sum($matchStatuses);
        }

        // ====================================================
        // 05. GRAFIK TREN LAPORAN
        // ====================================================

        // Untuk periode:
        // 7 hari   = 7 titik harian
        // 30 hari  = 30 titik harian
        // 90 hari  = 90 titik harian
        // 365 hari = 12 titik bulanan
        // all      = tren 30 hari terakhir

        $monthly = $period === '365';

        $days = match ($period) {
            '7' => 7,
            '90' => 90,
            '365' => 12,
            default => 30,
        };

        $trendStart = $monthly
            ? now()->startOfMonth()->subMonths(11)
            : now()
                ->startOfDay()
                ->subDays($days - 1);

        $trendRows = DB::table('lost_found_items')
            ->whereIn('status', ['verified', 'claimed', 'returned'])
            ->where('created_at', '>=', $trendStart)
            ->whereIn('type', ['lost', 'found'])
            ->selectRaw('type, DATE(created_at) AS report_day, COUNT(*) AS total')
            ->groupBy('type', DB::raw('DATE(created_at)'))
            ->get();

        $bucket = [];

        foreach ($trendRows as $row) {
            $key = $monthly ? substr($row->report_day, 0, 7) : $row->report_day;
            $bucket[$key][$row->type] = ($bucket[$key][$row->type] ?? 0) + (int) $row->total;
        }

        $trend = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = $monthly
                ? now()->startOfMonth()->subMonths($i)
                : now()->startOfDay()->subDays($i);

            $key = $monthly ? $date->format('Y-m') : $date->format('Y-m-d');

            $trend[] = [
                'label' => $monthly
                    ? $date->locale('id')->translatedFormat('M y')
                    : $date->format('d/m'),

                'lost' => $bucket[$key]['lost'] ?? 0,

                'found' => $bucket[$key]['found'] ?? 0,
            ];
        }

        // ====================================================
        // 06. KATEGORI BARANG TERBANYAK
        // ====================================================

        $topCategories = (clone $items)
            ->select('category', DB::raw('COUNT(*) as total'))
            ->whereNotNull('category')
            ->where('category', '<>', '')
            ->groupBy('category')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        // ====================================================
        // 07. LOKASI LAPORAN TERBANYAK
        // ====================================================

        $topLocations = (clone $items)
            ->select('location', DB::raw('COUNT(*) as total'))
            ->whereNotNull('location')
            ->where('location', '<>', '')
            ->groupBy('location')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        // ====================================================
        // 08. LAPORAN TERBARU
        // ====================================================

        // Sumber nama barang konsisten dengan seluruh modul admin.

        $recent = (clone $items)
            ->select('id', 'title', 'type', 'status', 'location', 'created_at', 'photo_path')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        // ====================================================
        // 09. KIRIM DATA KE HALAMAN STATISTIK
        // ====================================================

        return compact(
            'period',
            'total',
            'lost',
            'found',
            'statuses',
            'claimTotal',
            'claimStatuses',
            'matchTotal',
            'matchStatuses',
            'trend',
            'topCategories',
            'topLocations',
            'recent',
        );
    }
}
