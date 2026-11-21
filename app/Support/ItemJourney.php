<?php

namespace App\Support;

use App\Models\ItemClaim;
use App\Models\ItemMatch;
use App\Models\LostFoundItem;
use Illuminate\Support\Collection;

class ItemJourney
{
    public static function forItem(LostFoundItem $item, ?ItemClaim $claim = null): Collection
    {
        $matches = ItemMatch::query()
            ->where(fn ($query) => $query->where('lost_item_id', $item->id)->orWhere('found_item_id', $item->id))
            ->with('reviewer:id,name')
            ->orderBy('id')
            ->get();

        $history = ReportAudit::history($item);
        $foundIds = $item->type === 'found'
            ? [$item->id]
            : $matches->where('status', 'confirmed')->pluck('found_item_id')->unique()->all();

        foreach ($matches as $match) {
            $history = $history->concat(self::matchEvents($match));
        }

        return self::appendClaims($history, $claim ? collect([$claim]) : self::claims($foundIds));
    }

    public static function forMatch(ItemMatch $match): Collection
    {
        $history = collect();

        foreach (['Laporan barang hilang' => $match->lostItem, 'Laporan barang ditemukan' => $match->foundItem] as $label => $item) {
            if ($item) {
                $history = $history->concat(ReportAudit::history($item)->map(function (array $event) use ($label) {
                    $event['context'] = $label;
                    return $event;
                }));
            }
        }

        return self::appendClaims(
            $history->concat(self::matchEvents($match)),
            self::claims([$match->found_item_id]),
        );
    }

    private static function claims(array $foundIds): Collection
    {
        if ($foundIds === []) {
            return collect();
        }

        return ItemClaim::query()->whereIn('lost_found_item_id', $foundIds)
            ->with(['claimant:id,name', 'reviewer:id,name', 'handoverOfficer:id,name'])
            ->orderBy('id')
            ->get();
    }

    private static function appendClaims(Collection $history, Collection $claims): Collection
    {
        foreach ($claims as $claim) {
            $history = $history->concat(ClaimHistory::forClaim($claim)->map(function (array $event) use ($claim) {
                $event['context'] = 'Klaim K' . $claim->id;
                return $event;
            }));
        }

        return $history->sortBy('at')->values();
    }

    private static function matchEvents(ItemMatch $match): Collection
    {
        $history = collect();
        $context = 'Kandidat ' . $match->id;

        if ($match->created_at) {
            $history->push([
                'at' => $match->created_at,
                'title' => 'Kandidat pencocokan dibuat',
                'context' => $context,
                'actor' => null,
                'detail' => 'Pasangan laporan tersedia untuk diperiksa.',
                'kind' => 'created',
            ]);
        }

        if ($match->reviewed_at && in_array($match->status, ['confirmed', 'rejected'], true)) {
            $rejected = $match->status === 'rejected';
            $reason = explode('; Alasan penolakan admin:', (string) $match->match_reason, 2)[1] ?? '';
            $history->push([
                'at' => $match->reviewed_at,
                'title' => $rejected ? 'Kandidat ditolak' : 'Pencocokan dikonfirmasi',
                'context' => $context,
                'actor' => $match->reviewer?->name ?? 'Admin',
                'detail' => $rejected ? trim($reason) : 'Pasangan laporan dinilai cocok. Kepemilikan diperiksa melalui klaim.',
                'kind' => $rejected ? 'rejected' : 'confirmed',
            ]);
        }

        return $history;
    }
}
