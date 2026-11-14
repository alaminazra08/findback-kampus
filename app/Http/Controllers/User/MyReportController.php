<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\LostFoundItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class MyReportController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', 'in:lost,found'],
            'status' => ['nullable', 'in:pending,verified,rejected,claimed,returned'],
        ]);

        // Data hanya milik pengguna yang sedang login.
        $baseQuery = LostFoundItem::query()
            ->where('user_id', auth()->id())
            ->withExists([
                'revisions as awaiting_revision' => fn ($q) => $q->whereNull('closed_at'),
            ]);

        // Statistik laporan pengguna.
        $totalReports = (clone $baseQuery)->count();

        $lostReports = (clone $baseQuery)->where('type', 'lost')->count();

        $foundReports = (clone $baseQuery)->where('type', 'found')->count();

        $pendingReports = (clone $baseQuery)->where('status', 'pending')->count();

        $verifiedReports = (clone $baseQuery)->where('status', 'verified')->count();

        $rejectedReports = (clone $baseQuery)->where('status', 'rejected')->count();

        // Query daftar laporan.
        $query = clone $baseQuery;

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");

                if (ctype_digit($search)) {
                    $q->orWhere('id', (int) $search);
                }
            });
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $reports = $query->latest()->paginate(10)->withQueryString();

        return view(
            'user.reports.index',
            compact(
                'reports',
                'filters',
                'totalReports',
                'lostReports',
                'foundReports',
                'pendingReports',
                'verifiedReports',
                'rejectedReports',
            ),
        );
    }
}
