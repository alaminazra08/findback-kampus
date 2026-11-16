
<section class="fb-cycle-history" aria-label="Riwayat verifikasi laporan">

    {{-- Header riwayat --}}
    <div class="fb-history-heading">
        <div class="fb-history-icon">
            <svg viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="1.8"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="m12 3 9 5-9 5-9-5 9-5z"/>
                <path d="m3 12 9 5 9-5"/>
                <path d="m3 16 9 5 9-5"/>
            </svg>
        </div>

        <div>
            <h2>Riwayat Verifikasi Laporan</h2>
            <p>Perkembangan proses pemeriksaan laporanmu.</p>
        </div>
    </div>

    {{-- Daftar riwayat --}}
    <ol>
        @forelse($history as $event)
            <li>
                <div class="fb-history-event">

                    <div class="fb-history-event-icon">
                        <svg viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <path d="M14 2v6h6M8 13h8M8 17h8"/>
                        </svg>
                    </div>

                    <div class="fb-history-event-content">
                        <strong>{{ $event['title'] }}</strong>

                        <small>
                            {{ $event['at']->timezone('Asia/Jakarta')->format('d M Y, H:i') }}
                            WIB · {{ $event['actor'] }}
                        </small>

                        @if ($event['detail'])
                            <p>{{ $event['detail'] }}</p>
                        @endif
                    </div>

                </div>
            </li>
        @empty
            <li class="fb-history-empty">
                Belum ada peristiwa tercatat.
            </li>
        @endforelse
    </ol>

</section>
