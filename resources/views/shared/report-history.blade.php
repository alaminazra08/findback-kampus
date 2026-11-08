<section class="fb-cycle-history" aria-label="Riwayat verifikasi laporan">
    <h2>Riwayat Verifikasi Laporan</h2>
    <ol>
        @forelse($history as $event)
            <li><strong>{{ $event['title'] }}</strong><small>{{ $event['at']->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}
                    WIB · {{ $event['actor'] }}</small>
                @if ($event['detail'])
                    <p>{{ $event['detail'] }}</p>
                @endif
        </li>@empty<li>Belum ada peristiwa tercatat.</li>
        @endforelse
    </ol>
</section>
