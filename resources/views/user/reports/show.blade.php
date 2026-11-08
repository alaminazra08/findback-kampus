@extends('user.layouts.campus')
@section('title', 'Detail Laporan Saya')
@section('content')
    <link rel="stylesheet" href="{{ asset('css/report-cycle.css') }}?v=1">
    <div class="cu-crumb"><a href="{{ route('user.reports.index') }}">Aktivitas saya</a> / Laporan #{{ $report->id }}</div>
    <section class="cu-panel cu-detail">
        <div class="cu-detail-photo">
            @if ($report->photo_path)
            <img src="{{ asset('storage/' . $report->photo_path) }}" alt="{{ $report->title }}">@else<div
                    class="cu-placeholder">Foto belum tersedia</div>
            @endif
        </div>
        <div>
            @if ($report->status === 'pending' && $report->revisions->whereNull('closed_at')->isNotEmpty())
                <span class="cu-badge amber">Menunggu perbaikan Anda</span>
            @else
                @include('user.components.status', ['status' => $report->status])
            @endif
            <h1>
                {{ $report->title }}</h1>
            <p class="cu-description">{{ $report->description }}</p>
            <dl class="cu-facts">
                <div>
                    <dt>Jenis</dt>
                    <dd>{{ $report->type === 'lost' ? 'Barang hilang' : 'Barang ditemukan' }}</dd>
                </div>
                <div>
                    <dt>Lokasi</dt>
                    <dd>{{ $report->location }}</dd>
                </div>
                <div>
                    <dt>Tanggal</dt>
                    <dd>{{ $report->event_date?->format('d M Y') }}</dd>
                </div>
            </dl>
            @if ($report->rejection_reason)
                <div class="cu-note">Alasan penolakan: {{ $report->rejection_reason }}</div>
            @endif
            @foreach ($report->revisions->whereNull('closed_at') as $revision)
                <div class="cu-note"><strong>Permintaan perbaikan dari admin</strong>
                    <p>{{ $revision->note }}</p>
                </div>
            @endforeach
            <a class="cu-btn light" href="{{ route('user.reports.index') }}">← Riwayat laporan</a>
        </div>
    </section>
    @php($openRevision = $report->status === 'pending' ? $report->revisions->firstWhere('closed_at', null) : null)
    @php($canFixRejected = $report->status === 'rejected')
    @if ($openRevision || $canFixRejected)
        <section class="fb-cycle-form">
            <h2>Perbaiki dan Kirim Ulang Laporan</h2>
            <p class="fb-cycle-notice">
                {{ $canFixRejected ? $report->rejection_reason : $openRevision->note }}<br><small>{{ $canFixRejected ? 'Alasan penolakan Admin' : 'Diminta oleh ' . $openRevision->requester_name }}
                    ·
                    {{ ($canFixRejected ? $report->reviewed_at : $openRevision->created_at)?->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}
                    WIB</small></p>
            <p>Jenis laporan dan pemilik tidak dapat diubah. Foto lama tetap dipakai bila tidak diganti. Pengiriman ulang
                belum berarti laporan disetujui.</p>
            <form method="POST" action="{{ route('user.reports.revision', $report) }}" enctype="multipart/form-data"
                id="report-revision-form">@csrf
                <input type="hidden" name="revision_id" value="{{ $openRevision?->id ?? 0 }}">
                <div class="fb-cycle-grid">
                    <label>Nama barang <span><input name="title" required maxlength="255"
                                value="{{ old('title', $report->title) }}"></span></label>

                    <label>Lokasi<input name="location" required maxlength="255"
                            value="{{ old('location', $report->location) }}"></label>
                    <label>Tanggal kejadian<input type="date" name="event_date" required
                            max="{{ now()->format('Y-m-d') }}"
                            value="{{ old('event_date', $report->event_date?->format('Y-m-d')) }}"></label>
                    <label class="wide">Deskripsi barang
                        <textarea name="description" required maxlength="3000" rows="5">{{ old('description', $report->description) }}</textarea>
                    </label>
                    <label class="wide">Ganti foto (opsional bila sudah ada)<input type="file" name="photo"
                            accept="image/jpeg,image/png,image/webp" @required(!$report->photo_path)><small>JPG, PNG, WEBP; maksimal 5
                            MB.</small></label>
                    <label class="wide">Catatan perbaikan
                        <textarea name="response_note" required minlength="10" maxlength="2000" rows="3"
                            placeholder="Jelaskan bagian yang telah diperbaiki.">{{ old('response_note') }}</textarea>
                    </label>
                </div><label class="check"><input type="checkbox" name="confirmation" value="1" required
                        @checked(old('confirmation'))>Saya telah memeriksa kebenaran informasi dan foto laporan ini.</label>
                <button type="submit">Kirim Ulang untuk Pemeriksaan</button>
            </form>
        </section>
        <script src="{{ asset('js/pages/user/reports/show.js') }}"></script>
    @endif
    @include('shared.report-history')
@endsection
