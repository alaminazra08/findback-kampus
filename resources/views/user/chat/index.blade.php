@extends('user.layouts.campus')
@section('title', 'Chat Admin')
@section('content')
    <link rel="stylesheet" href="{{ asset('css/chat/findback-chat-ui-v2.css') }}?v=2">
    <div class="cu-title">
        <h1>Chat admin</h1>
        <p>Berkomunikasi dengan admin untuk verifikasi dan pengaturan serah terima.</p>
    </div>
    @if ($activeClaims->isEmpty() && !$hasHistory)
        <section class="cu-panel cu-empty"><span class="cu-round">@include('user.components.icon', ['icon' => 'chat'])</span>
            <h2>Chat tersedia setelah klaim disetujui</h2>
            <p>Ajukan klaim barang ditemukan. Setelah Admin menyetujui klaim, kamu dapat menghubungi Admin untuk serah
                terima.</p><a class="cu-btn" href="{{ route('user.items.index', ['type' => 'found']) }}">Cari barang
                ditemukan</a>
        </section>
    @else
        <link rel="stylesheet" href="{{ asset('css/chat/findback-claim-chat.css') }}?v=1">
        <section class="cu-chat cu-panel">
            <header><span class="cu-avatar">A</span>
                <div>
                    <h2>Admin FindBack</h2>
                    <p>Tim layanan Fakultas Sains dan Teknologi</p>
                </div><span class="cu-badge green">Percakapan pribadi</span>
            </header>
            <div class="cu-chat-context">
                <strong>{{ $activeClaims->isNotEmpty() ? 'Klaim disetujui untuk serah terima' : 'Arsip percakapan sebelumnya' }}</strong>
                @foreach ($activeClaims as $claim)
                    <a href="{{ route('user.claims.index') }}">#{{ $claim->id }} · {{ $claim->item?->title ?? 'Barang' }}
                        ↗</a>
                @endforeach
                <small>
                    Sebutkan kode barang atau nomor klaim agar konteksnya jelas.</small>
            </div>
            <nav aria-label="Pilih percakapan klaim" class="fb-refactor fb-user-claim-nav">
                @foreach ($claimOptions as $option)
                    <a href="{{ route('user.chat.index', ['claim_id' => $option->id]) }}"
                        class="fb-refactor fb-user-claim-link {{ $selectedClaim?->id === $option->id ? 'is-selected' : '' }}">K{{ $option->id }}
                        · {{ $option->item?->title ?? 'Barang' }} ·
                        {{ $option->status === 'approved' ? 'Aktif' : 'Arsip' }}</a>
                @endforeach
                <a href="{{ route('user.chat.index', ['legacy' => 1]) }}" class="fb-refactor fb-user-archive-link">Arsip
                    Chat
                    Lama</a>
                @if (!$selectedClaim)
                    <small>Pilih klaim untuk membuka percakapan. Pesan lama tetap tersimpan terpisah.</small>
                @endif
            </nav>
            <div class="cu-chat-stream" id="chat-stream" data-after="{{ $messages->max('id') ?? 0 }}"
                data-poll="{{ $messages->currentPage() === 1 && $selectedClaim ? route('user.chat.messages', ['claim_id' => $selectedClaim->id]) : '' }}">
                @foreach ($messages->getCollection()->reverse() as $message)
                    <article class="cu-message {{ (int) $message->sender_id === (int) auth()->id() ? 'mine' : '' }}">
                        <div>
                            <strong>{{ (int) $message->sender_id === (int) auth()->id() ? 'Kamu' : 'Admin FindBack' }}</strong>
                            <p>{{ $message->message }}</p>
                            @if ($message->attachment_path)
                                <a href="{{ asset('storage/' . $message->attachment_path) }}" target="_blank"
                                    rel="noopener"><img src="{{ asset('storage/' . $message->attachment_path) }}"
                                        alt="Lampiran percakapan"></a>
                            @endif
                            <small>
                                {{ $message->created_at->timezone('Asia/Jakarta')->format('d M, H:i') }}</small>
                        </div>
                    </article>
                @endforeach
            </div>
            @include('user.components.pages', ['paginator' => $messages])@if ($canChat)
                <form class="cu-chat-form" method="POST" enctype="multipart/form-data"
                    action="{{ route('user.chat.store') }}">@csrf<input type="hidden" name="claim_id"
                        value="{{ $selectedClaim->id }}"><label class="cu-sr" for="chat-message">Pesan</label>
                    <textarea id="chat-message" name="message" maxlength="3000" rows="2"
                        placeholder="Tulis pesan dan kode barang atau nomor klaim...">{{ old('message') }}</textarea><label class="cu-chat-file">Lampirkan gambar (maks. 4 MB)<input type="file"
                            name="attachment" accept="image/jpeg,image/png,image/webp"></label><button class="cu-btn">Kirim
                        pesan ↗</button>
            </form>@else<div class="fb-chat-archive-note">Percakapan lama tetap dapat dibaca. Untuk mengirim pesan baru,
                    diperlukan klaim aktif.</div>
            @endif
            <p id="chat-state" class="cu-muted">Pesan diperbarui otomatis saat halaman terbuka.</p>
        </section>
    @endif
@endsection
