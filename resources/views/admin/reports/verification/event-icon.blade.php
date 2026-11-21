<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
    stroke-linejoin="round" aria-hidden="true">
    @if ($kind === 'verified')
        <path d="M12 22s7-3 7-10V5l-7-3-7 3v7c0 7 7 10 7 10Z"/><path d="m9 12 2 2 4-4"/>
    @else
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6v6h6"/>
        <path d="{{ $kind === 'rejected' ? 'm9 12 6 6m0-6-6 6' : 'M8 13h8m-8 4h8' }}"/>
    @endif
</svg>
