@extends('user.layouts.campus')
@section('title', 'Detail Klaim')
@section('content')
    <link rel="stylesheet" href="{{ asset('css/report-history-timeline.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('css/report-cycle.css') }}?v=7">
    <script src="{{ asset('js/report-history-live.js') }}?v=1" defer></script>

    <div class="fb-claim-user-detail">
        <header class="fb-activity-heading">
            <div class="fb-activity-heading-text">
                <h1>Detail Klaim</h1>
                <p>Klaim K{{ $claim->id }} · {{ $claim->item?->title ?? 'Barang tidak tersedia' }}</p>
            </div>
        </header>

        <div class="fb-claim-user-detail-grid">
            <section class="cu-panel">
                @include('user.components.status', ['status' => $claim->status])
                <h2>Bukti Kepemilikan</h2>
                <p>{{ $claim->proof_description ?: 'Belum ada deskripsi bukti.' }}</p>

                @if ($claim->status === 'approved')
                    <a class="fb-report-view" href="{{ route('user.chat.index', ['claim_id' => $claim->id]) }}">Hubungi Admin</a>
                @endif
            </section>
            @include('shared.report-history', [
                'historyTitle' => 'Riwayat Klaim',
                'historyDescription' => 'Perkembangan pengajuan dan serah terima barang.',
            ])
        </div>
        <a class="fb-claim-user-back" href="{{ route('user.claims.index') }}">← Kembali ke Pengajuan Klaim</a>
    </div>
@endsection
