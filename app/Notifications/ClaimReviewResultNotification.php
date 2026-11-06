<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ClaimReviewResultNotification extends Notification
{
    use Queueable;

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
                $this->status === 'approved'
                    ? 'Klaim #' .
                        $this->claimId .
                        ' telah disetujui. Tunggu jadwal serah terima dari Admin.'
                    : 'Klaim #' .
                        $this->claimId .
                        ' ditolak. ' .
                        ($this->reason ?: 'Periksa status klaim.'),
            'url' => route('user.claims.index'),
        ];
    }
}
