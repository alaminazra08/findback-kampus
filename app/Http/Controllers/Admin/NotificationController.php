<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function markAsRead(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user()->notifications()->whereKey($id)->firstOrFail();
        $notification->markAsRead();

        return redirect()->to($this->destination($notification));
    }

    // Only routes owned by the admin application are allowed as destinations.
    public static function destination($notification): string
    {
        $data = $notification->data ?? [];
        $category = $data['category'] ?? null;
        $id = (int) ($data['record_id'] ?? 0);
        if ($category === 'reports') {
            return $id && \App\Models\LostFoundItem::whereKey($id)->exists()
                ? route('admin.reports.verification.show', $id)
                : route('admin.items.index');
        }
        if ($category === 'claims') {
            return $id && \App\Models\ItemClaim::whereKey($id)->exists()
                ? route('admin.claims.show', $id)
                : route('admin.claims.index');
        }
        if ($category === 'matching') {
            return route('admin.matching.index');
        }
        if ($category === 'chat') {
            return $id && \App\Models\User::whereKey($id)->where('role', 'user')->exists()
                ? route('admin.chat.show', $id)
                : route('admin.chat.index');
        }
        $title = mb_strtolower((string) ($data['title'] ?? ''));
        $message = mb_strtolower((string) ($data['message'] ?? ''));
        $combined = $title . ' ' . $message;

        if (str_contains($combined, 'klaim')) {
            return route('admin.claims.index');
        }
        if (str_contains($combined, 'pencocokan') || str_contains($combined, 'kandidat')) {
            return route('admin.matching.index');
        }
        if (str_contains($combined, 'pesan') || str_contains($combined, 'chat')) {
            return route('admin.chat.index');
        }
        if (str_contains($combined, 'laporan') || str_contains($combined, 'verifikasi')) {
            return route('admin.items.index');
        }

        return route('admin.dashboard');
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back();
    }
}
