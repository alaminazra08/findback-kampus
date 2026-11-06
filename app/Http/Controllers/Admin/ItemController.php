<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LostFoundItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', 'in:lost,found'],
            'status' => ['nullable', 'in:verified,claimed,returned'],
            'group' => ['nullable', 'in:aktif,selesai,semua'],
        ]);

        $query = LostFoundItem::query()
            ->whereIn('status', ['verified', 'claimed', 'returned'])
            ->with('reporter:id,name,email,role');

        if ($search = trim($filters['search'] ?? '')) {
            $query->where(function ($q) use ($search): void {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('location', 'like', '%' . $search . '%')
                    ->orWhere('category', 'like', '%' . $search . '%')
                    ->orWhere(
                        'id',
                        preg_match('/^(?:BRG-)?(\d+)$/i', $search, $m) ? (int) $m[1] : 0,
                    );
            });
        }

        if ($type = $filters['type'] ?? null) {
            $query->where('type', $type);
        }
        $group = $filters['group'] ?? 'semua';
        if ($group === 'aktif') {
            $query->whereIn('status', ['verified', 'claimed']);
        } elseif ($group === 'selesai') {
            $query->where('status', 'returned');
        }
        if ($status = $filters['status'] ?? null) {
            $query->where('status', $status);
        }

        return view('admin.items.index', [
            'items' => $query->latest()->paginate(10)->withQueryString(),
            'total' => LostFoundItem::whereIn('status', [
                'verified',
                'claimed',
                'returned',
            ])->count(),
            'lost' => LostFoundItem::where('type', 'lost')
                ->whereIn('status', ['verified', 'claimed', 'returned'])
                ->count(),
            'found' => LostFoundItem::where('type', 'found')
                ->whereIn('status', ['verified', 'claimed', 'returned'])
                ->count(),
            'pending' => LostFoundItem::where('status', 'pending')->count(),
            'returned' => LostFoundItem::where('status', 'returned')->count(),
            'group' => $group,
        ]);
    }

    public function show(LostFoundItem $item): View
    {
        $item->load([
            'reporter:id,name,email,role',
            'reviewer:id,name',
            'revisions' => fn ($q) => $q->orderBy('created_at'),
        ]);
        $claims = \App\Models\ItemClaim::query()
            ->where('lost_found_item_id', $item->id)
            ->with(['claimant:id,name', 'reviewer:id,name', 'handoverOfficer:id,name'])
            ->orderBy('created_at')
            ->get();
        $history = \App\Support\ReportAudit::history($item);
        foreach ($claims as $claim) {
            if ($claim->created_at) {
                $history->push([
                    'at' => $claim->created_at,
                    'title' => 'Klaim diajukan',
                    'actor' => $claim->claimant?->name ?? 'Pengaju klaim',
                    'detail' => 'Pengajuan kepemilikan barang diterima.',
                    'kind' => 'claim',
                ]);
            }
            if ($claim->reviewed_at) {
                $history->push([
                    'at' => $claim->reviewed_at,
                    'title' => $claim->status === 'rejected' ? 'Klaim ditolak' : 'Klaim disetujui',
                    'actor' => $claim->reviewer?->name ?? 'Admin',
                    'detail' => $claim->rejection_reason ?: 'Keputusan klaim telah dicatat.',
                    'kind' => $claim->status === 'rejected' ? 'rejected' : 'verified',
                ]);
            }
            if ($claim->returned_at) {
                $history->push([
                    'at' => $claim->returned_at,
                    'title' => 'Serah terima barang dikonfirmasi',
                    'actor' =>
                        $claim->handoverOfficer?->name ??
                        ($claim->handover_officer_name ?? 'Petugas'),
                    'detail' =>
                        'Barang diterima oleh ' .
                        ($claim->recipient_name ?: 'penerima tercatat') .
                        '. Kode serah terima: ' .
                        ($claim->handover_code ?: 'tidak tercatat') .
                        '.',
                    'kind' => 'returned',
                ]);
            }
        }
        $history = $history->sortBy(fn ($event) => $event['at']->timestamp)->values();
        return view('admin.items.show', compact('item', 'history', 'claims'));
    }
}
