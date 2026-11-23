@extends('admin.layouts.items')

@section('title', 'Percakapan Pengguna')

@section('breadcrumb', 'Pesan Pengguna')

@section('page-styles')
    <link rel="stylesheet" href="{{ asset('css/pages/admin/chat/message-states.css') }}">
    <link rel="stylesheet" href="{{ asset('css/chat/chat-ui.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('css/chat/chat.css') }}?v=3">
    <link rel="stylesheet" href="{{ asset('css/chat/findback-claim-chat.css') }}?v=20261111">
    <link rel="stylesheet" href="{{ asset('css/chat/claim-chat-premium.css') }}?v=20261120-photo">
    <link rel="stylesheet" href="{{ asset('css/pages/admin/chat/show.css') }}">
    @include('shared.chat-tools-style')
@endsection

@section('content')



    <div class="fb-chat-page">

        <main class="chat-container">

            <a href="{{ route('admin.chat.index') }}" class="chat-back">
                ← Semua Pesan
            </a>

            <section class="chat-panel">

                <div class="chat-panel-header">

                    <div class="admin-avatar">
                        {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                    </div>

                    <div>
                        <h1 class="chat-inbox-name">{{ $user->name }}</h1>
                        <small>{{ $selectedClaim ? 'Klaim K'.$selectedClaim->id.' · '.($selectedClaim->item?->title ?? 'Barang') : $user->email }}</small>
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
                <div class="chat-messages" id="chat-messages" @include('admin.chat.stream-actions')
                    data-receipts-url="{{ $selectedClaim ? route('admin.chat.messages', ['user' => $user->id, 'claim_id' => $selectedClaim->id]) : '' }}"
                    data-messages-url="{{ $messages->currentPage() === 1 && $selectedClaim ? route('admin.chat.messages', ['user' => $user->id, 'claim_id' => $selectedClaim->id]) : '' }}">

                    @forelse($messages->getCollection()->reverse() as $msg)
                        <article
                            class="chat-message {{ (int) $msg->sender_id === (int) auth()->id() ? 'mine' : 'theirs' }}"
                            data-message-id="{{ $msg->id }}" data-sent-at="{{ $msg->created_at->toIso8601String() }}" @if ($msg->read_at) data-read-at="{{ $msg->read_at->toIso8601String() }}" @endif>

                            <div class="chat-bubble" @if ($ratio = $msg->attachmentPhotoRatio()) style="--chat-photo-ratio: {{ $ratio }}" @endif>

                                <span class="chat-sender">
                                    {{ (int) $msg->sender_id === (int) auth()->id() ? 'Admin' : $user->name }}
                                </span>

                               
{{-- Pesan yang sudah ditarik --}}
@if ($msg->recalled_at)

    <p class="chat-recalled">
        Pesan telah ditarik
    </p>

@else

    {{-- Lampiran gambar --}}
    @if ($msg->attachment_path)
        @include('shared.chat-attachment', ['path' => $msg->attachment_path, 'label' => 'Lampiran pesan'])
    @endif

    {{-- Isi pesan --}}
    @if ($msg->message)
        <p>{{ $msg->message }}</p>
    @endif

    {{-- Penanda pesan diedit --}}
    @if ($msg->edited_at)
        <small class="chat-edited">
            Diedit
        </small>
    @endif

@endif


                                @include('admin.chat.message-actions')
                                <small>
                                    {{ $msg->created_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j M Y, H:i') }}
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
                    <div class="fb-chat-action-slot">
                    <form action="{{ route('admin.chat.store', $user->id) }}" method="POST" enctype="multipart/form-data"
                        class="chat-form" data-chat-compose>

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

                        <button type="button" class="chat-upload" data-chat-pick aria-label="Buka menu lampiran" aria-haspopup="dialog">+</button>
                        <input type="file" name="attachment" accept="image/jpeg,image/png,image/webp,video/mp4,video/webm" hidden>

                        <textarea name="message" rows="1" placeholder="Balas pesan pengguna..." maxlength="3000"></textarea>

                        @include('shared.chat-send-button')

                    </form>
                    </div>
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

    <script src="{{ asset('js/pages/admin/chat/show.js') }}?v=chat6"></script>

    
@endsection
