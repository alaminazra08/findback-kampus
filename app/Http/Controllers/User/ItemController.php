<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\LostFoundItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    /**
     * Menampilkan daftar barang yang sudah diverifikasi.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', 'in:lost,found'],
            'location' => ['nullable', 'string', 'max:100'],
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        // Hanya barang yang sudah diverifikasi admin.
        $baseQuery = LostFoundItem::query()->where('status', 'verified');

        // Statistik keseluruhan barang terverifikasi.
        $totalItems = (clone $baseQuery)->count();

        $totalLost = (clone $baseQuery)->where('type', 'lost')->count();

        $totalFound = (clone $baseQuery)->where('type', 'found')->count();

        // Query pencarian.
        $query = clone $baseQuery;

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['location'])) {
            $query->where('location', 'like', '%' . $filters['location'] . '%');
        }
        if (!empty($filters['date'])) {
            $query->whereDate('event_date', $filters['date']);
        }
        $items = $query->latest('created_at')->paginate(12)->withQueryString();

        return view(
            'user.items.index',
            compact('items', 'filters', 'totalItems', 'totalLost', 'totalFound'),
        );
    }

    /**
     * Detail barang yang sudah diverifikasi.
     */
    public function show(LostFoundItem $item): View
    {
        abort_unless($item->status === 'verified', 404);

        return view('user.items.show', compact('item'));
    }
}
