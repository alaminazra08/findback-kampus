@if ($url)
    <a class="fb-claim-media-preview" href="{{ $url }}" data-fb-proof-open="{{ $dialogId }}"
        aria-haspopup="dialog">
        @if ($document)
            <span class="fb-claim-media-frame" aria-hidden="true">PDF</span>
        @else
            <img class="fb-claim-media-frame" src="{{ $url }}" alt="" loading="lazy">
        @endif
        <span class="fb-claim-media-action">{{ $action }}</span>
    </a>
@else
    <div class="fb-claim-media-preview">
        <p class="fb-claim-media-frame fb-claim-media-empty">{{ $empty }}</p>
    </div>
@endif
