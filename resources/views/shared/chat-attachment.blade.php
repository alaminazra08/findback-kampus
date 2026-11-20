@php
    $url = \Illuminate\Support\Facades\Storage::disk('public')->url($path);
    $isVideo = preg_match('/\.(webm|mp4)$/i', $path) === 1;
@endphp
<a href="{{ $url }}" target="_blank" rel="noopener noreferrer" @if ($isVideo) aria-label="Putar video lampiran" @endif>
    @if ($isVideo)
        <video src="{{ $url }}" preload="metadata" playsinline muted></video>
        <span class="fb-chat-video-play" aria-hidden="true">▶</span>
    @else
        <img src="{{ $url }}" alt="{{ $label }}" loading="lazy">
    @endif
</a>
