@if ($person && $person->role === 'user')
    <a class="fb-workflow-action" href="{{ route('admin.chat.show', $person->id) }}"
        aria-label="Chat dengan {{ $person->name }}"><svg viewBox="0 0 24 24" aria-hidden="true" fill="none"
            stroke="currentColor" stroke-width="1.8">
            <path d="M21 11a8 8 0 0 1-8 8H8l-5 3 1-6a8 8 0 1 1 17-5Z" />
        </svg>Chat</a>
@else<span class="fb-workflow-action is-disabled" aria-disabled="true" title="Akun pengguna tidak tersedia">Chat tidak
        tersedia</span>
@endif
