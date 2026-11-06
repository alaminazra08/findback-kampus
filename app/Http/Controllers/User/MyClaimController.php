<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MyClaimController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:pending,approved,rejected,returned'],
        ]);

        $userId = $request->user()->id;

        // Statistik seluruh klaim milik pengguna.
        $baseQuery = DB::table('item_claims')->where('user_id', $userId);

        $totalClaims = (clone $baseQuery)->count();

        $pendingClaims = (clone $baseQuery)->where('status', 'pending')->count();

        $approvedClaims = (clone $baseQuery)->where('status', 'approved')->count();

        $rejectedClaims = (clone $baseQuery)->where('status', 'rejected')->count();

        $returnedClaims = (clone $baseQuery)->where('status', 'returned')->count();

        // Daftar klaim dan informasi barang.
        $query = DB::table('item_claims as claims')
            ->leftJoin('lost_found_items as items', 'items.id', '=', 'claims.lost_found_item_id')
            ->where('claims.user_id', $userId)
            ->select([
                'claims.id',
                'claims.status',
                'claims.proof_description',
                'claims.proof_photo_path',
                'claims.rejection_reason',
                'claims.created_at',
                'claims.reviewed_at',
                'claims.returned_at',
                'items.title as item_title',
                'items.category',
                'items.location',
                'items.photo_path as item_photo',
            ]);

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);

            $query->where(function ($q) use ($search) {
                $q->where('items.title', 'like', "%{$search}%")
                    ->orWhere('items.category', 'like', "%{$search}%")
                    ->orWhere('items.location', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('claims.status', $filters['status']);
        }

        $claims = $query->orderByDesc('claims.created_at')->paginate(10)->withQueryString();

        $openEvidenceRequests = \App\Models\ClaimEvidenceRequest::query()
            ->whereIn('item_claim_id', $claims->pluck('id')->all())
            ->whereNull('closed_at')
            ->get()
            ->keyBy('item_claim_id');

        return view(
            'user.claims.index',
            compact(
                'claims',
                'filters',
                'totalClaims',
                'pendingClaims',
                'approvedClaims',
                'rejectedClaims',
                'returnedClaims',
                'openEvidenceRequests',
            ),
        );
    }
}
