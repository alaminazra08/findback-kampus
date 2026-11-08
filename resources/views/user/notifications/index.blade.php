@extends('user.layouts.campus')
@section('title', 'Notifikasi')
@section('content')
    <div class="fb-notif-v12">
        <div class="cu-section-title">
            <div class="cu-title">
                <h1>Notifikasi</h1>
                <p>Informasi terbaru tentang laporan dan klaimmu.</p>
            </div>
            <form method="POST" action="{{ route('user.notifications.readAll') }}">@csrf<button class="cu-btn light">Tandai
                    semua dibaca</button></form>
        </div>@php($chatUnread = \App\Models\ChatMessage::where('user_id', auth()->id())->where('sender_id', '!=', auth()->id())->whereNull('read_at')->count())
        @if ($chatUnread)
            <div class="cu-alert success">{{ $chatUnread }} pesan admin belum dibaca. <a
                    href="{{ route('user.chat.index') }}">Buka Chat Klaim →</a></div>
        @endif
        <section class="cu-panel">
            @forelse($notifications as $notification)
                <article class="cu-notification {{ $notification->read_at ? '' : 'unread' }}"><span
                        class="cu-round">@include('user.components.icon', ['icon' => 'bell'])</span>
                    <div>
                        <h3>{{ $notification->data['title'] ?? 'Pemberitahuan' }}</h3>
                        <p>{{ $notification->data['message'] ?? '' }}</p>
                        <small>{{ $notification->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }}</small>
                        <div class="cu-notice-links"><a
                                href="{{ isset($notification->data['claim_id']) ? route('user.claims.index') : (!empty($notification->data['item_id']) ? route('user.reports.show', (int) $notification->data['item_id']) : route('user.reports.index')) }}">Lihat
                                aktivitas →</a>
                            @unless ($notification->read_at)
                                <form method="POST" action="{{ route('user.notifications.read', $notification->id) }}">
                                    @csrf<button class="cu-link">Tandai dibaca</button></form>
                            @endunless
                        </div>
                    </div>
            </article>@empty<div class="cu-empty">
                    <h2>Belum ada notifikasi</h2>
                    <p>Perubahan status laporan dan klaim akan tampil di sini.</p>
                </div>
            @endforelse
            @include('user.components.pages', ['paginator' => $notifications])
        </section>
        <!-- FINDBACK_NOTIFIKASI_V12_CLOSE -->
    </div>
@endsection
