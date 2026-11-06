<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class UserClaimStatusNotification extends Notification
{
    public function __construct(
        public int $claimId,
        public string $status,
        public ?string $reason = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->status === 'approved' ? 'Klaim disetujui' : 'Klaim ditolak',
            'message' =>
                'Klaim #' .
                $this->claimId .
                ($this->status === 'approved'
                    ? ' telah disetujui. Hubungi admin untuk mengatur serah terima.'
                    : ' ditolak. Alasan: ' . ($this->reason ?? '-')),
            'claim_id' => $this->claimId,
            'status' => $this->status,
            'url' => '/klaim-saya',
        ];
    }
}
