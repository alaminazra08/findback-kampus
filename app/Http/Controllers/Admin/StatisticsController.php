<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    public function __construct(private readonly \App\Services\StatisticsReportService $reports)
    {
    }

    public function index(Request $request)
    {
        return view('admin.statistics.index', $this->reports->reportData($this->period($request)));
    }

    public function exportCsv(Request $request)
    {
        $period = $this->period($request);
        $query = $this->reports->itemsForPeriod($period);
        $maxId = (clone $query)->max('id');
        $filename = 'findback-laporan-barang-' . $period . '-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(
            function () use ($query, $maxId): void {
                $output = fopen('php://output', 'w');
                // UTF-8 BOM and semicolon delimiter for Indonesian Excel installations.
                fwrite($output, "\xEF\xBB\xBFsep=;\r\n");
                fputcsv(
                    $output,
                    [
                        'ID',
                        'Nama Barang',
                        'Jenis',
                        'Kategori',
                        'Lokasi',
                        'Tanggal Kejadian',
                        'Status',
                        'Tanggal Laporan',
                        'Deskripsi',
                    ],
                    ';',
                    '"',
                    '',
                );
                if ($maxId !== null) {
                    $query
                        ->where('id', '<=', $maxId)
                        ->select([
                            'id',
                            'title',
                            'type',
                            'category',
                            'location',
                            'event_date',
                            'status',
                            'created_at',
                            'description',
                        ])
                        ->chunkById(500, function ($rows) use ($output): void {
                            foreach ($rows as $row) {
                                $values = [
                                    $row->id,
                                    $row->title,
                                    $this->typeLabel($row->type),
                                    $row->category,
                                    $row->location,
                                    $row->event_date,
                                    $this->statusLabel($row->status),
                                    $row->created_at,
                                    $row->description,
                                ];
                                fputcsv(
                                    $output,
                                    array_map($this->csvCell(...), $values),
                                    ';',
                                    '"',
                                    '',
                                );
                            }
                        });
                }
                fclose($output);
            },
            $filename,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Cache-Control' => 'private, no-store',
                'X-Content-Type-Options' => 'nosniff',
            ],
        );
    }

    public function printReport(Request $request)
    {
        $period = $this->period($request);
        $data = $this->reports->reportData($period);
        $data['reports'] = $this->reports
            ->itemsForPeriod($period)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();
        $data['settings'] = \App\Support\AdminSettings::all();
        $data['generatedAt'] = now()->timezone('Asia/Jakarta');
        $data['periodLabel'] = [
            '7' => '7 hari',
            '30' => '30 hari',
            '90' => '90 hari',
            '365' => '365 hari (12 bulan)',
            'all' => 'Semua data',
        ][$period];
        $data['since'] = $this->reports->since($period);
        return response()
            ->view('admin.statistics.print', $data)
            ->header('Cache-Control', 'private, no-store');
    }

    public function downloadPdf(Request $request)
    {
        $period = $this->period($request);
        $data = $this->reports->reportData($period);
        $data['reports'] = $this->reports
            ->itemsForPeriod($period)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();
        $data['settings'] = \App\Support\AdminSettings::all();
        $data['generatedAt'] = now()->timezone('Asia/Jakarta');
        $data['periodLabel'] = [
            '7' => '7 hari',
            '30' => '30 hari',
            '90' => '90 hari',
            '365' => '365 hari',
            'all' => 'Semua data',
        ][$period];
        return Pdf::loadView('admin.statistics.pdf', $data)
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'isRemoteEnabled' => false,
                'defaultFont' => 'DejaVu Sans',
                'chroot' => public_path(),
            ])
            ->download(
                'findback-laporan-admin-' . $period . '-' . now()->format('Ymd-His') . '.pdf',
            );
    }

    public function downloadBoard(Request $request)
    {
        $period = $this->period($request);
        $query = $this->reports
            ->itemsForPeriod($period)
            ->where('status', 'verified')
            ->whereIn('type', ['lost', 'found']);
        // Barang yang telah mendapat pemilik tidak lagi diumumkan sebagai tersedia.
        $query->whereNotExists(function ($claims): void {
            $claims
                ->selectRaw('1')
                ->from('item_claims')
                ->whereColumn('item_claims.lost_found_item_id', 'lost_found_items.id')
                ->whereIn('item_claims.status', ['approved', 'returned']);
        });
        $query->whereNotExists(function ($matches): void {
            $matches
                ->selectRaw('1')
                ->from('item_matches')
                ->join('lost_found_items as found', 'found.id', '=', 'item_matches.found_item_id')
                ->whereColumn('item_matches.lost_item_id', 'lost_found_items.id')
                ->where('item_matches.status', 'confirmed')
                ->whereIn('found.status', ['claimed', 'returned']);
        });
        $items = $query
            ->select(['id', 'title', 'type', 'location', 'category', 'event_date', 'photo_path'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
        $items->transform(function ($item) {
            $item->photoData = $this->localPhoto($item->photo_path);
            return $item;
        });
        $pages = $items->chunk(4)->values();
        if ($pages->isEmpty()) {
            $pages = collect([collect()]);
        }
        return Pdf::loadView('admin.statistics.board', [
            'pages' => $pages,
            'total' => $items->count(),
            'settings' => \App\Support\AdminSettings::all(),
            'generatedAt' => now()->timezone('Asia/Jakarta'),
            'periodLabel' => [
                '7' => '7 hari',
                '30' => '30 hari',
                '90' => '90 hari',
                '365' => '365 hari',
                'all' => 'Semua data',
            ][$period],
        ])
            ->setPaper([0, 0, 607.5, 1080])
            ->setOptions([
                'isRemoteEnabled' => false,
                'defaultFont' => 'DejaVu Sans',
                'chroot' => public_path(),
            ])
            ->download('findback-papan-informasi-tv-' . now()->format('Ymd-His') . '.pdf');
    }

    private function localPhoto(?string $path): ?string
    {
        if (!$path) {
            return null;
        }
        $root = realpath(storage_path('app/public'));
        $file = $root ? realpath($root . DIRECTORY_SEPARATOR . ltrim($path, '/\\')) : false;
        if (
            !$file ||
            !str_starts_with($file, $root . DIRECTORY_SEPARATOR) ||
            !is_file($file) ||
            filesize($file) > 8 * 1024 * 1024
        ) {
            return null;
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file);
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/gif'], true)) {
            return null;
        }
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($file));
    }

    private function period(Request $request): string
    {
        $validated = $request->validate(['period' => ['nullable', 'in:7,30,90,365,all']]);
        return (string) ($validated['period'] ?? '30');
    }

    private function typeLabel(string $type): string
    {
        return ['lost' => 'Hilang', 'found' => 'Ditemukan'][$type] ?? $type;
    }

    private function statusLabel(string $status): string
    {
        return \App\Support\WorkflowStatus::report($status);
    }

    private function csvCell(mixed $value): string
    {
        $text = (string) ($value ?? '');
        // Treat spreadsheet formula prefixes as literal text, including leading controls.
        return preg_match('/^[\x00-\x20]*[=+@-]/u', $text) ? "'" . $text : $text;
    }
}
