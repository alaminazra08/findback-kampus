@php($photoUrl = $reportPhotoUrl ?? asset('storage/' . ltrim($item->photo_path, '/')))
<a class="fb-v-main-photo" href="{{ $photoUrl }}"
    data-fb-proof-open="fb-proof-dialog" data-fb-proof-title="Foto Barang"
    aria-haspopup="dialog" aria-controls="fb-proof-dialog">
    <img src="{{ $photoUrl }}" alt="Foto {{ $item->title }}">
    <span>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" aria-hidden="true">
            <circle cx="10" cy="10" r="6"/><path d="m15 15 5 5"/>
        </svg>
        Perbesar Foto
    </span>
</a>
