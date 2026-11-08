<?php

namespace App\Observers;

use App\Models\LostFoundItem;
use App\Support\AdminAlerts;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class LostFoundItemObserver implements ShouldHandleEventsAfterCommit
{
    public function created(LostFoundItem $record): void
    {
        if ($record->status === 'pending') {
            AdminAlerts::send(
                'reports',
                'Laporan barang baru',
                "Laporan #{$record->id}: {$record->title} menunggu verifikasi.",
                $record->id,
            );
        }
    }
}
