<svg class="fp-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
    stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('home')
            <path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z" />
        @break

        @case('search')
            <circle cx="10.5" cy="10.5" r="6.5" />
            <path d="m16 16 5 5" />
        @break

        @case('report')
            <rect x="5" y="3" width="14" height="18" rx="2" />
            <path d="M9 8h6M9 12h6M9 16h3" />
        @break

        @case('claim')
            <path d="m12 3 8 4v5c0 5-8 9-8 9s-8-4-8-9V7z" />
            <path d="m8 12 3 3 5-6" />
        @break

        @case('lost')
            <circle cx="12" cy="12" r="9" />
            <path d="M12 7v6m0 4h.01" />
        @break

        @case('found')
            <path d="m3 7 9-4 9 4v10l-9 4-9-4zM3 7l9 4 9-4M12 11v10" />
        @break

        @case('arrow')
            <path d="M5 12h14m-5-5 5 5-5 5" />
        @break

        @case('clock')
            <circle cx="12" cy="12" r="9" />
            <path d="M12 7v5l3 2" />
        @break

        @case('logout')
            <path d="M10 4H4v16h6m4-13 5 5-5 5m-5-5h10" />
        @break

        @case('menu')
            <path d="M4 6h16M4 12h16M4 18h16" />
        @break

        @case('check')
            <path d="m5 12 4 4L19 6" />
        @break

        @case('pin')
            <path d="M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 1 1 14 0Z" />
            <circle cx="12" cy="10" r="2" />
        @break
    @endswitch
</svg>
