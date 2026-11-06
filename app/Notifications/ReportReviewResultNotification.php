<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReportReviewResultNotification extends Notification
{
    use Queueable;

    public function __construct(
        public int $itemId,
        public string $itemTitle,
        public string $type,
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
        $approved = $this->status === 'verified';
        return [
            'title' => $approved ? 'Laporan disetujui' : 'Laporan ditolak',
            'message' => $approved
                ? 'Laporan "' . $this->itemTitle . '" telah disetujui administrator.'
                : 'Laporan "' . $this->itemTitle . '" ditolak. Alasan: ' . ($this->reason ?? '-'),
            'item_id' => $this->itemId,
            'type' => $this->type,
            'status' => $this->status,
            'url' => route('user.reports.show', $this->itemId, false),
        ];
    }
}
