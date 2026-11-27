
@extends('user.layouts.campus')

@section('title', 'Detail Laporan Saya')

@section('content')
<link rel="stylesheet" href="{{ asset('css/report-cycle.css') }}?v=12">
<script src="{{ asset('js/report-history-live.js') }}?v=1" defer></script>
<link rel="stylesheet" href="{{ asset('css/report-history-timeline.css') }}?v=1">

<div class="fb-detail-page">

    {{-- Breadcrumb --}}
    <nav class="fb-detail-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('user.reports.index') }}">Aktivitas Saya</a>
        <span>›</span>
        <a href="{{ route('user.reports.index') }}">Riwayat Laporan</a>
        <span>›</span>
        <strong>Laporan {{ \App\Support\WorkflowStatus::code((int) $report->id) }}</strong>
    </nav>

    {{-- Header halaman --}}
    <div class="fb-detail-heading">
        <div class="fb-detail-heading-icon">
            <svg viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="1.8"
                stroke-linecap="round" stroke-linejoin="round">
                <rect x="5" y="3" width="14" height="18" rx="2"/>
                <path d="M9 8h6M9 12h6M9 16h4"/>
            </svg>
        </div>

        <div>
            <h1>Detail Laporan</h1>
            <p>Informasi lengkap laporan barang yang kamu buat.</p>
        </div>
    </div>

    {{-- Informasi laporan dan riwayat --}}
    <div class="fb-detail-grid">

        {{-- Kartu informasi barang --}}
        <section class="fb-detail-card fb-detail-information">

            <div class="fb-detail-card-top">
                <span class="fb-detail-number">
                    Laporan {{ \App\Support\WorkflowStatus::code((int) $report->id) }}
                </span>

                @if ($report->status === 'pending' && $report->revisions->whereNull('closed_at')->isNotEmpty())
                    <span class="fb-detail-status amber">
                        Menunggu perbaikan Anda
                    </span>
                @else
                    @include('user.components.status', [
                        'status' => $report->status
                    ])
                @endif
            </div>

            <div class="fb-detail-item">

                {{-- Foto barang --}}
                <div class="fb-detail-photo">
                    @if ($report->photo_path)
                        <img
                            src="{{ asset('storage/' . $report->photo_path) }}"
                            alt="{{ $report->title }}"
                        >
                    @else
                        <div class="fb-detail-placeholder">
                            <span>Foto belum tersedia</span>
                        </div>
                    @endif
                </div>

                {{-- Informasi barang --}}
                <div class="fb-detail-item-content">

                    <h2>{{ $report->title }}</h2>

                    <p class="fb-detail-description">
                        {{ $report->description }}
                    </p>

                    <dl class="fb-detail-facts">

                        {{-- Jenis barang --}}
                        <div>
                            <dt>
                                <svg xmlns="http://www.w3.org/2000/svg"
     width="16"
     height="16"
     viewBox="0 0 24 24"
     fill="none"
     stroke="currentColor"
     stroke-width="1.7"
     stroke-linecap="round"
     stroke-linejoin="round">
    <path d="M20.59 13.41 11 3.83V3H4v7h.83l9.58 9.59a2 2 0 0 0 2.83 0l3.35-3.35a2 2 0 0 0 0-2.83Z"/>
    <circle cx="7.5" cy="7.5" r=".8"/>
</svg>
                                Jenis
                            </dt>

                            <dd>
                                {{ $report->type === 'lost' ? 'Barang hilang' : 'Barang ditemukan' }}
                            </dd>
                        </div>

                        {{-- Lokasi --}}
                        <div>
                            <dt>
                                <svg viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0z"/>
                                    <circle cx="12" cy="10" r="2.5"/>
                                </svg>
                                Lokasi
                            </dt>

                            <dd>{{ $report->location }}</dd>
                        </div>

                        {{-- Tanggal --}}
                        <div>
                            <dt>
                                <svg viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="5" width="18" height="16" rx="2"/>
                                    <path d="M16 3v4M8 3v4M3 10h18"/>
                                </svg>
                                Tanggal
                            </dt>

                            <dd>
                                {{ $report->event_date?->locale('id')->translatedFormat('j M Y') ?? '-' }}
                            </dd>
                        </div>

                    </dl>
                </div>
            </div>

            {{-- Alasan penolakan --}}
            @if ($report->status === 'rejected' && $report->revision_expires_at)
                @php
                    $revisionSecondsLeft = max(0, now()->diffInSeconds($report->revision_expires_at, false));
                @endphp
                <div class="fb-detail-notice">
                    <strong>Batas waktu perbaikan</strong>
                    <p>Perbaiki sebelum {{ $report->revision_expires_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j F Y, H:i') }} WIB.</p>
                    <p><strong>{{ $revisionSecondsLeft > 0 ? max(1, (int) ceil($revisionSecondsLeft / 86400)).' hari tersisa' : 'Tenggat perbaikan telah berakhir' }}</strong>. Jika melewati tenggat, laporan akan dihapus permanen.</p>
                </div>
            @endif
            @if ($report->rejection_reason)
                <div class="fb-detail-notice">
                    <strong>Alasan penolakan</strong>
                    <p>{{ $report->rejection_reason }}</p>
                </div>
            @endif

            {{-- Permintaan perbaikan --}}
            @foreach ($report->revisions->whereNull('closed_at') as $revision)
                <div class="fb-detail-notice">
                    <strong>Permintaan perbaikan dari Admin</strong>
                    <p>{{ $revision->note }}</p>
                </div>
            @endforeach

        </section>

        {{-- Riwayat verifikasi --}}
        <div class="fb-detail-history">
            @include('shared.report-history', [
                'historyTitle' => 'Lacak Laporan',
                'historyDescription' => 'Riwayat aktivitas laporan Anda.',
            ])
        </div>

    </div>

    {{-- Tombol kembali --}}
    <a class="fb-detail-back" href="{{ route('user.reports.index') }}">
        <span>←</span>
        Kembali ke Riwayat Laporan
    </a>

    {{-- Form perbaikan laporan --}}
    @php
        $openRevision = $report->status === 'pending'
            ? $report->revisions->firstWhere('closed_at', null)
            : null;

        $canFixRejected = $report->status === 'rejected' && (!$report->revision_expires_at || now()->lessThanOrEqualTo($report->revision_expires_at));
    @endphp

    @if ($openRevision || $canFixRejected)

        <section class="fb-cycle-form fb-detail-revision">

            <h2>Perbaiki dan Kirim Ulang Laporan</h2>

            <p class="fb-cycle-notice">
                {{ $canFixRejected ? $report->rejection_reason : $openRevision->note }}

                <br>

                <small>
                    {{ $canFixRejected
                        ? 'Alasan penolakan Admin'
                        : 'Diminta oleh ' . $openRevision->requester_name
                    }}
                    ·
                    {{ ($canFixRejected
                        ? $report->reviewed_at
                        : $openRevision->created_at
                    )?->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j M Y, H:i') }}
                    WIB
                </small>
            </p>

            <p>
                Jenis laporan dan pemilik tidak dapat diubah.
                Foto lama tetap dipakai bila tidak diganti.
                Pengiriman ulang belum berarti laporan disetujui.
            </p>

            <form
                method="POST"
                action="{{ route('user.reports.revision', $report) }}"
                enctype="multipart/form-data"
                id="report-revision-form"
            >

                @csrf

                <input
                    type="hidden"
                    name="revision_id"
                    value="{{ $openRevision?->id ?? 0 }}"
                >

                <div class="fb-cycle-grid">

                    <label>
                        Nama barang
                        <span>
                            <input
                                name="title"
                                required
                                maxlength="255"
                                value="{{ old('title', $report->title) }}"
                            >
                        </span>
                    </label>

                    <label>
                        Lokasi
                        <input
                            name="location"
                            required
                            maxlength="255"
                            value="{{ old('location', $report->location) }}"
                        >
                    </label>

                    <label>
                        Tanggal kejadian
                        <input
                            type="date"
                            name="event_date"
                            required
                            max="{{ now()->format('Y-m-d') }}"
                            value="{{ old('event_date', $report->event_date?->format('Y-m-d')) }}"
                        >
                    </label>

                    <label class="wide">
                        Deskripsi barang
                        <textarea
                            name="description"
                            required
                            maxlength="3000"
                            rows="5"
                        >{{ old('description', $report->description) }}</textarea>
                    </label>

                    <label class="wide">
                        Ganti foto (opsional bila sudah ada)
                        <input
                            type="file"
                            name="photo"
                            accept="image/jpeg,image/png,image/webp"
                            @required(!$report->photo_path)
                        >
                        <small>JPG, PNG, WEBP; maksimal 5 MB.</small>
                    </label>

                    <label class="wide">
                        Catatan perbaikan
                        <textarea
                            name="response_note"
                            required
                            minlength="10"
                            maxlength="2000"
                            rows="3"
                            placeholder="Jelaskan bagian yang telah diperbaiki."
                        >{{ old('response_note') }}</textarea>
                    </label>

                </div>

                <label class="check">
                    <input
                        type="checkbox"
                        name="confirmation"
                        value="1"
                        required
                        @checked(old('confirmation'))
                    >
                    Saya telah memeriksa kebenaran informasi dan foto laporan ini.
                </label>

                <button type="submit">
                    Kirim Ulang untuk Pemeriksaan
                </button>

            </form>
        </section>

        <script src="{{ asset('js/pages/user/reports/show.js') }}"></script>

    @endif

</div>
@endsection
