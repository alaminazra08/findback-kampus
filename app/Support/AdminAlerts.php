<?php

namespace App\Support;

use App\Models\User;
use App\Notifications\AdminMessageNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Schema;

final class AdminAlerts
{
    public static function send(
        string $category,
        string $title,
        string $message,
        ?int $recordId = null,
    ): void {
        // Alert admin mengikuti preferensi; keputusan untuk pelapor tetap dikirim.
        try {
            if (
                !Schema::hasTable('notifications') ||
                !AdminSettings::notificationsEnabled($category)
            ) {
                return;
            }
            User::where('role', 'admin')->chunkById(100, function ($admins) use (
                $title,
                $message,
                $category,
                $recordId,
            ): void {
                foreach ($admins as $admin) {
                    self::notify(
                        $admin,
                        new AdminMessageNotification($title, $message, $category, $recordId),
                    );
                }
            });
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public static function notify(?User $recipient, Notification $notification): void
    {
        try {
            if ($recipient && Schema::hasTable('notifications')) {
                $recipient->notify($notification);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
