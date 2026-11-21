<?php

namespace App\Observers;

use App\Models\ItemClaim;
use App\Support\AdminAlerts;
use App\Support\WorkflowStatus;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class ItemClaimObserver implements ShouldHandleEventsAfterCommit
{
    public function created(ItemClaim $record): void
    {
        if ($record->status === 'pending') {
            AdminAlerts::send(
                'claims',
                'Pengajuan klaim baru',
                "Klaim K{$record->id} untuk barang " . WorkflowStatus::code((int) $record->lost_found_item_id) . " menunggu pemeriksaan.",
                $record->id,
                [
                    'status_key' => $record->status,
                    'status_label' => WorkflowStatus::claim($record->status),
                    'item_code' => WorkflowStatus::code((int) $record->lost_found_item_id),
                    'event' => 'created',
                ],
            );
        }
    }

    public function updated(ItemClaim $record): void
    {
        if (!$record->wasChanged('status')) {
            return;
        }

        $newStatus = (string) $record->status;
        $newLabel = WorkflowStatus::claim($newStatus);
        $itemCode = WorkflowStatus::code((int) $record->lost_found_item_id);

        AdminAlerts::send(
            'claims',
            'Status klaim diperbarui',
            "Klaim K{$record->id} untuk barang {$itemCode}: status terbaru {$newLabel}.",
            (int) $record->id,
            [
                'status_key' => $newStatus,
                'status_label' => $newLabel,
                'item_code' => $itemCode,
                'event' => 'status_changed',
            ],
        );

        if (in_array($record->status, ['approved', 'rejected'])) {
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
