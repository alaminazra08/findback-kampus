<?php

namespace App\Support;

use App\Models\ItemClaim;
use Illuminate\Support\Collection;

class ClaimHistory
{
    public static function forClaim(ItemClaim $claim): Collection
    {
        $history = collect();

        if ($claim->created_at) {
            $history->push([
                'at' => $claim->created_at,
                'title' => 'Klaim diajukan',
                'actor' => $claim->claimant?->name ?? 'Pengaju klaim',
                'detail' => 'Pengajuan kepemilikan barang diterima.',
                'kind' => 'claim',
            ]);
        }

        if ($claim->reviewed_at && in_array($claim->status, ['approved', 'rejected', 'returned'], true)) {
            $rejected = $claim->status === 'rejected';
            $history->push([
                'at' => $claim->reviewed_at,
                'title' => $rejected ? 'Klaim ditolak' : 'Klaim disetujui',
                'actor' => $claim->reviewer?->name ?? 'Admin',
                'detail' => $rejected ? $claim->rejection_reason : 'Kepemilikan disetujui oleh admin.',
                'kind' => $rejected ? 'rejected' : 'approved',
            ]);
        }

        if ($claim->reviewed_at && in_array($claim->status, ['approved', 'returned'], true)) {
            $history->push([
                'at' => $claim->reviewed_at,
                'title' => 'Menunggu Serah Terima',
                'actor' => null,
                'detail' => 'Klaim diteruskan ke tahap penyerahan barang.',
                'kind' => 'awaiting_handover',
            ]);
        }

        if ($claim->returned_at) {
            $recipient = $claim->recipient_name ?: 'penerima tercatat';
            $code = $claim->handover_code ?: 'tidak tercatat';
            $history->push([
                'at' => $claim->returned_at,
                'title' => 'Serah Terima Selesai',
                'actor' => $claim->handoverOfficer?->name ?? $claim->handover_officer_name ?? 'Petugas',
                'detail' => "Barang diterima oleh {$recipient}. Kode serah terima: {$code}.",
                'kind' => 'returned',
            ]);
        }

        return $history;
    }
}
