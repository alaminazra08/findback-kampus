@extends('admin.layouts.items')
@section('title', 'Pesan Pengguna')
@section('breadcrumb', 'Pesan Pengguna')
@section('content')
    <link rel="stylesheet" href="{{ asset('css/chat/chat.css') }}?v=3">
    <link rel="stylesheet" href="{{ asset('css/pages/admin/chat/index.css') }}">
    <div class="fb-inbox">
        <h1>Pesan Pengguna</h1>
        <p>Pilih pengguna dan nomor klaim untuk melihat percakapan yang sesuai.</p>
        <div class="fb-inbox-layout {{ $selectedUser ? 'has-selection' : '' }}">
            <aside class="fb-inbox-side">
                <header>
                    <h2>Daftar Percakapan</h2><small>{{ $users->total() }} pengguna</small>
                    <form method="GET" action="{{ route('admin.chat.index') }}" class="fb-inbox-search"><input type="search"
                            name="search" value="{{ $search }}" placeholder="Cari pengguna..."><button
                            type="submit">Cari</button></form>
                </header>
                @forelse($users as $user)
                    <a class="fb-inbox-user {{ $selectedUser && $selectedUser->id === $user->id ? 'active' : '' }}"
                        href="{{ route('admin.chat.index', ['user' => $user->id, 'search' => $search]) }}"><span
                            class="fb-inbox-avatar">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span><span
                            class="fb-inbox-user-info"><strong>{{ $user->name }}</strong><small>{{ $user->email }}</small></span>
                        @if ($user->unread_count)
                            <span class="fb-inbox-count">{{ $user->unread_count }}</span>
                        @endif
                    </a>
                @empty<div class="fb-inbox-empty fb-refactor fb-inbox-empty-height"><strong>Belum Ada
                            Percakapan</strong><span>Pesan pengguna akan muncul setelah proses klaim.</span></div>
                @endforelse
                <div class="fb-inbox-pagination">{{ $users->links() }}</div>
            </aside>
            <section class="fb-inbox-main">
                @if ($selectedUser)
                    <header><a class="fb-inbox-mobile-back" href="{{ route('admin.chat.index') }}">←</a><span
                            class="fb-inbox-avatar">{{ mb_strtoupper(mb_substr($selectedUser->name, 0, 1)) }}</span><span
                            class="fb-refactor fb-inbox-contact-copy"><strong>{{ $selectedUser->name }}</strong><small>{{ $selectedUser->email }}</small></span><span
                            class="fb-inbox-status">{{ $canChat ? 'Chat aktif' : 'Arsip' }}</span></header>
                    <nav class="fb-claim-switch fb-refactor fb-inbox-claim-nav" aria-label="Pilih percakapan klaim">
                        @foreach ($claimOptions as $itemClaim)
                            <a href="{{ route('admin.chat.index', ['user' => $selectedUser->id, 'claim_id' => $itemClaim->id]) }}"
                                class="fb-refactor fb-inbox-claim-link {{ $selectedClaim?->id === $itemClaim->id ? 'is-selected' : '' }}">K{{ $itemClaim->id }}
                                · {{ $itemClaim->item?->title ?? 'Barang' }} ·
                                {{ $itemClaim->status === 'approved' ? 'Chat aktif' : 'Arsip' }}</a>
                        @endforeach
                        <a href="{{ route('admin.chat.index', ['user' => $selectedUser->id, 'legacy' => 1]) }}"
                            class="fb-refactor fb-inbox-archive-link">Arsip Chat Lama</a>
                        @if (!$selectedClaim)
                            <small class="fb-refactor fb-inbox-hint">Pilih nomor klaim untuk melihat pesan. Pesan lama tanpa
                                nomor klaim tetap tersimpan terpisah.</small>
                        @endif
                    </nav>
                    <div class="fb-inbox-messages" id="fb-inbox-messages">
                        @forelse($selectedMessages as $msg)
                            <div class="fb-inbox-bubble {{ (int) $msg->sender_id === (int) auth()->id() ? 'mine' : '' }}">
                                <strong>{{ (int) $msg->sender_id === (int) auth()->id() ? 'Admin' : $selectedUser->name }}</strong>
                                @if ($msg->attachment_path)
                                    <p><a href="{{ \Illuminate\Support\Facades\Storage::url($msg->attachment_path) }}"
                                            target="_blank" rel="noopener noreferrer"><img
                                                src="{{ \Illuminate\Support\Facades\Storage::url($msg->attachment_path) }}"
                                                alt="Lampiran pesan"></a></p>
                                @endif
                                @if ($msg->message)
                                    <p>{{ $msg->message }}</p>
                                @endif
                                <small>{{ $msg->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</small>
                            </div>
                        @empty<div class="fb-inbox-empty"><strong>Belum Ada Pesan</strong><span>Belum ada riwayat pesan
                                    dengan pengguna ini.</span></div>
                        @endforelse
                    </div>
                    @if ($canChat)
                        <form class="fb-inbox-compose" method="POST"
                            action="{{ route('admin.chat.store', $selectedUser->id) }}" enctype="multipart/form-data">
                            @csrf<input type="hidden" name="from_claim" value="{{ $selectedClaim->id }}"><input
                                type="hidden" name="from_inbox" value="1"><input type="file" name="attachment"
                                accept="image/jpeg,image/png,image/webp" aria-label="Lampiran foto">
                            <textarea name="message" maxlength="3000" placeholder="Tulis pesan untuk {{ $selectedUser->name }}..."></textarea><button class="fb-inbox-submit" type="submit">Kirim</button>
                        </form>
                    @else<div class="fb-inbox-note">Riwayat pesan tersimpan. Pengiriman pesan hanya tersedia setelah
                            klaim disetujui dan sebelum barang dikembalikan.</div>
                    @endif
                    @if ($errors->any())
                        <div class="fb-inbox-note">{{ $errors->first() }}</div>
                    @endif
                    @if (session('success'))
                        <div class="fb-inbox-note">{{ session('success') }}</div>
                    @endif
                @else<div class="fb-inbox-empty"><strong>Pilih Percakapan</strong><span>Klik nama pengguna di sebelah
                            kiri untuk melihat seluruh pesannya.</span></div>
                @endif
            </section>
        </div>
    </div>
    <script src="{{ asset('js/pages/admin/chat/index.js') }}"></script>
@endsection
