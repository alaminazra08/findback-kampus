
@extends('user.layouts.campus')

@section('title', 'Chat Admin')

@section('content')

    


{{-- Halaman utama sebelum membuka percakapan --}}
<main class="fb-chat-landing" id="fb-chat-landing">
    <div class="fb-chat-landing-card">

        <span class="fb-chat-eyebrow">
            KOMUNIKASI SERAH TERIMA
        </span>

        <h1>Chat dengan Admin FindBack</h1>

        <p>
            Pilih percakapan klaim untuk menghubungi
            Admin atau membaca arsip pesan.
        </p>

        @if ($activeClaims->isEmpty() && !$hasHistory)
            <p>
                Chat tersedia setelah klaim disetujui Admin.
            </p>

            <a class="fb-chat-primary"
               href="{{ route('user.claims.index') }}">
                Lihat klaim saya
            </a>
        @else
            <div class="fb-chat-choices">
                @foreach ($claimOptions as $option)
                    <a
                        href="{{ route('user.chat.index', [
                            'claim_id' => $option->id
                        ]) }}"
                        class="fb-chat-choice"
                    >
                        <strong>
                            K{{ $option->id }} ·
                            {{ $option->item?->title ?? 'Barang' }}
                        </strong>

                        <small>
                            {{ $option->status === 'approved'
                                ? 'Aktif'
                                : 'Arsip' }}
                        </small>
                    </a>
                @endforeach

                <a class="fb-chat-choice"
                   href="{{ route('user.chat.index', [
                       'legacy' => 1
                   ]) }}">
                    Arsip chat lama
                </a>
            </div>
        @endif

    </div>
</main>

{{-- Modal hanya tampil ketika percakapan dipilih --}}
@if ($selectedClaim || request()->boolean('legacy'))

<div class="fb-chat-overlay" id="fb-chat-overlay">

    <section class="fb-chat-modal"
             role="dialog"
             aria-modal="true"
             aria-labelledby="fb-modal-title">

        <header class="fb-chat-modal-head fb-chat-compact-header">
            <span class="fb-chat-avatar" aria-hidden="true">A</span>
            <div class="fb-chat-contact-copy">
                <h2 id="fb-modal-title">Admin FindBack</h2>
                <p>{{ $selectedClaim ? 'Klaim K'.$selectedClaim->id.' · '.($selectedClaim->item?->title ?? 'Barang') : 'Arsip Chat Lama' }}</p>
            </div>
            <div class="fb-chat-head-actions">
                @unless ($canChat)
                    <span class="fb-chat-status">Arsip</span>
                @endunless
                <a href="{{ route('user.chat.index') }}" class="fb-chat-close" aria-label="Tutup percakapan">&times;</a>
            </div>
        </header>

        {{-- Pemberitahuan --}}
        @if (session('success'))
            <p class="fb-chat-flash" role="status">
                {{ session('success') }}
            </p>
        @endif

        @if ($errors->any())
            <p class="fb-chat-flash is-error" role="alert">
                {{ $errors->first() }}
            </p>
        @endif

        {{-- Daftar pesan --}}
        <div
            class="fb-chat-messages"
            id="fb-modal-stream"
            

            data-receipts-url="{{ $selectedClaim ? route('user.chat.messages', ['claim_id' => $selectedClaim->id]) : '' }}"
            data-poll="{{ $messages->currentPage() === 1
                && $selectedClaim
                ? route('user.chat.messages', [
                    'claim_id' => $selectedClaim->id
                  ])
                : '' }}"

            data-chat-token="{{ csrf_token() }}"
            data-update-url="{{ route(
                'user.chat.messages.update',
                ['message' => '__MESSAGE_ID__']
            ) }}"

            data-recall-url="{{ route(
                'user.chat.messages.recall',
                ['message' => '__MESSAGE_ID__']
            ) }}"

            data-hide-url="{{ route(
                'user.chat.messages.hide',
                ['message' => '__MESSAGE_ID__']
            ) }}"
        >

            @forelse (
                $messages->getCollection()->reverse()
                as $message
            )

                @if (!$message->hidden_for_user)

                    @php
                        $isMine =
                            (int) $message->sender_id ===
                            (int) auth()->id();
                    @endphp

                    <article
                        class="fb-chat-bubble-row {{ $isMine ? 'mine' : '' }} {{ $message->recalled_at ? 'is-recalled' : '' }}"
                        @if ($isMine && $message->recallDeadline())
                            data-recall-until="{{ $message->recallDeadline()->toIso8601String() }}"
                        @endif
                        data-id="{{ $message->id }}" data-sent-at="{{ $message->created_at->toIso8601String() }}" @if ($message->read_at) data-read-at="{{ $message->read_at->toIso8601String() }}" @endif
                    >

                        <div class="fb-chat-bubble" @if ($ratio = $message->attachmentPhotoRatio()) style="--chat-photo-ratio: {{ $ratio }}" @endif>

                            <strong>
                                {{ $isMine
                                    ? 'Kamu'
                                    : 'Admin FindBack' }}
                            </strong>

                            @if ($message->recalled_at)
                                <p class="fb-chat-recalled">Pesan telah ditarik</p>
                            @else

                                @if ($message->message)
                                    <p class="fb-chat-text">{{ $message->message }}</p>
                                @endif

                                @if ($message->attachment_path)
                                    @include('shared.chat-attachment', ['path' => $message->attachment_path, 'label' => 'Lampiran gambar'])
                                @endif

                            @endif

                            <button type="button" class="fb-chat-options" aria-label="Opsi pesan" data-user-chat-options>⌄</button>
                            <footer>
                                <time datetime="{{ $message->created_at->toIso8601String() }}"
                                      title="{{ $message->created_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j M Y, H:i') }} WIB"
                                      data-compact-time="true">{{ $message->created_at->timezone('Asia/Jakarta')->format('H.i') }}</time>

                               @if ($message->edited_at && !$message->recalled_at)
                                    <span> · Diedit</span>
                                @endif
                            </footer>
                        </div>


                            <div
                                class="fb-chat-message-menu"
                                data-message-menu
                                hidden
                            >

                                <div
                                    class="fb-chat-menu-content"
                                    role="group"
                                    aria-label="Tindakan pesan"
                                >
                                    <button type="button" data-chat-select>Pilih pesan</button>

                                    @if (
                                        $isMine && !$message->recalled_at &&
                                        $message->message
                                    )
                                        <button
                                            type="button"
                                            class="fb-chat-edit-btn"
                                            data-update="{{ route(
                                                'user.chat.messages.update',
                                                $message
                                            ) }}"
                                            data-text="{{ $message->message }}"
                                        >
                                            ✎ Edit pesan
                                        </button>
                                    @endif

                                </div>
                            </div>

                    </article>
                @endif

            @empty
                <p class="fb-chat-empty">
                    Belum ada pesan dalam percakapan ini.
                </p>
            @endforelse
        </div>

        {{-- Bagian pengiriman pesan --}}
        <div class="fb-chat-bottom">

            @include('user.components.pages', [
                'paginator' => $messages
            ])

            @if ($canChat)

                <div class="fb-chat-action-slot">
                <form
                    class="fb-chat-composer" data-chat-compose
                    method="POST"
                    action="{{ route('user.chat.store') }}"
                    enctype="multipart/form-data"
                    id="fb-chat-form"
                >
                    @csrf

                    <input
                        type="hidden"
                        name="claim_id"
                        value="{{ $selectedClaim->id }}"
                    >

                   

                    <button type="button" class="fb-chat-attach fb-chat-attach-plus" aria-label="Buka menu lampiran" aria-haspopup="dialog"
                            data-chat-pick>+</button>

                    <input
                        type="file"
                        name="attachment"
                        id="fb-chat-file"
                        accept="image/jpeg,image/png,image/webp,video/mp4,video/webm"
                        hidden
                    >

                    <label
                        class="fb-chat-visually-hidden"
                        for="fb-chat-textarea"
                    >
                        Tulis pesan
                    </label>

                    <textarea
                        id="fb-chat-textarea"
                        name="message"
                        rows="1"
                        maxlength="3000"
                        placeholder="Tulis pesan untuk Admin..."
                    >{{ old('message') }}</textarea>

                   
                    @include('shared.chat-send-button', ['chatSendId' => 'fb-chat-send', 'chatSendClass' => 'fb-chat-primary'])


                </form>
                </div>

<p
    class="fb-chat-ajax-error"
    id="fb-chat-ajax-error"
    role="alert"
    hidden
></p>

            @else
                <p class="fb-chat-archive-warning">
                    Percakapan arsip hanya dapat dibaca.
                </p>
            @endif

        </div>
    </section>
</div>

{{-- Form untuk mengedit pesan --}}
<form
    id="fb-chat-edit-form"
    method="POST"
    hidden
>
    @csrf
    @method('PATCH')

    <textarea
        name="message"
        id="fb-chat-edit-value"
    ></textarea>
</form>

{{-- Dialog Edit Pesan --}}
<div
    id="fb-edit-overlay"
    class="fb-dialog-overlay"
    hidden
>
    <div
        class="fb-dialog-card fb-dialog-card-edit"
        role="dialog"
        aria-modal="true"
        aria-labelledby="fb-edit-title"
    >
        <div class="fb-dialog-head">
            <h3 id="fb-edit-title">
                Edit pesan
            </h3>

            <button
                type="button"
                id="fb-edit-close"
                class="fb-dialog-close"
                aria-label="Tutup"
            >
                &times;
            </button>
        </div>

        <p class="fb-dialog-text">
            Ubah isi pesan kamu lalu simpan.
        </p>

        <textarea
            id="fb-edit-textarea"
            class="fb-dialog-textarea"
            maxlength="3000"
            rows="5"
            placeholder="Tulis isi pesan..."
        ></textarea>

        <small
            id="fb-edit-help"
            class="fb-dialog-help"
        >
            Maksimal 3000 karakter.
        </small>

        <div class="fb-dialog-actions">
            <button
                type="button"
                id="fb-edit-cancel"
                class="fb-dialog-btn fb-dialog-btn-light"
            >
                Batal
            </button>

            <button
                type="button"
                id="fb-edit-save"
                class="fb-dialog-btn fb-dialog-btn-primary"
            >
                Simpan perubahan
            </button>
        </div>
    </div>
</div>


@endif

<script src="{{ asset('js/shared/chat-tools.js') }}?v=17" defer></script>
<script
    src="{{ asset('js/user/chat-modal.js') }}?v=29"
    defer>
</script>
@endsection
