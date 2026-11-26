@php
    $adminNotifications = auth()->user()->notifications()->latest()->orderByDesc('id')->limit(4)->get();
    $links = \App\Http\Controllers\Admin\NotificationController::destinations(
        $adminNotifications->filter(fn ($notification) => $notification->read_at !== null)
    );
    $unreadCount = auth()->user()->unreadNotifications()->count();
    $claimChatUnread = \App\Models\ChatMessage::query()
        ->whereNull('read_at')
        ->whereNull('recalled_at')
        ->whereColumn('sender_id', 'user_id')
        ->whereHas('claim', fn ($query) => $query->where('status', 'approved')
            ->whereColumn('item_claims.user_id', 'chat_messages.user_id'))
        ->selectRaw('claim_id, COUNT(*) as unread_total')
        ->groupBy('claim_id')
        ->pluck('unread_total', 'claim_id');
@endphp
<div class="fb-notif" id="fb-notif" data-fb-claim-unread="{{ $claimChatUnread->toJson() }}" data-feed="{{ route('admin.notifications.feed') }}">
    <button class="fb-notif-trigger fb-notification-bell" type="button" id="fb-notif-trigger"
        aria-label="Notifikasi{{ $unreadCount ? ', ' . $unreadCount . ' belum dibaca' : '' }}" aria-expanded="false"
        aria-controls="fb-notif-panel" aria-haspopup="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
            stroke-linejoin="round" aria-hidden="true">
            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4" />
        </svg>
        @if ($unreadCount > 0)
            <span class="fb-notif-count">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
        @endif
    </button>
    <div class="fb-notif-panel fb-notice-preview" id="fb-notif-panel" hidden>
        <div class="fb-notif-head fb-notice-preview-head">
            <strong>Notifikasi @if ($unreadCount)
                    ({{ $unreadCount }})
                @endif
            </strong>

        </div>
        @if ($adminNotifications->isEmpty())
            <p class="fb-notif-empty">Belum ada pemberitahuan.</p>
        @else
            <ul class="fb-notif-list fb-notice-preview-list">
                @foreach ($adminNotifications as $notification)
                    <li data-notification-id="{{ $notification->id }}" data-category="{{ $notification->data['category'] ?? '' }}"
                        class="fb-notif-item fb-notice-preview-row {{ $notification->read_at ? '' : 'is-unread' }}">
                        @if (!$notification->read_at)
                            <form class="fb-notice-preview-row-form" method="POST" action="{{ route('admin.notifications.read', $notification->id) }}">
                                @csrf @method('PATCH')
                                <button class="fb-notice-preview-row-button" type="submit"
                                    aria-label="Tandai dibaca dan buka: {{ \App\Support\WorkflowStatus::notificationText($notification->data['title'] ?? 'Notifikasi') }}">
                                    <span class="fb-notif-dot" aria-hidden="true"></span>
                                    <span class="fb-notif-content fb-notice-preview-copy">
                                        <strong>{{ \App\Support\WorkflowStatus::notificationText($notification->data['title'] ?? 'Pemberitahuan FindBack') }}</strong>
                                        <span class="fb-notice-preview-message">{{ \App\Support\WorkflowStatus::notificationText($notification->data['message'] ?? 'Ada pembaruan baru.') }}</span>
                                        <time datetime="{{ $notification->created_at->toIso8601String() }}">{{ $notification->created_at->diffForHumans() }}</time>
                                    </span>
                                </button>
                            </form>
                        @else
                            <a class="fb-notice-preview-row-link" href="{{ $links[$notification->id] }}" aria-label="Buka notifikasi: {{ \App\Support\WorkflowStatus::notificationText($notification->data['title'] ?? 'Notifikasi') }}">
                                <span class="fb-notif-dot" aria-hidden="true"></span>
                                <span class="fb-notif-content fb-notice-preview-copy">
                                    <strong>{{ \App\Support\WorkflowStatus::notificationText($notification->data['title'] ?? 'Pemberitahuan FindBack') }}</strong>
                                    <span class="fb-notice-preview-message">{{ \App\Support\WorkflowStatus::notificationText($notification->data['message'] ?? 'Ada pembaruan baru.') }}</span>
                                    <time datetime="{{ $notification->created_at->toIso8601String() }}">{{ $notification->created_at->diffForHumans() }}</time>
                                </span>
                            </a>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
        <a class="fb-notif-view-all fb-notice-preview-all" href="{{ route('admin.notifications.index') }}">
            Lihat semua notifikasi <span aria-hidden="true">→</span>
        </a>
    </div>
</div>
