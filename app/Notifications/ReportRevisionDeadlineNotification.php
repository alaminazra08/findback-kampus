<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReportRevisionDeadlineNotification extends Notification
{
    use Queueable;

    public function __construct(
        public int $itemId,
        public string $itemTitle,
        public string $deadline,
        public bool $finalWarning = false,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->finalWarning ? 'Batas perbaikan hari terakhir' : 'Pengingat perbaikan laporan',
            'message' => $this->finalWarning
                ? 'Hari ini adalah batas terakhir untuk memperbaiki laporan "'.$this->itemTitle.'". Setelah tenggat berakhir, laporan akan dihapus permanen.'
                : 'Laporan "'.$this->itemTitle.'" belum diperbaiki. Silakan kirim perbaikan sebelum '.$this->deadline.'.',
            'item_id' => $this->itemId,
            'status' => $this->finalWarning ? 'revision_deadline_final' : 'revision_deadline_reminder',
            'deadline' => $this->deadline,
            'url' => route('user.reports.show', $this->itemId, false),
        ];
    }
}
