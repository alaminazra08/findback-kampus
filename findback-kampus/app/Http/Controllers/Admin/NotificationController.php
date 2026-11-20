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
            $this->applyNotificationSearch($query, $search);
        }

        $notifications = $query
            ->orderBy('created_at', $sort === 'oldest' ? 'asc' : 'desc')
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
        $statusSnapshots = self::currentStatusSnapshots($notifications->getCollection());
        $notifications->getCollection()->each(function ($notification) use ($statusSnapshots): void {
            $snapshot = $statusSnapshots[$notification->id] ?? null;
            if ($snapshot) {
                $notification->current_message = $snapshot['message'];
                $notification->current_status_key = $snapshot['status_key'];
                $notification->current_status_label = $snapshot['status_label'];
            }
        });
        $readLinks = self::destinations(
            $notifications->getCollection()->filter(fn ($notification) => $notification->read_at !== null),
        );

        return view('admin.notifications.index', compact(
            'notifications', 'category', 'search', 'sort', 'totalCount', 'unreadCount',
            'notificationsToday', 'readLinks',
        ));
    }

    public function stats(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'category' => ['nullable', 'in:all,unread,reports,claims,registrations,system'],
            'q' => ['nullable', 'string', 'max:120'],
            'sort' => ['nullable', 'in:newest,oldest'],
        ]);

        $base = $request->user()->notifications();
        $category = $validated['category'] ?? 'all';
        $search = trim($validated['q'] ?? '');
        $sort = $validated['sort'] ?? 'newest';

        // Rebuild the currently selected tab/search/sort on every poll.
        // This is what makes new notifications appear in Semua and their
        // matching category at the same time without a page reload.
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
            $this->applyNotificationSearch($query, $search);
        }

        $notifications = $query
            ->orderBy('created_at', $sort === 'oldest' ? 'asc' : 'desc')
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

        $snapshots = self::currentStatusSnapshots($notifications->getCollection());
        $notifications->getCollection()->each(function ($notification) use ($snapshots): void {
            $snapshot = $snapshots[$notification->id] ?? null;
            if ($snapshot) {
                $notification->current_message = $snapshot['message'];
                $notification->current_status_key = $snapshot['status_key'];
                $notification->current_status_label = $snapshot['status_label'];
            }
        });

        $readLinks = self::destinations(
            $notifications->getCollection()->filter(fn ($notification) => $notification->read_at !== null),
        );
        $listHtml = view('admin.notifications.partials.live-list', compact(
            'notifications', 'category', 'search', 'readLinks',
        ))->render();

        $start = now('Asia/Jakarta')->startOfDay()->utc();
        $end = now('Asia/Jakarta')->addDay()->startOfDay()->utc();

        return response()->json([
            'total' => (clone $base)->count(),
            'unread' => (clone $base)->whereNull('read_at')->count(),
            'today' => (clone $base)->where('created_at', '>=', $start)
                ->where('created_at', '<', $end)->count(),
            'date' => now('Asia/Jakarta')->toDateString(),
            'statuses' => $snapshots,
            'list_html' => $listHtml,
        ])->header('Cache-Control', 'private, no-store');
    }

    /**
     * Search notification text and generated item codes (for example BRG-0055).
     * The visible report code is derived from the related item's database ID,
     * so it is not necessarily present in the original notification JSON text.
     */
    private function applyNotificationSearch(Builder $query, string $search): void
    {
        $query->where(function (Builder $builder) use ($search): void {
            $builder->where('data->title', 'like', '%' . $search . '%')
                ->orWhere('data->message', 'like', '%' . $search . '%');

            if (preg_match('/\\bBRG-(\\d+)\\b/i', $search, $matches) !== 1) {
                return;
            }

            $itemId = (int) $matches[1];
            if ($itemId < 1) {
                return;
            }

            // Report notifications store the lost/found item ID as record_id.
            $builder->orWhere(function (Builder $codeQuery) use ($itemId): void {
                $codeQuery->where('data->category', 'reports')
                    ->where('data->record_id', $itemId);
            });

            // Claim notifications store the claim ID, while the BRG code
            // belongs to the related item. Resolve matching claim IDs first.
            $claimIds = ItemClaim::query()
                ->where('lost_found_item_id', $itemId)
                ->pluck('id');

            if ($claimIds->isNotEmpty()) {
                $builder->orWhere(function (Builder $codeQuery) use ($claimIds): void {
                    $codeQuery->where('data->category', 'claims')
                        ->whereIn('data->record_id', $claimIds->all());
                });
            }
        });
    }

    /**
     * Build live notification messages from the current workflow records.
     * The original database notification remains an immutable event record;
     * this snapshot only keeps the admin's view synchronized with current state.
     */
    public static function currentStatusSnapshots(Collection $notifications): array
    {
        $reportIds = [];
        $claimIds = [];

        foreach ($notifications as $notification) {
            $data = $notification->data ?? [];
            $recordId = (int) ($data['record_id'] ?? 0);
            if (!$recordId) {
                continue;
            }
            if (($data['category'] ?? null) === 'reports') {
                $reportIds[] = $recordId;
            } elseif (($data['category'] ?? null) === 'claims') {
                $claimIds[] = $recordId;
            }
        }

        $reports = $reportIds
            ? LostFoundItem::query()->whereIn('id', array_unique($reportIds))->get(['id', 'title', 'status'])->keyBy('id')
            : collect();
        $claims = $claimIds
            ? ItemClaim::query()->with('item:id,title')->whereIn('id', array_unique($claimIds))->get(['id', 'lost_found_item_id', 'status'])->keyBy('id')
            : collect();

        $snapshots = [];
        foreach ($notifications as $notification) {
            $data = $notification->data ?? [];
            $category = $data['category'] ?? null;
            $recordId = (int) ($data['record_id'] ?? 0);

            if ($category === 'reports' && ($record = $reports->get($recordId))) {
                $state = match ($record->status) {
                    'pending' => 'menunggu verifikasi',
                    'verified' => 'telah disetujui',
                    'rejected' => 'ditolak',
                    'claimed' => 'sedang dalam proses klaim',
                    'returned' => 'telah dikembalikan',
                    default => \App\Support\WorkflowStatus::report($record->status),
                };
                $snapshots[$notification->id] = [
                    'message' => "Laporan " . \App\Support\WorkflowStatus::code((int) $record->id) . ": {$record->title} {$state}.",
                    'status_key' => $record->status,
                    'status_label' => \App\Support\WorkflowStatus::report($record->status),
                ];
            } elseif ($category === 'claims' && ($record = $claims->get($recordId))) {
                $state = match ($record->status) {
                    'pending' => 'menunggu pemeriksaan',
                    'approved' => 'telah disetujui',
                    'rejected' => 'ditolak',
                    'returned' => 'telah selesai dikembalikan',
                    default => \App\Support\WorkflowStatus::claim($record->status),
                };
                $snapshots[$notification->id] = [
                    'message' => "Klaim untuk barang " . \App\Support\WorkflowStatus::code((int) $record->lost_found_item_id) . " {$state}.",
                    'status_key' => $record->status,
                    'status_label' => \App\Support\WorkflowStatus::claim($record->status),
                ];
            }
        }

        return $snapshots;
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
