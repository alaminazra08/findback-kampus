<?php

namespace App\Observers;

use App\Models\LostFoundItem;
use App\Support\AdminAlerts;
use App\Support\WorkflowStatus;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class LostFoundItemObserver implements ShouldHandleEventsAfterCommit
{
    public function created(LostFoundItem $record): void
    {
        if ($record->status === 'pending') {
            AdminAlerts::send(
                'reports',
                'Laporan barang baru',
                'Laporan ' . WorkflowStatus::code((int) $record->id) . ": {$record->title} menunggu verifikasi.",
                $record->id,
                [
                    'item_code' => WorkflowStatus::code((int) $record->id),
                    'status_key' => $record->status,
                    'status_label' => WorkflowStatus::report($record->status),
                    'event' => 'created',
                ],
            );
        }
    }

    public function updated(LostFoundItem $record): void
    {
        if (!$record->wasChanged('status')) {
            return;
        }

        $newStatus = (string) $record->status;
        $code = WorkflowStatus::code((int) $record->id);
        $newLabel = WorkflowStatus::report($newStatus);

        AdminAlerts::send(
            'reports',
            'Status laporan diperbarui',
            "Laporan {$code}: {$record->title}. Status terbaru: {$newLabel}.",
            (int) $record->id,
            [
                'item_code' => $code,
                'status_key' => $newStatus,
                'status_label' => $newLabel,
                'event' => 'status_changed',
            ],
        );
    }
}
