<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class MatchConfirmedNotification extends Notification
{
    public function __construct(
        private int $foundItemId,
        private string $reportTitle,
        private bool $canClaim,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->canClaim
                ? 'Ada barang temuan yang cocok dengan laporanmu'
                : 'Barang temuanmu telah dicocokkan',
            'message' => $this->canClaim
                ? "Admin mencocokkan laporan kehilangan \"{$this->reportTitle}\" dengan barang temuan. Periksa detail dan ajukan klaim jika barang itu milikmu."
                : "Admin mencocokkan laporan temuan \"{$this->reportTitle}\" dengan laporan kehilangan. Tunggu arahan admin untuk serah terima.",
            'item_id' => $this->foundItemId,
            'match_action' => $this->canClaim ? 'claim' : 'view-report',
        ];
    }
}
