<button type="submit" class="fb-chat-send-button {{ $chatSendClass ?? '' }}"
    @isset($chatSendId) id="{{ $chatSendId }}" @endisset
    aria-label="Kirim pesan" title="Kirim pesan">
    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
        stroke-linecap="round" stroke-linejoin="round">
        <path d="m22 2-7 20-4-9-9-4Z"/>
        <path d="M22 2 11 13"/>
    </svg>
</button>
