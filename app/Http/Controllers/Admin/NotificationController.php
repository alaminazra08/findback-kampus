<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ItemClaim;
use App\Models\LostFoundItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class NotificationController extends Controller
{
    public function markAsRead(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user()->notifications()->whereKey($id)->firstOrFail();
        $notification->markAsRead();

        return redirect()->to($this->destination($notification));
    }

    private const DESTINATIONS = [
        'reports' => ['admin.reports.verification.show', 'admin.items.index'],
        'claims' => ['admin.claims.show', 'admin.claims.index'],
        'chat' => ['admin.chat.show', 'admin.chat.index'],
    ];

    private static function destinationRecords(string $category): Builder
    {
        return match ($category) {
            'reports' => LostFoundItem::query(),
            'claims' => ItemClaim::query(),
            'chat' => User::query()->where('role', 'user'),
        };
    }

    public static function destinations(Collection $notifications): array
    {
        $idsByCategory = [];
        foreach ($notifications as $notification) {
            $category = $notification->data['category'] ?? null;
            $id = (int) ($notification->data['record_id'] ?? 0);
            if (is_string($category) && isset(self::DESTINATIONS[$category]) && $id) {
                $idsByCategory[$category][] = $id;
            }
        }

        $existing = [];
        foreach ($idsByCategory as $category => $ids) {
            $existing[$category] = array_fill_keys(
                self::destinationRecords($category)->whereKey(array_unique($ids))->pluck('id')->all(),
                true,
            );
        }

        $links = [];
        foreach ($notifications as $notification) {
            $links[$notification->id] = self::destination($notification, $existing);
        }
        return $links;
    }

    // Only routes owned by the admin application are allowed as destinations.
    public static function destination($notification, ?array $existing = null): string
    {
        $data = $notification->data ?? [];
        $category = $data['category'] ?? null;
        $id = (int) ($data['record_id'] ?? 0);
        if (is_string($category) && isset(self::DESTINATIONS[$category])) {
            [$detail, $fallback] = self::DESTINATIONS[$category];
            $exists = $id && ($existing === null
                ? self::destinationRecords($category)->whereKey($id)->exists()
                : isset($existing[$category][$id]));

            return $exists ? route($detail, $id) : route($fallback);
        }
        if ($category === 'matching') {
            return route('admin.matching.index');
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
