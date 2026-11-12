<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ItemMatch;
use App\Models\LostFoundItem;
use App\Support\AdminAlerts;
use App\Support\AdminSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MatchingController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:all,pending,confirmed,rejected'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);
        $status = $filters['status'] ?? 'all';
        $search = trim($filters['q'] ?? '');

        $query = ItemMatch::query()
            ->with(['lostItem', 'foundItem', 'reviewer'])
            ->latest();
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas(
                    'lostItem',
                    fn ($item) => $item
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%"),
                )->orWhereHas(
                    'foundItem',
                    fn ($item) => $item
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%"),
                );
                if (ctype_digit($search)) {
                    $q->orWhere('id', (int) $search);
                }
            });
        }

        $counts = ItemMatch::query()
            ->selectRaw('status, COUNT(*) AS total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.matching.index', [
            'matches' => $query->paginate(12)->withQueryString(),
            'status' => $status,
            'search' => $search,
            'stats' => [
                'all' => $counts->sum(),
                'pending' => (int) $counts->get('pending', 0),
                'confirmed' => (int) $counts->get('confirmed', 0),
                'rejected' => (int) $counts->get('rejected', 0),
            ],
        ]);
    }

    public function show(ItemMatch $match): View
    {
        $match->load(['lostItem.reporter', 'foundItem.reporter', 'reviewer']);
        return view('admin.matching.show', compact('match'));
    }

    /**
     * Generate likely pairs from verified reports only. This is rule-based
     * similarity, not ownership verification or machine-learning prediction.
     */
    public function generate(): RedirectResponse
    {
        $created = 0;
        $minimumScore = AdminSettings::minimumScore();
        $eligible = LostFoundItem::query()
            ->where('status', 'verified')
            ->whereIn('type', ['lost', 'found'])
            ->orderBy('id')
            ->get();

        $lost = $eligible->where('type', 'lost');
        $found = $eligible->where('type', 'found');

        // Prevent large synchronous matching jobs from exhausting the server.
        if ($lost->count() * $found->count() > 25000) {
            return back()->with(
                'error',
                'Terlalu banyak kombinasi laporan. Jalankan pencocokan secara bertahap melalui queue.',
            );
        }

        foreach ($lost as $lostItem) {
            foreach ($found as $foundItem) {
                if ($lostItem->user_id === $foundItem->user_id) {
                    continue;
                }
                [$score, $reasons] = $this->calculateScore($lostItem, $foundItem);
                if ($score < $minimumScore) {
                    continue;
                }

                // Never rewrite an administrator's previous decision.
                $inserted = ItemMatch::query()->insertOrIgnore([
                    'lost_item_id' => $lostItem->id,
                    'found_item_id' => $foundItem->id,
                    'match_score' => $score,
                    'match_reason' => implode('; ', $reasons),
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $created += (int) $inserted;
            }
        }

        if ($created > 0) {
            AdminAlerts::send(
                'matching',
                'Kandidat pencocokan baru',
                "{$created} kandidat baru tersedia untuk diperiksa.",
            );
        }

        return redirect()
            ->route('admin.matching.index')
            ->with(
                'success',
                "Pencocokan selesai. {$created} kandidat baru ditambahkan. Pasangan lama tidak diubah.",
            );
    }

    public function review(Request $request, ItemMatch $match): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'in:confirm,reject'],
            'rejection_reason' => [
                'required_if:action,reject',
                'nullable',
                'string',
                'min:5',
                'max:1000',
            ],
        ]);

        $error = DB::transaction(function () use ($match, $validated): ?string {
            // Semua kandidat dengan laporan yang sama berbagi kunci ini.
            $items = LostFoundItem::query()
                ->whereIn('id', [$match->lost_item_id, $match->found_item_id])
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $locked = ItemMatch::query()->lockForUpdate()->findOrFail($match->id);
            if ($locked->status !== 'pending') {
                return 'Kandidat ini sudah diperiksa sebelumnya.';
            }

            $lostItem = $items->get($locked->lost_item_id);
            $foundItem = $items->get($locked->found_item_id);
            if (
                !$lostItem ||
                !$foundItem ||
                $lostItem->type !== 'lost' ||
                $foundItem->type !== 'found' ||
                $lostItem->status !== 'verified' ||
                $foundItem->status !== 'verified'
            ) {
                return 'Status laporan berubah. Kandidat hanya dapat diproses bila kedua laporan masih terverifikasi.';
            }

            if ($validated['action'] === 'confirm') {
                $conflict = ItemMatch::query()
                    ->where('status', 'confirmed')
                    ->where('id', '!=', $locked->id)
                    ->where(function ($query) use ($locked) {
                        $query
                            ->where('lost_item_id', $locked->lost_item_id)
                            ->orWhere('found_item_id', $locked->found_item_id);
                    })
                    ->exists();
                if ($conflict) {
                    return 'Salah satu laporan telah dikonfirmasi pada pasangan lainnya.';
                }
            }

            $locked->update([
                'status' => $validated['action'] === 'confirm' ? 'confirmed' : 'rejected',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'match_reason' =>
                    $validated['action'] === 'reject'
                        ? $locked->match_reason .
                            '; Alasan penolakan admin: ' .
                            trim($validated['rejection_reason'])
                        : $locked->match_reason,
            ]);
            return null;
        }, 3);

        if ($error !== null) {
            return back()->with('error', $error);
        }

        return redirect()
            ->route('admin.matching.show', $match)
            ->with(
                'success',
                $validated['action'] === 'confirm'
                    ? 'Pasangan laporan dikonfirmasi. Status barang tidak diubah; lakukan verifikasi kepemilikan sebelum serah terima.'
                    : 'Kandidat pencocokan ditolak.',
            );
    }

    private function calculateScore(LostFoundItem $lost, LostFoundItem $found): array
    {
        return app(\App\Services\MatchingScoreService::class)->calculateScore($lost, $found);
    }
}
