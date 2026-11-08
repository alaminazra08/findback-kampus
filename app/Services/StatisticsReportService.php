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

        $total = (clone $items)->count();

        $lost = (clone $items)->where('type', 'lost')->count();

        $found = (clone $items)->where('type', 'found')->count();

        $statuses = (clone $items)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
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

            $claimTotal = (clone $claims)->count();

            $claimStatuses = (clone $claims)
                ->select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status')
                ->all();
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

            $matchTotal = (clone $matches)->count();

            $matchStatuses = (clone $matches)
                ->select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status')
                ->all();
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
            ->select('type', 'created_at')
            ->get();

        $bucket = [];

        foreach ($trendRows as $row) {
            if ($row->created_at === null) {
                continue;
            }

            $date = Carbon::parse($row->created_at);

            $key = $monthly ? $date->format('Y-m') : $date->format('Y-m-d');

            $kind = match ($row->type) {
                'lost' => 'lost',
                'found' => 'found',
                default => null,
            };

            if ($kind !== null) {
                $bucket[$key][$kind] = ($bucket[$key][$kind] ?? 0) + 1;
            }
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
            ->select('id', 'title', 'type', 'status', 'location', 'created_at')
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
