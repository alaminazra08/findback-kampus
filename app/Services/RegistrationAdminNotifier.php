<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\AdminMessageNotification;
use Throwable;

class RegistrationAdminNotifier
{
    /**
     * Notify every administrator when a genuinely new user account is created.
     * Notification failures are reported but must not invalidate registration.
     */
    public function notify(User $registeredUser, string $provider): void
    {
        try {
            $admins = User::query()
                ->where('role', 'admin')
                ->get();
        } catch (Throwable $exception) {
            report($exception);
            return;
        }

        foreach ($admins as $admin) {
            try {
                $admin->notify(new AdminMessageNotification(
                    title: 'Pengguna baru terdaftar',
                    message: "Akun {$registeredUser->name} ({$registeredUser->email}) berhasil dibuat melalui {$provider}.",
                    category: 'users',
                    recordId: (int) $registeredUser->id,
                ));
            } catch (Throwable $exception) {
                report($exception);
            }
        }
    }
}
