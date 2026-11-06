<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ItemClaim;
use App\Models\LostFoundItem;
use App\Support\AdminSettings;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ClaimController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:pending,approved,rejected,returned'],
            'evidence' => ['nullable', 'in:open'],
        ]);

        $query = ItemClaim::query()->with([
            'item:id,user_id,title,type,status,location,category,photo_path',
            'claimant:id,name,email',
        ]);

        $query->withExists([
            'evidenceRequests as awaiting_evidence' => fn ($q) => $q->whereNull('closed_at'),
        ]);
        if (($filters['evidence'] ?? null) === 'open') {
            $query->whereHas('evidenceRequests', fn ($q) => $q->whereNull('closed_at'));
        }
        $search = trim($filters['search'] ?? '');
        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->whereHas('item', function ($itemQuery) use ($search): void {
                    $itemQuery
                        ->where('title', 'like', '%' . $search . '%')
                        ->orWhere('location', 'like', '%' . $search . '%')
                        ->orWhere('category', 'like', '%' . $search . '%');
                })->orWhereHas('claimant', function ($userQuery) use ($search): void {
                    $userQuery->where('name', 'like', '%' . $search . '%');
                });
                if (ctype_digit($search)) {
                    $q->orWhereKey((int) $search);
                }
            });
        }

        if ($status = $filters['status'] ?? null) {
            $query->where('status', $status);
        }

        return view('admin.claims.index', [
            'claims' => $query
                ->orderByRaw(
                    "CASE WHEN status = 'pending' THEN 0 WHEN status = 'approved' THEN 1 ELSE 2 END",
                )
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'total' => ItemClaim::count(),
            'pending' => ItemClaim::where('status', 'pending')->count(),
            'approved' => ItemClaim::where('status', 'approved')->count(),
            'rejected' => ItemClaim::where('status', 'rejected')->count(),
            'returned' => ItemClaim::where('status', 'returned')->count(),
        ]);
    }

    public function show(ItemClaim $claim): View
    {
        $claim->load([
            'item.reporter:id,name,email',
            'claimant:id,name,email,role',
            'reviewer:id,name,email',
            'handoverOfficer:id,name',
            'evidenceRequests' => fn ($q) => $q->latest('id'),
        ]);

        $claimMessages = collect();
        if (in_array($claim->status, ['approved', 'returned'], true) && $claim->claimant) {
            $claimMessages = \App\Models\ChatMessage::with('sender')
                ->where('user_id', $claim->claimant->id)
                ->where('claim_id', $claim->id)
                ->latest('id')
                ->limit(80)
                ->get()
                ->reverse()
                ->values();
        }

        return view('admin.claims.show', compact('claim', 'claimMessages'));
    }

    public function review(Request $request, ItemClaim $claim): RedirectResponse
    {
        $data = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'evidence_reviewed' => ['exclude_unless:decision,approved', 'required', 'accepted'],
            'rejection_reason' => [
                'required_if:decision,rejected',
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        DB::transaction(function () use ($claim, $data, $request): void {
            // Lock the item first, then the claim, to serialize approval per item.
            $itemId = $claim->lost_found_item_id;
            $item = LostFoundItem::query()->lockForUpdate()->findOrFail($itemId);
            $lockedClaim = ItemClaim::query()->lockForUpdate()->findOrFail($claim->id);

            if ($lockedClaim->evidenceRequests()->whereNull('closed_at')->exists()) {
                throw ValidationException::withMessages([
                    'decision' => 'Klaim masih menunggu bukti tambahan.',
                ]);
            }
            if ($lockedClaim->status !== 'pending') {
                throw ValidationException::withMessages([
                    'decision' => 'Klaim ini sudah diproses sebelumnya.',
                ]);
            }
            if ($item->type !== 'found') {
                throw ValidationException::withMessages([
                    'decision' => 'Klaim hanya dapat diproses untuk laporan barang ditemukan.',
                ]);
            }
            if ($item->status !== 'verified') {
                throw ValidationException::withMessages([
                    'decision' => 'Barang harus berstatus terverifikasi sebelum klaim diputuskan.',
                ]);
            }

            if ($data['decision'] === 'approved') {
                if (
                    !trim((string) $lockedClaim->proof_description) &&
                    !$lockedClaim->proof_photo_path
                ) {
                    throw ValidationException::withMessages([
                        'decision' =>
                            'Bukti kepemilikan belum tersedia. Minta bukti tambahan terlebih dahulu.',
                    ]);
                }
                $hasAnotherWinner = ItemClaim::where('lost_found_item_id', $item->id)
                    ->where('id', '!=', $lockedClaim->id)
                    ->whereIn('status', ['approved', 'returned'])
                    ->exists();
                if ($hasAnotherWinner) {
                    throw ValidationException::withMessages([
                        'decision' => 'Sudah ada klaim lain yang disetujui untuk barang ini.',
                    ]);
                }
            }

            $lockedClaim->update([
                'status' => $data['decision'],
                'rejection_reason' =>
                    $data['decision'] === 'rejected' ? trim($data['rejection_reason']) : null,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);
        }, 3);

        $claim->refresh();
        if ($claim->claimant) {
            \App\Support\AdminAlerts::notify(
                $claim->claimant,
                new \App\Notifications\ClaimReviewResultNotification(
                    $claim->id,
                    $claim->status,
                    $claim->rejection_reason,
                ),
            );
        }

        return redirect()
            ->route('admin.claims.show', $claim)
            ->with(
                'success',
                $data['decision'] === 'approved'
                    ? 'Klaim berhasil disetujui.'
                    : 'Klaim berhasil ditolak.',
            );
    }

    public function markReturned(
        \App\Http\Requests\CompleteHandoverRequest $request,
        ItemClaim $claim,
        \App\Services\ClaimHandoverService $handovers,
    ): RedirectResponse {
        $handovers->complete(
            $claim,
            $request->user(),
            $request->validated(),
            $request->file('handover_evidence'),
        );
        return redirect()
            ->route('admin.claims.show', $claim)
            ->with(
                'success',
                'Serah terima tersimpan. Anda dapat mengunduh bukti serah terima PDF.',
            );
    }

    public function handoverPdf(ItemClaim $claim)
    {
        abort_unless(
            $claim->hasHandoverReceipt(),
            404,
            'Bukti serah terima lengkap belum tersedia untuk klaim ini.',
        );
        return Pdf::loadView('admin.claims.handover-pdf', [
            'claim' => $claim,
            'settings' => AdminSettings::all(),
        ])
            ->setPaper('a4')
            ->setOptions([
                'isRemoteEnabled' => false,
                'defaultFont' => 'DejaVu Sans',
                'chroot' => public_path(),
            ])
            ->download('bukti-serah-terima-' . $claim->handover_code . '.pdf')
            ->header('Cache-Control', 'private, no-store');
    }

    public function handoverEvidence(ItemClaim $claim)
    {
        abort_unless($claim->status === 'returned' && $claim->handover_evidence_path, 404);
        $path = $claim->handover_evidence_path;
        abort_unless(
            preg_match('~^handovers/[a-zA-Z0-9]+\.(jpg|jpeg|png|pdf)$~', $path) &&
                Storage::disk('local')->exists($path),
            404,
            'Berkas bukti tidak tersedia.',
        );
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        return Storage::disk('local')->download(
            $path,
            'lampiran-serah-terima-' . $claim->id . '.' . $extension,
            [
                'Content-Type' => 'application/octet-stream',
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store',
            ],
        );
    }

    public function requestEvidence(Request $request, ItemClaim $claim): RedirectResponse
    {
        $data = $request->validate(['note' => ['required', 'string', 'min:10', 'max:2000']]);
        DB::transaction(function () use ($request, $claim, $data) {
            $item = LostFoundItem::lockForUpdate()->findOrFail($claim->lost_found_item_id);
            $locked = ItemClaim::lockForUpdate()->findOrFail($claim->id);
            if (
                $locked->status !== 'pending' ||
                $item->type !== 'found' ||
                $item->status !== 'verified' ||
                !$locked->claimant ||
                $locked->evidenceRequests()->whereNull('closed_at')->exists() ||
                ItemClaim::where('lost_found_item_id', $item->id)
                    ->whereIn('status', ['approved', 'returned'])
                    ->exists()
            ) {
                throw ValidationException::withMessages([
                    'note' =>
                        'Permintaan hanya untuk klaim menunggu pada barang terverifikasi, tanpa permintaan terbuka atau klaim lain yang disetujui.',
                ]);
            }
            $locked->evidenceRequests()->create([
                'requested_by' => $request->user()->id,
                'requester_name' => $request->user()->name,
                'note' => $data['note'],
            ]);
            $locked->claimant->notify(
                new \App\Notifications\ClaimEvidenceRequestNotification($locked->id, $data['note']),
            );
        }, 3);
        return back()->with(
            'success',
            'Permintaan bukti tambahan dan notifikasi pengaju telah tersimpan.',
        );
    }

    public function cancelEvidence(Request $request, ItemClaim $claim): RedirectResponse
    {
        $data = $request->validate([
            'request_id' => ['required', 'integer'],
            'closure_note' => ['required', 'string', 'min:10', 'max:2000'],
        ]);
        DB::transaction(function () use ($request, $claim, $data) {
            LostFoundItem::lockForUpdate()->findOrFail($claim->lost_found_item_id);
            $locked = ItemClaim::lockForUpdate()->findOrFail($claim->id);
            $entry = $locked
                ->evidenceRequests()
                ->whereKey($data['request_id'])
                ->whereNull('closed_at')
                ->first();
            if ($locked->status !== 'pending' || !$entry) {
                throw ValidationException::withMessages([
                    'closure_note' => 'Permintaan tidak aktif. Muat ulang halaman.',
                ]);
            }
            $entry->update([
                'closed_at' => now(),
                'closed_by' => $request->user()->id,
                'closer_name' => $request->user()->name,
                'closure_type' => 'cancelled',
                'closure_note' => $data['closure_note'],
            ]);
            $locked->claimant?->notify(
                new \App\Notifications\ClaimEvidenceRequestNotification(
                    $locked->id,
                    $data['closure_note'],
                    true,
                ),
            );
        }, 3);
        return back()->with(
            'success',
            'Permintaan dibatalkan dengan alasan tercatat. Klaim tetap menunggu pemeriksaan.',
        );
    }
}
