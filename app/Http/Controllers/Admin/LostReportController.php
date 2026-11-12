<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LostFoundItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class LostReportController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:pending,verified,rejected,claimed,returned'],
        ]);

        $query = LostFoundItem::query()->where('type', 'lost')->with('reporter:id,name,email');

        $search = trim($filters['search'] ?? '');
        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('location', 'like', '%' . $search . '%')
                    ->orWhere('category', 'like', '%' . $search . '%');
                if (ctype_digit($search)) {
                    $q->orWhere('id', (int) $search);
                }
            });
        }
        if ($status = $filters['status'] ?? null) {
            $query->where('status', $status);
        }

        $counts = LostFoundItem::query()
            ->where('type', 'lost')
            ->selectRaw('status, COUNT(*) AS total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.reports.lost.index', [
            'items' => $query->latest()->paginate(10)->withQueryString(),
            'total' => $counts->sum(),
            'pending' => (int) $counts->get('pending', 0),
            'verified' => (int) $counts->get('verified', 0),
            'returned' => (int) $counts->get('returned', 0),
        ]);
    }
}
