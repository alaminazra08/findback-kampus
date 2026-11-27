<section class="fb-report-timeline {{ $historyClass ?? '' }}" aria-labelledby="fb-report-timeline-title" data-live-report-history>
    <header>
        <h2 id="fb-report-timeline-title">{{ $historyTitle ?? 'Riwayat Verifikasi Laporan' }}</h2>
        <p>{{ $historyDescription ?? 'Aktivitas laporan yang tercatat.' }}</p>
    </header>
    <ol role="list">
        @php
            // ItemJourney sudah mengurutkan aktivitas dari waktu terlama ke terbaru.
            // Halaman admin Detail Barang memakai urutan kronologis kiri-ke-kanan.
            // Halaman user tetap mempertahankan urutan tampilan sebelumnya.
            $orderedHistory = ($historyClass ?? '') === 'fb-admin-item-timeline'
                ? $history
                : $history->reverse();
        @endphp
        @forelse ($orderedHistory as $event)
            @php
                $at = $event['at']->copy()->timezone('Asia/Jakarta')->locale('id');
                $kind = $event['kind'] ?? '';
                $resubmitted = $event['title'] === 'Perbaikan dikirim ulang oleh pelapor';
                $detailLabel = match (true) {
                    $kind === 'rejected' => 'Alasan penolakan',
                    $resubmitted => 'Catatan pelapor',
                    default => null,
                };
            @endphp
            <li class="fb-report-timeline-entry" data-event="{{ $resubmitted ? 'resubmitted' : $kind }}">
                <time datetime="{{ $at->toIso8601String() }}">
                    <span>{{ $at->locale('id')->translatedFormat('j M Y') }}</span>
                    <span>{{ $at->format('H:i') }} WIB</span>
                </time>
                <span class="fb-report-timeline-marker" aria-hidden="true"></span>
                <div class="fb-report-timeline-copy">
                    <strong>{{ $kind === 'verified' ? 'Laporan diverifikasi & dipublikasikan' : $event['title'] }}</strong>
                    @if (filled($event['actor']) || !empty($event['context']))
                        <small>{{ implode(' · ', array_filter([$event['actor'], $event['context'] ?? null])) }}</small>
                    @endif
                    @if ($event['detail'])
                        <p>@if ($detailLabel)<b>{{ $detailLabel }}</b> @endif{{ $event['detail'] }}</p>
                    @endif
                </div>
            </li>
        @empty
            <li>Belum ada aktivitas yang tercatat.</li>
        @endforelse
    </ol>
    @if (!empty($historyNotice))
        <p class="fb-report-timeline-notice">{{ $historyNotice }}</p>
    @endif
</section>
