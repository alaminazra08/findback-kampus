<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class ClaimEvidenceRequestNotification extends Notification
{
    public function __construct(
        public int $claimId,
        public string $note,
        public bool $cancelled = false,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->cancelled
                ? 'Permintaan bukti dibatalkan'
                : 'Bukti tambahan klaim diperlukan',
            'message' => 'Klaim #' . $this->claimId . ': ' . $this->note,
            'claim_id' => $this->claimId,
            'status' => $this->cancelled ? 'evidence_cancelled' : 'evidence_requested',
            'url' => '/dashboard',
        ];
    }
}
