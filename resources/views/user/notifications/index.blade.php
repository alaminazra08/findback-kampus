
@extends('user.layouts.campus')

@section('title', 'Notifikasi')

@section('content')
    @php
        $chatUnread = \App\Models\ChatMessage::where('user_id', auth()->id())
            ->where('sender_id', '!=', auth()->id())
            ->whereNull('read_at')
            ->count();

        $totalUnread = auth()->user()->unreadNotifications()->count();
        $totalNotifications = auth()->user()->notifications()->count();
    @endphp

    <div class="fb-notif-v12">

        {{-- HEADER HALAMAN --}}
        <div class="cu-section-title">
            <div class="cu-title">
                <span class="fb-notif-eyebrow">PUSAT AKTIVITAS</span>
                <h1>Notifikasi</h1>
                <p>Informasi terbaru tentang laporan, klaim, dan aktivitas akunmu.</p>
            </div>

            @if ($totalUnread > 0)
                <div class="fb-notif-new">
                    @include('user.components.icon', ['icon' => 'bell'])
                    <span>{{ $totalUnread }} belum dibaca</span>
                </div>
            @endif
        </div>

        {{-- INFORMASI CHAT ADMIN --}}
        @if ($chatUnread > 0)
            <div class="cu-alert success fb-notif-chat-alert">
                <div>
                    <strong>{{ $chatUnread }} pesan admin belum dibaca.</strong>
                    <p>Ada pesan baru terkait aktivitas klaimmu.</p>
                </div>
                <a href="{{ route('user.chat.index') }}">
                    Buka Chat Klaim →
                </a>
            </div>
        @endif

        {{-- RINGKASAN NOTIFIKASI --}}
        <div class="fb-notif-summary">

            <div class="fb-notif-summary-card">
                <div class="fb-notif-summary-icon">
                    @include('user.components.icon', ['icon' => 'bell'])
                </div>
                <div>
                    <span>Total Notifikasi</span>
                    <strong>{{ $totalNotifications }}</strong>
                </div>
            </div>

            <div class="fb-notif-summary-card">
                <div class="fb-notif-summary-icon unread-icon">
                    @include('user.components.icon', ['icon' => 'clock'])
                </div>
                <div>
                    <span>Belum Dibaca</span>
                    <strong>{{ $totalUnread }}</strong>
                </div>
            </div>

            <div class="fb-notif-summary-card">
                <div class="fb-notif-summary-icon read-icon">
                    @include('user.components.icon', ['icon' => 'check'])
                </div>
                <div>
                    <span>Sudah Dibaca</span>
                    <strong>{{ max(0, $totalNotifications - $totalUnread) }}</strong>
                </div>
            </div>

        </div>

        {{-- DAFTAR NOTIFIKASI --}}
        <section class="cu-panel fb-notif-panel">

            <div class="fb-notif-panel-header">
                <div>
                    <h2>Semua Notifikasi</h2>
                    <p>Daftar informasi terbaru dari FindBack Kampus.</p>
                </div>

                @if ($totalUnread > 0)
                    <form method="POST"
                        action="{{ route('user.notifications.readAll') }}"
                        onsubmit="return confirm('Tandai semua notifikasi sebagai sudah dibaca?')">
                        @csrf

                        <button type="submit" class="cu-btn light fb-notif-read-all">
                            @include('user.components.icon', ['icon' => 'check'])
                            Tandai Semua Dibaca
                        </button>
                    </form>
                @endif
            </div>

            <div class="fb-notif-list">

                @forelse ($notifications as $notification)

                    @php
                        $data = $notification->data ?? [];

                        $title = $data['title'] ?? 'Pemberitahuan';
                        $message = $data['message'] ?? 'Ada informasi terbaru untuk akunmu.';

                        $isUnread = is_null($notification->read_at);
                        $isClaim = !empty($data['claim_id']);
                        $itemId = $data['item_id'] ?? null;

                        if ($isClaim) {
                            $category = 'Klaim';
                            $icon = 'check';
                            $targetUrl = route('user.claims.index');
                        } elseif (!empty($itemId)) {
                            $category = 'Laporan';
                            $icon = 'box';
                            $targetUrl = route('user.reports.show', (int) $itemId);
                        } else {
                            $category = 'Sistem';
                            $icon = 'bell';
                            $targetUrl = route('user.reports.index');
                        }
                    @endphp

                    <article class="cu-notification fb-notif-item {{ $isUnread ? 'unread' : 'read' }}">

                        {{-- IKON NOTIFIKASI --}}
                        <div class="cu-round fb-notif-icon">
                            @include('user.components.icon', ['icon' => $icon])
                        </div>

                        {{-- KONTEN NOTIFIKASI --}}
                        <div class="fb-notif-content">

                            <div class="fb-notif-title-row">
                                <div class="fb-notif-title-group">
                                    <h3>{{ $title }}</h3>

                                    <span class="fb-notif-category category-{{ strtolower($category) }}">
                                        {{ $category }}
                                    </span>
                                </div>

                                @if ($isUnread)
                                    <span class="fb-notif-unread-label">
                                        Baru
                                    </span>
                                @endif
                            </div>

                            <p>{{ $message }}</p>

                            <div class="fb-notif-time">
                                @include('user.components.icon', ['icon' => 'clock'])

                                <span>
                                    {{ $notification->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                                </span>
                            </div>

                            {{-- AKSI NOTIFIKASI --}}
                            <div class="cu-notice-links">

                                <a href="{{ $targetUrl }}" class="fb-notif-detail">
                                    Lihat Aktivitas →
                                </a>

                                @if ($isUnread)
                                    <form method="POST"
                                        action="{{ route('user.notifications.read', $notification->id) }}">
                                        @csrf

                                        <button type="submit" class="cu-link">
                                            Tandai Dibaca
                                        </button>
                                    </form>
                                @endif

                            </div>

                        </div>

                        {{-- INDIKATOR BELUM DIBACA --}}
                        @if ($isUnread)
                            <span class="fb-notif-dot" title="Belum dibaca"></span>
                        @endif

                    </article>

                @empty

                    {{-- TAMPILAN JIKA BELUM ADA NOTIFIKASI --}}
                    <div class="cu-empty fb-notif-empty">

                        <div class="fb-notif-empty-icon">
                            @include('user.components.icon', ['icon' => 'bell'])
                        </div>

                        <h2>Belum Ada Notifikasi</h2>

                        <p>
                            Saat ini belum ada informasi terbaru.
                            Perubahan status laporan dan klaim akan ditampilkan di sini.
                        </p>

                        <a href="{{ route('user.reports.index') }}" class="cu-btn light">
                            Lihat Laporan Saya
                        </a>

                    </div>

                @endforelse

            </div>

            {{-- PAGINATION --}}
            @if ($notifications->hasPages())
                <div class="fb-notif-pagination">
                    <div class="fb-notif-pagination-info">
                        Menampilkan
                        {{ $notifications->firstItem() }}–{{ $notifications->lastItem() }}
                        dari {{ $notifications->total() }} notifikasi
                    </div>

                    @include('user.components.pages', [
                        'paginator' => $notifications
                    ])
                </div>
            @endif

        </section>

    </div>
@endsection
