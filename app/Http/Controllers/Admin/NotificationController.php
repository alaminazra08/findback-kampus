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
use Illuminate\Contracts\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.notifications.index', $this->notificationPage($request));
    }

    public function stats(Request $request): \Illuminate\Http\JsonResponse
    {
        $page = $this->notificationPage($request);

        return response()->json([
            'total' => $page['totalCount'],
            'unread' => $page['unreadCount'],
            'today' => $page['notificationsToday'],
            'date' => now('Asia/Jakarta')->toDateString(),
            'list_html' => view('admin.notifications.partials.live-list', $page)->render(),
        ])->header('Cache-Control', 'private, no-store');
    }

    private function notificationPage(Request $request): array
    {
        $filters = $request->validate([
            'category' => ['nullable', 'in:all,unread,reports,claims,registrations,system'],
            'q' => ['nullable', 'string', 'max:120'],
            'sort' => ['nullable', 'in:newest,oldest'],
        ]);

        $category = $filters['category'] ?? 'all';
        $search = trim($filters['q'] ?? '');
        $sort = $filters['sort'] ?? 'newest';
        $base = $request->user()->notifications();

        $query = clone $base;
        if ($category === 'unread') {
            $query->whereNull('read_at');
        } elseif ($category === 'reports') {
            $query->where(function ($builder) {
                $builder->where('data->category', 'reports')
                    ->orWhere('data->title', 'like', '%laporan%');
            });
        } elseif ($category === 'claims') {
            $query->where(function ($builder) {
                $builder->where('data->category', 'claims')
                    ->orWhere('data->title', 'like', '%klaim%')
                    ->orWhere('data->message', 'like', '%klaim%');
            });
        } elseif ($category === 'registrations') {
            $query->where(function ($builder) {
                $builder->where('data->category', 'users')
                    ->orWhere('data->title', 'like', '%registrasi%')
                    ->orWhere('data->title', 'like', '%pengguna baru%');
            });
        } elseif ($category === 'system') {
            $query->where(function ($builder) {
                $builder->whereNull('data->category')
                    ->orWhereNotIn('data->category', ['reports', 'claims', 'users']);
            })->where(function ($builder) {
                $builder->where('data->title', 'not like', '%laporan%')
                    ->where('data->title', 'not like', '%klaim%')
                    ->where('data->title', 'not like', '%registrasi%')
                    ->where('data->title', 'not like', '%pengguna baru%');
            });
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('data->title', 'like', '%' . $search . '%')
                    ->orWhere('data->message', 'like', '%' . $search . '%')
                    ->orWhere('data->item_code', 'like', '%' . $search . '%')
                    ->orWhere('data->status_label', 'like', '%' . $search . '%');

                if (preg_match('/\bBRG-(\d+)\b/i', $search, $matches) === 1) {
                    $itemId = (int) $matches[1];
                    if ($itemId > 0) {
                        $builder->orWhere(function ($codeQuery) use ($itemId): void {
                            $codeQuery->where('data->category', 'reports')
                                ->where('data->record_id', $itemId);
                        });

                        $claimIds = ItemClaim::query()
                            ->where('lost_found_item_id', $itemId)
                            ->pluck('id');
                        if ($claimIds->isNotEmpty()) {
                            $builder->orWhere(function ($claimQuery) use ($claimIds): void {
                                $claimQuery->where('data->category', 'claims')
                                    ->whereIn('data->record_id', $claimIds->all());
                            });
                        }
                    }
                }
            });
        }

        $notifications = $query
            ->reorder('created_at', $sort === 'oldest' ? 'asc' : 'desc')
            ->orderBy('id', $sort === 'oldest' ? 'asc' : 'desc')
            ->paginate(10)
            // Pagination must always return to the full notification-center page.
            // The same partial is also rendered by the live stats endpoint.
            ->withPath(route('admin.notifications.index'))
            ->withQueryString();

        $notifications->getCollection()->each(function ($notification) {
            $data = $notification->data ?? [];
            $raw = $data['category'] ?? null;
            $text = mb_strtolower(($data['title'] ?? '') . ' ' . ($data['message'] ?? ''));
            $notification->display_category = match ($raw) {
                'reports' => 'reports',
                'claims' => 'claims',
                'users' => 'registrations',
                default => (str_contains($text, 'laporan') ? 'reports'
                    : (str_contains($text, 'klaim') ? 'claims'
                    : ((str_contains($text, 'registrasi') || str_contains($text, 'pengguna baru')) ? 'registrations' : 'system'))),
            };
        });

        $totalCount = (clone $base)->count();
        $unreadCount = (clone $base)->whereNull('read_at')->count();
        $todayStart = now('Asia/Jakarta')->startOfDay()->utc();
        $tomorrowStart = now('Asia/Jakarta')->addDay()->startOfDay()->utc();
        $notificationsToday = (clone $base)->where('created_at', '>=', $todayStart)
            ->where('created_at', '<', $tomorrowStart)->count();
        $readLinks = self::destinations(
            $notifications->getCollection()->filter(fn ($notification) => $notification->read_at !== null),
        );

        return compact('notifications', 'category', 'search', 'sort', 'totalCount', 'unreadCount',
            'notificationsToday', 'readLinks');
    }

    public function feed(): \Illuminate\Http\Response
    {
        return response()->view('admin.partials.notification-bell')
            ->header('Cache-Control', 'private, no-store');
    }

    public function markAsRead(Request $request, string $id): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $notification = $request->user()->notifications()->whereKey($id)->firstOrFail();
        $notification->markAsRead();

        $destination = $this->destination($notification);

        if ($request->expectsJson()) {
            return response()->json(['destination' => $destination]);
        }

        return redirect()->to($destination);
    }

    private const DESTINATIONS = [
        'reports' => ['admin.reports.verification.show', 'admin.items.index'],
        'claims' => ['admin.claims.show', 'admin.claims.index'],
        'chat' => ['admin.chat.show', 'admin.chat.index'],
        'users' => ['admin.users.show', 'admin.users.index'],
    ];

    private static function destinationRecords(string $category): Builder
    {
        return match ($category) {
            'reports' => LostFoundItem::query(),
            'claims' => ItemClaim::query(),
            'chat', 'users' => User::query()->where('role', 'user'),
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
            $records = self::destinationRecords($category)->whereKey(array_unique($ids));
            $existing[$category] = $category === 'reports'
                ? $records->pluck('status', 'id')->all()
                : array_fill_keys($records->pluck('id')->all(), true);
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
        if ($category === 'reports') {
            $status = $existing === null && $id
                ? self::destinationRecords($category)->whereKey($id)->value('status')
                : ($existing[$category][$id] ?? null);

            return match ($status) {
                null => route('admin.items.index'),
                'claimed', 'returned' => route('admin.items.show', $id),
                default => route('admin.reports.verification.show', $id),
            };
        }

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

    public function markAllAsRead(Request $request): RedirectResponse|\Illuminate\Http\Response
    {
        $request->user()->unreadNotifications->markAsRead();

        if ($request->expectsJson()) {
            return response()->noContent();
        }

        return back();
    }
}
