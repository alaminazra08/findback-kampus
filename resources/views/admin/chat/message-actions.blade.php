<button type="button" class="fb-chat-options" data-admin-chat-options
    @if ((int) $msg->sender_id === (int) auth()->id() && $msg->recallDeadline())
        data-recall-until="{{ $msg->recallDeadline()->toIso8601String() }}"
    @endif
    aria-label="Pilih pesan">⌄</button>
