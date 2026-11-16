
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

        {{-- Header modal --}}
        <header class="fb-chat-modal-head">

            <div>
                <span class="fb-chat-eyebrow">
                    KOMUNIKASI SERAH TERIMA
                </span>

                <h2 id="fb-modal-title">
                    {{ $selectedClaim
                        ? 'Percakapan Klaim K'.$selectedClaim->id
                        : 'Arsip Chat Lama' }}
                </h2>

                <p>
                    Koordinasikan pengambilan dan penyerahan
                    barang dengan Admin FindBack.
                </p>
            </div>

            <div class="fb-chat-head-actions">

                <span class="fb-chat-status">
                    {{ $canChat ? 'Chat Aktif' : 'Arsip' }}
                </span>

                <a href="{{ route('user.chat.index') }}"
                   class="fb-chat-close"
                   aria-label="Tutup percakapan">
                    &times;
                </a>

            </div>
        </header>

        {{-- Informasi penerima pesan --}}
        <div class="fb-chat-person">

            <span class="fb-chat-avatar">A</span>

            <span>
                <strong>Admin FindBack</strong>
                <small>
                    Tim layanan Fakultas Sains dan Teknologi
                </small>
            </span>

            @if ($selectedClaim)
                <span class="fb-chat-claim">
                    Klaim K{{ $selectedClaim->id }} ·
                    {{ $selectedClaim->item?->title ?? 'Barang' }}
                </span>
            @endif
        </div>

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
            

            data-poll="{{ $messages->currentPage() === 1
                && $selectedClaim
                ? route('user.chat.messages', [
                    'claim_id' => $selectedClaim->id
                  ])
                : '' }}"

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
                        data-id="{{ $message->id }}"
                    >

                        <div class="fb-chat-bubble">

                            <strong>
                                {{ $isMine
                                    ? 'Kamu'
                                    : 'Admin FindBack' }}
                            </strong>

                            @if ($message->recalled_at)
                                <p class="fb-chat-recalled">
                                    Pesan telah ditarik
                                </p>
                            @else

                                @if ($message->message)
                                    <p class="fb-chat-text">
                                        {{ $message->message }}
                                    </p>
                                @endif

                                @if ($message->attachment_path)
                                    <a
                                        href="{{ asset(
                                            'storage/'.$message->attachment_path
                                        ) }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        <img
                                            src="{{ asset(
                                                'storage/'.$message->attachment_path
                                            ) }}"
                                            alt="Lampiran gambar"
                                            loading="lazy"
                                        >
                                    </a>
                                @endif

                            @endif

                            <footer>
                                <time datetime="{{ $message->created_at->toIso8601String() }}">
                                    {{ $message->created_at
                                        ->timezone('Asia/Jakarta')
                                        ->format('d M Y · H:i') }} WIB
                                </time>

                               @if ($message->edited_at && !$message->recalled_at)
                                    <span> · Diedit</span>
                                @endif
                            </footer>
                        </div>

                        {{-- Menu tindakan pesan milik sendiri --}}
                        @if ($isMine)

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

                                    @if (
                                        !$message->recalled_at &&
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

                                    @if (!$message->recalled_at)

                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'user.chat.messages.recall',
                                                $message
                                            ) }}"
                                            data-confirm="Tarik pesan untuk semua pihak?"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit">
                                                ↶ Tarik pesan
                                            </button>
                                        </form>

                                    @endif

                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'user.chat.messages.hide',
                                            $message
                                        ) }}"
                                        data-confirm="Hapus pesan hanya dari tampilan kamu?"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="fb-chat-danger"
                                        >
                                            Hapus untuk saya
                                        </button>
                                    </form>

                                </div>
                            </div>

                        @endif

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

                <form
                    class="fb-chat-composer"
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

                   

<label
    for="fb-chat-file"
    class="fb-chat-attach fb-chat-attach-plus"
    title="Lampirkan gambar"
    aria-label="Lampirkan gambar"
>
    <span class="fb-chat-attach-symbol"
          aria-hidden="true">+</span>
</label>



                    <input
                        type="file"
                        name="attachment"
                        id="fb-chat-file"
                        accept="image/jpeg,image/png,image/webp"
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
                        rows="2"
                        maxlength="3000"
                        placeholder="Tulis pesan untuk Admin..."
                    >{{ old('message') }}</textarea>

                   
<button
    type="submit"
    class="fb-chat-primary"
    id="fb-chat-send"
>
    <span>Kirim</span>

    <svg
        aria-hidden="true"
        width="18"
        height="18"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
    >
        <path d="m22 2-7 20-4-9-9-4Z"/>
        <path d="M22 2 11 13"/>
    </svg>
</button>


                </form>

<p
    class="fb-chat-ajax-error"
    id="fb-chat-ajax-error"
    role="alert"
    hidden
></p>

                <small
                    class="fb-chat-file-label"
                    id="fb-chat-file-label"
                >
                    JPG, PNG, atau WebP · Maks. 4 MB ·
                    Enter kirim, Shift+Enter baris baru
                </small>

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

{{-- Dialog konfirmasi custom --}}
<div
    class="fb-dialog-overlay"
    id="fb-confirm-overlay"
    hidden
>
    <div
        class="fb-dialog-card"
        role="dialog"
        aria-modal="true"
        aria-labelledby="fb-confirm-title"
    >
        <div class="fb-dialog-head">
            <h3 id="fb-confirm-title">
                Konfirmasi tindakan
            </h3>

            <button
                type="button"
                class="fb-dialog-close"
                id="fb-confirm-close"
                aria-label="Tutup"
            >
                &times;
            </button>
        </div>

        <p
            class="fb-dialog-text"
            id="fb-confirm-message"
        >
            Apakah kamu yakin?
        </p>

        <div class="fb-dialog-actions">
            <button
                type="button"
                class="fb-dialog-btn fb-dialog-btn-light"
                id="fb-confirm-cancel"
            >
                Batal
            </button>

            <button
                type="button"
                class="fb-dialog-btn fb-dialog-btn-danger"
                id="fb-confirm-submit"
            >
                Ya, lanjutkan
            </button>
        </div>
    </div>
</div>


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

<script
    src="{{ asset('js/user/chat-modal.js') }}?v=18"
    defer>
</script>
@endsection
