<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adapter aman. Sesuaikan mapping nama tabel/kolom/status dengan migration asli.
 * Tidak mengarang angka saat skema belum tersedia.
 */
final class FindBackReportAnalytics
{
    public static function snapshot(): array
    {
        $empty = [
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
        ];
        // Default ini sengaja memakai nama umum. Periksa migration sebelum mengaktifkan.
        $table = 'reports';
        $needed = ['id', 'name', 'type', 'status', 'location', 'created_at'];
        if (!Schema::hasTable($table)) {
            return $empty;
        }
        foreach ($needed as $column) {
            if (!Schema::hasColumn($table, $column)) {
                return $empty;
            }
        }

        // Sesuaikan value 'lost', 'found', 'claim', 'returned' dengan enum di database.
        $rows = DB::table($table)->select($needed)->get();
        $lost = $rows->where('type', 'lost');
        $found = $rows->where('type', 'found');
        $days = collect(range(6, 0))->map(fn ($offset) => Carbon::today()->subDays($offset));
        return [
            'available' => true,
            'total' => $rows->count(),
            'lost' => $lost->count(),
            'found' => $found->count(),
            'claim' => $rows->where('status', 'claim')->count(),
            'returned' => $rows->where('status', 'returned')->count(),
            'labels' => $days->map(fn ($day) => $day->translatedFormat('d M'))->all(),
            'lostTrend' => $days
                ->map(
                    fn ($day) => $lost
                        ->filter(fn ($r) => Carbon::parse($r->created_at)->isSameDay($day))
                        ->count(),
                )
                ->all(),
            'foundTrend' => $days
                ->map(
                    fn ($day) => $found
                        ->filter(fn ($r) => Carbon::parse($r->created_at)->isSameDay($day))
                        ->count(),
                )
                ->all(),
            'recent' => $rows
                ->sortByDesc('created_at')
                ->take(5)
                ->map(
                    fn ($r) => [
                        'name' => $r->name,
                        'type' => $r->type,
                        'location' => $r->location,
                        'date' => Carbon::parse($r->created_at)->translatedFormat('d M Y'),
                        'status' => $r->status,
                    ],
                )
                ->values()
                ->all(),
        ];
    }
}
