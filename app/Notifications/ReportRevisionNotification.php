<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class ReportRevisionNotification extends Notification
{
    public function __construct(
        public int $itemId,
        public string $itemTitle,
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
                ? 'Permintaan perbaikan dibatalkan'
                : 'Laporan perlu diperbaiki',
            'message' => $this->itemTitle . ': ' . $this->note,
            'item_id' => $this->itemId,
            'status' => $this->cancelled ? 'revision_cancelled' : 'revision_requested',
            'url' => route('user.reports.show', $this->itemId, false),
        ];
    }
}
