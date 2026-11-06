<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LostFoundItem;
use App\Notifications\ReportReviewResultNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReportVerificationController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', Rule::in(['lost', 'found'])],
            'revision' => ['nullable', 'in:history'],
            'status' => [
                'nullable',
                Rule::in(['pending', 'verified', 'rejected', 'claimed', 'returned']),
            ],
        ]);

        if (
            !empty($filters['status']) &&
            !in_array($filters['status'], ['pending', 'rejected', 'verified'], true)
        ) {
            return redirect()->route(
                'admin.items.index',
                array_filter([
                    'status' => $filters['status'],
                    'search' => $filters['search'] ?? null,
                    'type' => $filters['type'] ?? null,
                ]),
            );
        }
        $revisionMode = ($filters['revision'] ?? null) === 'history' ? 'history' : 'ready';
        $query = LostFoundItem::query()
            ->where('status', $revisionMode === 'history' ? 'rejected' : 'pending')
            ->with('reporter:id,name,email,role')
            ->withExists([
                'revisions as awaiting_revision' => fn ($q) => $q->whereNull('closed_at'),
                'revisions as resubmitted' => fn ($q) => $q->where('closure_kind', 'resubmitted'),
            ]);
        // Laporan ditolak berada di Riwayat Keputusan hingga User mengirim ulang.
        // Laporan pending yang mempunyai permintaan revisi historis aktif tetap terlihat di antrean.
        $search = trim($filters['search'] ?? '');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhereHas('reporter', fn ($u) => $u->where('name', 'like', "%{$search}%"));
                if (preg_match('/^(?:BRG-)?(\d+)$/i', $search, $m)) {
                    $q->orWhere('id', (int) $m[1]);
                }
            });
        }
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        $counts = LostFoundItem::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $openRevisionCount = LostFoundItem::query()->where('status', 'rejected')->count();
        $pendingReadyCount = (int) $counts->get('pending', 0);

        return view('admin.reports.verification.index', [
            'revisionMode' => $revisionMode,
            'openRevisionCount' => $openRevisionCount,
            'pendingReadyCount' => $pendingReadyCount,
            'items' => $query->oldest('created_at')->orderBy('id')->paginate(10)->withQueryString(),
            'counts' => $counts,
            'total' => $counts->sum(),
            'additionalCounts' => $counts->except(['pending', 'verified', 'rejected']),
            'pending' => $counts->get('pending', 0),
            'verified' => $counts->get('verified', 0),
            'rejected' => $counts->get('rejected', 0),
        ]);
    }

    public function show(LostFoundItem $item): View
    {
        $item->load([
            'reporter:id,name,email,role',
            'reviewer:id,name,email',
            'revisions' => fn ($q) => $q->latest('id'),
        ]);
        $history = \App\Support\ReportAudit::history($item);
        $decision = \App\Models\ReportAuditEvent::where('lost_found_item_id', $item->id)
            ->whereIn('event', ['verified', 'rejected'])
            ->latest('id')
            ->first();
        return view('admin.reports.verification.show', compact('item', 'history', 'decision'));
    }

    public function review(Request $request, LostFoundItem $item): RedirectResponse
    {
        $validated = $request->validate(
            [
                'decision' => ['required', Rule::in(['verified', 'rejected'])],
                'reason' => [
                    'required_if:decision,rejected',
                    'nullable',
                    'string',
                    'min:10',
                    'max:1000',
                ],
            ],
            [
                'reason.required_if' => 'Alasan penolakan wajib diisi.',
                'reason.min' => 'Alasan penolakan minimal 10 karakter.',
            ],
        );
        // Lock menjamin laporan hanya dapat direview satu kali, walau ada dua admin sekaligus.
        try {
            $result = DB::transaction(function () use (
                $item,
                $validated,
                $request,
            ): ?LostFoundItem {
                $locked = LostFoundItem::query()->lockForUpdate()->findOrFail($item->getKey());
                if (
                    $locked->status !== 'pending' ||
                    $locked->revisions()->whereNull('closed_at')->exists()
                ) {
                    return null;
                }
                $locked->status = $validated['decision'];
                $locked->reviewed_by = $request->user()->id;
                $locked->reviewed_at = now();
                $locked->rejection_reason =
                    $validated['decision'] === 'rejected' ? trim($validated['reason']) : null;
                $locked->save();
                \App\Support\ReportAudit::record(
                    $locked,
                    $request->user(),
                    $locked->status,
                    $locked->rejection_reason,
                );
                $locked->reporter?->notify(
                    new ReportReviewResultNotification(
                        $locked->id,
                        $locked->title,
                        $locked->type,
                        $locked->status,
                        $locked->rejection_reason,
                    ),
                );
                return $locked;
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Tindakan gagal disimpan. Tidak ada keputusan atau permintaan yang diubah; silakan coba kembali.',
                );
        }
        if ($result === null) {
            return back()->with(
                'warning',
                'Laporan sudah diproses atau masih menunggu perbaikan. Muat ulang detail untuk melihat status terbaru.',
            );
        }

        return redirect()
            ->route('admin.reports.verification')
            ->with(
                'success',
                $validated['decision'] === 'verified'
                    ? 'Laporan berhasil disetujui.'
                    : 'Laporan berhasil ditolak.',
            );
    }

    public function requestRevision(Request $request, LostFoundItem $item): RedirectResponse
    {
        abort(410, 'Permintaan perbaikan baru telah digabungkan ke keputusan Tolak Laporan.');
        $data = $request->validate(['note' => ['required', 'string', 'min:10', 'max:2000']]);
        try {
            DB::transaction(function () use ($request, $item, $data) {
                $locked = LostFoundItem::lockForUpdate()->findOrFail($item->id);
                if (
                    $locked->status !== 'pending' ||
                    $locked->revisions()->whereNull('closed_at')->exists()
                ) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'note' =>
                            'Hanya laporan menunggu tanpa permintaan terbuka yang dapat diminta perbaikan.',
                    ]);
                }
                $locked->revisions()->create([
                    'requested_by' => $request->user()->id,
                    'requester_name' => $request->user()->name,
                    'note' => $data['note'],
                ]);
                // Database notification shares the transaction with the request.
                $locked->reporter?->notify(
                    new \App\Notifications\ReportRevisionNotification(
                        $locked->id,
                        $locked->title,
                        $data['note'],
                    ),
                );
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Tindakan gagal disimpan. Tidak ada keputusan atau permintaan yang diubah; silakan coba kembali.',
                );
        }
        return back()->with(
            'success',
            'Permintaan perbaikan tersimpan. Notifikasi telah dicatat untuk pelapor.',
        );
    }

    public function cancelRevision(Request $request, LostFoundItem $item): RedirectResponse
    {
        $data = $request->validate([
            'closure_note' => ['required', 'string', 'min:10', 'max:2000'],
            'revision_id' => ['required', 'integer'],
        ]);
        try {
            DB::transaction(function () use ($request, $item, $data) {
                $locked = LostFoundItem::lockForUpdate()->findOrFail($item->id);
                $revision = $locked
                    ->revisions()
                    ->whereKey($data['revision_id'])
                    ->whereNull('closed_at')
                    ->first();
                if ($locked->status !== 'pending' || !$revision) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'closure_note' => 'Permintaan tidak aktif. Muat ulang halaman.',
                    ]);
                }
                $revision->update([
                    'closed_at' => now(),
                    'closed_by' => $request->user()->id,
                    'closer_name' => $request->user()->name,
                    'closure_kind' => 'cancelled',
                    'closure_note' => $data['closure_note'],
                ]);
                $locked->reporter?->notify(
                    new \App\Notifications\ReportRevisionNotification(
                        $locked->id,
                        $locked->title,
                        $data['closure_note'],
                        true,
                    ),
                );
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Tindakan gagal disimpan. Tidak ada keputusan atau permintaan yang diubah; silakan coba kembali.',
                );
        }
        return back()->with(
            'success',
            'Permintaan dibatalkan dengan catatan. Laporan tetap menunggu keputusan admin.',
        );
    }
}
