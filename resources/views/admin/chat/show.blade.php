@extends('admin.layouts.items')

@section('title', 'Percakapan Pengguna')

@section('breadcrumb', 'Pesan Pengguna')

@section('page-styles')
    <link rel="stylesheet" href="{{ asset('css/chat/chat-ui.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('css/chat/chat.css') }}?v=3">
    <link rel="stylesheet" href="{{ asset('css/chat/findback-claim-chat.css') }}?v=20261111">
    <link rel="stylesheet" href="{{ asset('css/chat/claim-chat-premium.css') }}?v=20261111">
    <link rel="stylesheet" href="{{ asset('css/pages/admin/chat/show.css') }}">
@endsection

@section('content')



    <div class="fb-chat-page">

        <main class="chat-container">

            <a href="{{ route('admin.chat.index') }}" class="chat-back">
                ← Semua Pesan
            </a>

            <section class="chat-heading">
                <span class="chat-kicker">RUANG PERCAKAPAN</span>
                <h1>{{ $user->name }}</h1>
                <p>{{ $user->email }}</p>
            </section>

            <section class="chat-panel">

                <div class="chat-panel-header">

                    <div class="admin-avatar">
                        {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                    </div>

                    <div>
                        <strong>{{ $user->name }}</strong>
                        <small>Pengguna FindBack Kampus</small>
                    </div>

                    <span class="chat-secure">{{ $canChat ? 'Klaim disetujui · Chat aktif' : 'Arsip percakapan' }}</span>

                </div>

                <nav aria-label="Pilih klaim" class="fb-refactor fb-chat-claim-nav">
                    @foreach ($claimOptions as $option)
                        <a href="{{ route('admin.chat.show', ['user' => $user->id, 'claim_id' => $option->id]) }}"
                            class="fb-refactor fb-chat-detail-claim-link">K{{ $option->id }} ·
                            {{ $option->item?->title ?? 'Barang' }}</a>
                    @endforeach
                    <a href="{{ route('admin.chat.show', ['user' => $user->id, 'legacy' => 1]) }}"
                        class="fb-refactor fb-chat-archive-link">Arsip Chat Lama</a>
                    @if (!$selectedClaim && !request()->boolean('legacy'))
                        <span>Pilih nomor klaim terlebih dahulu.</span>
                    @endif
                </nav>
                <div class="chat-messages" id="chat-messages"
                    data-messages-url="{{ $messages->currentPage() === 1 && $selectedClaim ? route('admin.chat.messages', ['user' => $user->id, 'claim_id' => $selectedClaim->id]) : '' }}">

                    @forelse($messages->getCollection()->reverse() as $msg)
                        <article
                            class="chat-message {{ (int) $msg->sender_id === (int) auth()->id() ? 'mine' : 'theirs' }}"
                            data-message-id="{{ $msg->id }}">

                            <div class="chat-bubble">

                                <span class="chat-sender">
                                    {{ (int) $msg->sender_id === (int) auth()->id() ? 'Admin' : $user->name }}
                                </span>

                                @if ($msg->attachment_path)
                                    <a href="{{ \Illuminate\Support\Facades\Storage::url($msg->attachment_path) }}"
                                        target="_blank" rel="noopener noreferrer">
                                        <img class="chat-attachment"
                                            src="{{ \Illuminate\Support\Facades\Storage::url($msg->attachment_path) }}"
                                            alt="Lampiran pesan" loading="lazy">
                                    </a>
                                @endif

                                @if ($msg->message)
                                    <p>{{ $msg->message }}</p>
                                @endif

                                <small>
                                    {{ $msg->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}
                                </small>

                            </div>

                        </article>

                    @empty

                        <div class="chat-empty">
                            <div>✉</div>
                            <h3>Belum Ada Pesan</h3>
                            <p>{{ $canChat ? 'Gunakan ruang ini untuk verifikasi kepemilikan dan koordinasi serah terima.' : 'Belum ada riwayat pesan. Chat baru tersedia setelah klaim disetujui.' }}
                            </p>
                        </div>
                    @endforelse

                </div>

                <div class="fb-workflow-pages">{{ $messages->links() }}</div>
                @if ($canChat)
                    <form action="{{ route('admin.chat.store', $user->id) }}" method="POST" enctype="multipart/form-data"
                        class="chat-form">

                        @csrf
                        <input type="hidden" name="from_claim" value="{{ $selectedClaim->id }}">
                        <input type="hidden" name="from_inbox" value="1">

                        @if ($errors->any())
                            <div class="chat-error">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        @if (session('success'))
                            <div class="chat-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        <label class="chat-upload" title="Lampirkan gambar">
                            +
                            <input type="file" name="attachment" accept="image/jpeg,image/png,image/webp">
                        </label>

                        <textarea name="message" rows="1" placeholder="Balas pesan pengguna..." maxlength="3000"></textarea>

                        <button type="submit">Kirim ↑</button>

                    </form>
                @else
                    <div class="fb-chat-archive-note">Riwayat pesan tetap dapat dibaca. Pengiriman pesan hanya tersedia
                        setelah klaim disetujui dan sebelum barang dikembalikan.</div>
                @endif
            </section>

            <footer class="chat-footer">
                © {{ date('Y') }} FindBack Kampus ·
                Fakultas Sains dan Teknologi
            </footer>

        </main>

    </div>

    <script src="{{ asset('js/pages/admin/chat/show.js') }}"></script>

@endsection
