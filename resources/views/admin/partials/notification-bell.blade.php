@php
    $adminNotifications = auth()->user()->notifications()->latest()->orderByDesc('id')->limit(8)->get();
    $links = \App\Http\Controllers\Admin\NotificationController::destinations(
        $adminNotifications->filter(fn ($notification) => $notification->read_at !== null)
    );
    $unreadCount = auth()->user()->unreadNotifications()->count();
@endphp
<div class="fb-notif" id="fb-notif" data-feed="{{ route('admin.notifications.feed') }}">
    <button class="fb-notif-trigger" type="button" id="fb-notif-trigger"
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
    <div class="fb-notif-panel" id="fb-notif-panel" hidden>
        <div class="fb-notif-head">
            <strong>Notifikasi @if ($unreadCount)
                    ({{ $unreadCount }})
                @endif
            </strong>
            @if ($unreadCount)
                <form action="{{ route('admin.notifications.read-all') }}" method="POST">@csrf
                    <button class="fb-notif-action" type="submit">Tandai semua dibaca</button>
                </form>
            @endif
        </div>
        @if ($adminNotifications->isEmpty())
            <p class="fb-notif-empty">Belum ada pemberitahuan.</p>
        @else
            <ul class="fb-notif-list">
                @foreach ($adminNotifications as $notification)
                    <li data-notification-id="{{ $notification->id }}" data-category="{{ $notification->data['category'] ?? '' }}"
                        class="fb-notif-item {{ $notification->read_at ? '' : 'is-unread' }}">
                        <span class="fb-notif-dot" aria-hidden="true"></span>
                        <div class="fb-notif-content">
                            <strong>{{ $notification->data['title'] ?? 'Pemberitahuan FindBack' }}</strong>
                            <p>{{ $notification->data['message'] ?? 'Ada pembaruan baru.' }}</p>
                            <time
                                datetime="{{ $notification->created_at->toIso8601String() }}">{{ $notification->created_at->diffForHumans() }}</time>
                        </div>
                        @if (!$notification->read_at)
                            <form method="POST" action="{{ route('admin.notifications.read', $notification->id) }}">
                                @csrf @method('PATCH')
                                <button class="fb-notif-action" type="submit"
                                    aria-label="Baca dan buka: {{ $notification->data['title'] ?? 'Notifikasi' }}">Buka
                                    →</button>
                            </form>
                        @else
                            <a class="fb-notif-action"
                                href="{{ $links[$notification->id] }}"
                                aria-label="Buka kembali: {{ $notification->data['title'] ?? 'Notifikasi' }}">Buka
                                →</a>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
