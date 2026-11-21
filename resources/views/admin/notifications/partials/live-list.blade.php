@php
    $categoryLabels = [
        'reports' => 'Laporan',
        'claims' => 'Klaim',
        'registrations' => 'Registrasi',
        'system' => 'Sistem',
    ];
    $categoryIcons = [
        'reports' => 'M12 3 3 8l9 5 9-5-9-5Zm-9 9 9 5 9-5M3 16l9 5 9-5',
        'claims' => 'M8 3h8M8 2h8v4H8zM6 4H5a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-1M8 12h8M8 16h5',
        'registrations' => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM19 8v6M16 11h6',
        'system' => 'M12 3v3m0 12v3M3 12h3m12 0h3M5.64 5.64l2.12 2.12m8.48 8.48 2.12 2.12m0-12.72-2.12 2.12m-8.48 8.48-2.12 2.12M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8Z',
    ];
@endphp

<div data-fb-live-list-content>
@if ($notifications->isEmpty())
                <div class="fb-notif-center-empty">
                    <span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg></span>
                    <strong>Belum ada notifikasi</strong>
                    <p>Notifikasi yang sesuai dengan filter ini akan muncul di sini.</p>
                    @if ($category !== 'all' || $search !== '')
                        <a href="{{ route('admin.notifications.index') }}">Reset filter</a>
                    @endif
                </div>
            @else
                <div class="fb-notif-center-list">
                    @foreach ($notifications as $notification)
                        @php
                            $kind = $notification->display_category;
                            $isUnread = $notification->read_at === null;
                            $title = \App\Support\WorkflowStatus::notificationText($notification->data['title'] ?? 'Pemberitahuan FindBack');
                            $message = \App\Support\WorkflowStatus::notificationText($notification->data['message'] ?? 'Ada pembaruan baru.');
                        @endphp
                        <article class="fb-notif-center-row {{ $isUnread ? 'is-unread' : '' }}"
                            data-fb-notification-id="{{ $notification->id }}"
                            data-fb-notification-category="{{ $notification->data['category'] ?? '' }}"
                            data-fb-record-id="{{ $notification->data['record_id'] ?? '' }}">
                            <span class="fb-notif-center-dot" aria-hidden="true"></span>
                            <span class="fb-notif-center-icon is-{{ $kind }}">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="{{ $categoryIcons[$kind] }}"/></svg>
                            </span>
                            <div class="fb-notif-center-copy">
                                <div class="fb-notif-center-name-row">
                                    <h2>{{ $title }}</h2>
                                    @if ($isUnread)<span class="fb-notif-new-label">Baru</span>@endif
                                </div>
                                <p data-fb-notification-message>{{ $message }}</p>
                                <time datetime="{{ $notification->created_at->toIso8601String() }}" data-fb-relative-time>{{ $notification->created_at->diffForHumans() }}</time>
                            </div>
                            <div class="fb-notif-center-meta">
                                <div class="fb-notif-center-badges">
                                    <span class="fb-notif-category-badge is-{{ $kind }}">{{ $categoryLabels[$kind] }}</span>
                                    @if (!empty($notification->data['status_label']))
                                        <span class="fb-notif-status-badge is-{{ in_array($notification->data['status_key'] ?? '', ['verified', 'approved', 'returned'], true) ? 'success' : (in_array($notification->data['status_key'] ?? '', ['rejected'], true) ? 'danger' : 'pending') }}">
                                            {{ $notification->data['status_label'] }}
                                        </span>
                                    @endif
                                </div>
                                <div class="fb-notif-center-action">
                                    @if ($isUnread)
                                        <form method="POST" action="{{ route('admin.notifications.read', $notification->id) }}">
                                            @csrf @method('PATCH')
                                            @include('admin.partials.detail-action', ['detailUrl' => null])
                                        </form>
                                    @else
                                        @include('admin.partials.detail-action', ['detailUrl' => $readLinks[$notification->id] ?? route('admin.dashboard')])
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="fb-notif-center-footer">
                    <p>Menampilkan {{ $notifications->firstItem() ?? 0 }}–{{ $notifications->lastItem() ?? 0 }} dari {{ $notifications->total() }} notifikasi</p>
                    <nav class="fb-notif-center-pagination" aria-label="Navigasi halaman notifikasi">
                        @php
                            $currentPage = $notifications->currentPage();
                            $lastPage = $notifications->lastPage();
                            $startPage = max(1, $currentPage - 1);
                            $endPage = min($lastPage, $currentPage + 1);
                        @endphp

                        <a class="fb-page-link fb-page-prev {{ $notifications->onFirstPage() ? 'is-disabled' : '' }}"
                            href="{{ $notifications->onFirstPage() ? '#' : $notifications->previousPageUrl() }}"
                            @if ($notifications->onFirstPage()) aria-disabled="true" tabindex="-1" @endif
                            aria-label="Halaman sebelumnya">‹</a>

                        @if ($startPage > 1)
                            <a class="fb-page-link" href="{{ $notifications->url(1) }}">1</a>
                            @if ($startPage > 2)
                                <span class="fb-page-ellipsis" aria-hidden="true">…</span>
                            @endif
                        @endif

                        @foreach (range($startPage, $endPage) as $page)
                            <a class="fb-page-link {{ $page === $currentPage ? 'is-active' : '' }}"
                                href="{{ $notifications->url($page) }}"
                                @if ($page === $currentPage) aria-current="page" @endif>{{ $page }}</a>
                        @endforeach

                        @if ($endPage < $lastPage)
                            @if ($endPage < $lastPage - 1)
                                <span class="fb-page-ellipsis" aria-hidden="true">…</span>
                            @endif
                            <a class="fb-page-link" href="{{ $notifications->url($lastPage) }}">{{ $lastPage }}</a>
                        @endif

                        <a class="fb-page-link fb-page-next {{ $notifications->hasMorePages() ? '' : 'is-disabled' }}"
                            href="{{ $notifications->nextPageUrl() ?? '#' }}"
                            @if (!$notifications->hasMorePages()) aria-disabled="true" tabindex="-1" @endif
                            aria-label="Halaman berikutnya">›</a>
                    </nav>
                </div>
            @endif
</div>
