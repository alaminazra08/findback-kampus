<svg class="cu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
    stroke-linejoin="round" aria-hidden="true">
    @switch($icon)
        @case('search')
            <circle cx="10" cy="10" r="6" />
            <path d="m15 15 6 6" />
        @break

        @case('box')
            <path d="m3 7 9-4 9 4v10l-9 4-9-4V7Zm0 0 9 4 9-4M12 11v10" />
        @break

        @case('bell')
            <path d="M5 17h14l-2-3V9a5 5 0 0 0-10 0v5l-2 3Zm5 3h4" />
        @break

        @case('pin')
            <path d="M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 1 1 14 0Z" />
            <circle cx="12" cy="10" r="2" />
        @break

        @case('clock')
            <circle cx="12" cy="12" r="9" />
            <path d="M12 6v6l4 2" />
        @break

        @case('check')
            <circle cx="12" cy="12" r="9" />
            <path d="m7 12 3 3 7-7" />
        @break

        @case('upload')
            <path d="M7 18H5a4 4 0 0 1 0-8 7 7 0 0 1 14-1 5 5 0 0 1 0 10h-2M12 21V10m-4 4 4-4 4 4" />
        @break

                @case('edit')
            <path d="m15 5 4 4M4 20l4-1 12-12a2.8 2.8 0 0 0-4-4L4 15z" />
        @break

        @case('trash')
            <path d="M3 6h18M9 6V4h6v2M6 6l1 14h10l1-14M10 10v6M14 10v6" />
        @break

        @case('lock')
            <rect x="5" y="10" width="14" height="11" rx="2" />
            <path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3" />
        @break
        @default
            <path d="M4 4h16v13H9l-5 4V4Z" />
    @endswitch
</svg>
