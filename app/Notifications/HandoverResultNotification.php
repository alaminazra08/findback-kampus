<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class HandoverResultNotification extends Notification
{
    public function __construct(
        public string $itemTitle,
        public string $receiptCode,
        public bool $returnedToYou = true,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->returnedToYou ? 'Serah terima barang selesai' : 'Klaim ditutup',
            'message' => $this->returnedToYou
                ? 'Serah terima "' .
                    $this->itemTitle .
                    '" telah dicatat. Nomor bukti: ' .
                    $this->receiptCode .
                    '.'
                : 'Barang "' .
                    $this->itemTitle .
                    '" telah diserahkan melalui klaim lain. Pengajuan Anda ditutup; hubungi petugas bila perlu klarifikasi.',
            'url' => '/dashboard',
        ];
    }
}
