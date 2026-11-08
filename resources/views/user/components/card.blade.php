<article class="cu-item">
    <a class="cu-item-photo" href="{{ route('user.items.show', $item) }}">
        @if ($item->photo_path)
        <img src="{{ asset('storage/' . $item->photo_path) }}" alt="{{ $item->title }}" loading="lazy">@else<span
                class="cu-placeholder">@include('user.components.icon', ['icon' => 'box'])<small>Foto belum tersedia</small></span>
        @endif
        <span
            class="cu-badge {{ $item->type === 'found' ? 'green' : 'red' }}">{{ $item->type === 'found' ? 'Ditemukan' : 'Hilang' }}</span>
    </a>
    <div class="cu-item-copy">
        <h3><a href="{{ route('user.items.show', $item) }}">{{ $item->title }}</a></h3>
        <p>@include('user.components.icon', ['icon' => 'pin']) {{ $item->location ?: 'Lokasi tidak dicantumkan' }}</p>
        <p>@include('user.components.icon', ['icon' => 'clock']) {{ $item->event_date?->format('d M Y') ?? 'Tanggal tidak tersedia' }}</p><a
            class="cu-card-link" href="{{ route('user.items.show', $item) }}">Lihat detail <span>↗</span></a>
    </div>
</article>
