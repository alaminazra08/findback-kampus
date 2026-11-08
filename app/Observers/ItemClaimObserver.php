<?php

namespace App\Observers;

use App\Models\ItemClaim;
use App\Support\AdminAlerts;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class ItemClaimObserver implements ShouldHandleEventsAfterCommit
{
    public function created(ItemClaim $record): void
    {
        if ($record->status === 'pending') {
            AdminAlerts::send(
                'claims',
                'Pengajuan klaim baru',
                "Klaim #{$record->id} untuk barang #{$record->lost_found_item_id} menunggu pemeriksaan.",
                $record->id,
            );
        }
    }

    public function updated(ItemClaim $record): void
    {
        // Handover already sends its own result notification for closed claims.
        if ($record->status === 'rejected' && $record->item?->status === 'returned') {
            return;
        }
        if ($record->wasChanged('status') && in_array($record->status, ['approved', 'rejected'])) {
            AdminAlerts::notify(
                $record->claimant,
                new \App\Notifications\UserClaimStatusNotification(
                    $record->id,
                    $record->status,
                    $record->rejection_reason,
                ),
            );
        }
    }
}
