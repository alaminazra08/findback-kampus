@extends('admin.layouts.items')

@section('title', 'Detail Pencocokan')
@section('breadcrumb', 'Pencocokan Barang')

@section('page-styles')
    <link rel="stylesheet" href="{{ asset('css/admin/verification-toast-v1.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/admin/verification-actions-history.css') }}?v=4">
    <link rel="stylesheet" href="{{ asset('css/admin/admin-matching.css') }}?v=47">
    <link rel="stylesheet" href="{{ asset('css/pages/admin/detail-back.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('css/admin/claim-proof-modal.css') }}?v=16">
    <link rel="stylesheet" href="{{ asset('css/admin/matching-detail-layout.css') }}?v=7">
@endsection

@section('content')
<div class="match-wrap match-detail-page">
        <div class="fb-match-return-bar">
            <a class="fb-detail-return" data-fb-detail-back href="{{ route('admin.matching.index') }}"><span aria-hidden="true">←</span> Kembali ke daftar</a>
        </div>
        <header class="match-detail-hero" aria-labelledby="match-detail-title">
            <div class="match-detail-hero-main">
                <p class="match-kicker">ADMINISTRASI · FINDBACK KAMPUS</p>
                <h1 id="match-detail-title">Detail <span>Pencocokan Barang</span></h1>
            </div>
            <div class="match-detail-score-group">
                <span class="match-detail-id">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" aria-hidden="true">
                        <path d="m12 3-9 5 9 5 9-5-9-5ZM3 8v8l9 5 9-5V8M12 13v8" />
                    </svg>
                    Kandidat {{ $match->id }}
                </span>
                <div class="match-detail-hero-score">
                    <div class="match-detail-score-value">
                        <span>Skor kemiripan</span>
                        <strong>{{ number_format((float) $match->match_score, 0) }}%</strong>
                    </div>
                    <div class="match-score-track" role="meter" aria-label="Skor kemiripan" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ max(0, min(100, (int) round($match->match_score))) }}">
                        <span class="fb-refactor fb-percent-{{ max(0, min(100, (int) round($match->match_score))) }}"></span>
                    </div>
                    <p>Kepemilikan tetap perlu diverifikasi.</p>
                </div>
                <span class="match-pill match-detail-status {{ $match->status }}">
                    @if ($match->status === 'pending')
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="9" />
                            <path d="M12 7v5l3 2" />
                        </svg>
                    @endif
                    {{ \App\Support\WorkflowStatus::matches()[$match->status] ?? $match->status }}
                </span>
            </div>
            <div class="match-section-heading">
                <div>
                    <h2 id="match-comparison-title">Perbandingan Laporan</h2>
                    <p>Bandingkan informasi laporan kehilangan dengan laporan penemuan.</p>
                </div>
            </div>
        </header>
        @include('admin.partials.decision-toast', ['decisionSuccessTitle' => 'Pencocokan dikonfirmasi', 'decisionError' => session('error')])
        @if ($errors->any())
            <div class="match-flash error" role="alert">{{ $errors->first() }}</div>
        @endif

        <section class="match-comparison" aria-labelledby="match-comparison-title">
            <div class="match-compare">
                @foreach ([['item' => $match->lostItem, 'label' => 'LAPORAN BARANG HILANG'], ['item' => $match->foundItem, 'label' => 'LAPORAN BARANG DITEMUKAN']] as $data)
                    @php $item = $data['item']; @endphp
                    <section
                        class="match-panel match-item-panel {{ $loop->first ? 'match-lost-panel' : 'match-found-panel' }}">
                        <p class="match-kicker">{{ $data['label'] }}</p>
                        <div class="match-item-title">
                            <h2>{{ $item ? 'Nama Barang: ' . ($item->title ?: '—') : 'Laporan tidak tersedia' }}</h2>
                        </div>
                        <dl class="match-details">
                            <div>
                                <dt>Nomor Laporan</dt>
                                <dd>{{ $item?->id ?? '—' }}</dd>
                            </div>

                            <div>
                                <dt>Pelapor</dt>
                                <dd>{{ $item?->reporter?->name ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt>Tanggal Kejadian</dt>
                                <dd>{{ $item?->event_date?->locale('id')->translatedFormat('j M Y') ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt>Status</dt>
                                <dd>{{ \App\Support\WorkflowStatus::report($item?->status) }}</dd>
                            </div>
                            <div>
                                <dt>Lokasi</dt>
                                <dd>{{ $item?->location ?? '—' }}</dd>
                            </div>
                            <div class="match-photo-field">
                                <dt>Lampiran</dt>
                                <dd>
                                    @if ($item?->photo_path)
                                    <a class="match-image-link" href="{{ asset('storage/' . $item->photo_path) }}" aria-haspopup="dialog"
                                        data-fb-match-photo data-photo-label="{{ $data['label'] }}"><svg viewBox="0 0 24 24" aria-hidden="true" fill="none"
                                            stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="3" width="18" height="18" rx="3" />
                                            <circle cx="8.5" cy="8.5" r="1.5" />
                                            <path d="m21 15-5-5L5 21" />
                                        </svg><span>Lihat Foto Laporan</span></a>
                                    @else
                                        <span class="match-photo-empty">Tidak ada foto</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="full">
                                <dt>Deskripsi</dt>
                                <dd class="match-description">{{ $item?->description ?: 'Tidak ada deskripsi.' }}</dd>
                            </div>
                        </dl>

                    </section>
                @endforeach
            </div>

        </section>

        <div @class(['match-outcome', 'match-outcome--paired' => $match->status === 'confirmed'])>
            @if ($match->status === 'confirmed')
                <section class="match-panel match-review match-follow-up" aria-labelledby="match-follow-up">
                    <div class="match-review-heading">
                        <div>
                            <h2 id="match-follow-up">Keputusan Admin</h2>
                            <p class="match-decision-summary">Laporan kehilangan <strong>{{ $match->lost_item_id }}</strong> dan laporan penemuan <strong>{{ $match->found_item_id }}</strong> dikonfirmasi cocok
                                @if ($match->reviewer) oleh {{ $match->reviewer->name }} @endif
                                @if ($match->reviewed_at) pada {{ $match->reviewed_at->copy()->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j M Y, H:i') }} WIB. @endif
                            </p>
                        </div>
                    </div>
                    <div class="match-follow-up-body">
                        <div class="match-follow-up-copy">
                            @if ($relatedClaim)
                                <p><strong>{{ \App\Support\WorkflowStatus::claims()[$relatedClaim->status] ?? 'Status klaim tidak tersedia' }}</strong></p>
                                <p>{{ match ($relatedClaim->status) {
                                    'pending' => 'Periksa bukti kepemilikan pada detail klaim.',
                                    'approved' => 'Lanjutkan ke pencatatan serah terima.',
                                    'returned' => 'Serah terima barang sudah dicatat.',
                                    'rejected' => 'Buka detail klaim untuk melihat alasan penolakan.',
                                    default => 'Buka detail klaim untuk melihat tindak lanjut.',
                                } }}</p>
                            @elseif ($match->lostItem?->reporter?->role === 'user'
                                && $match->lostItem->user_id !== $match->foundItem?->user_id)
                                <p><strong>Menunggu pengajuan klaim</strong></p>
                                <p>Pelapor kehilangan dapat mengajukan klaim melalui detail barang temuan. Klaim akan muncul di sini setelah diajukan.</p>
                            @else
                                <p><strong>Belum ada klaim terkait</strong></p>
                                <p>Tinjau identitas pelapor sebelum mengarahkan pemilik barang ke pengajuan klaim.</p>
                            @endif
                        </div>
                        @if ($relatedClaim)
                            <a class="match-claim-link" href="{{ route('admin.claims.show', $relatedClaim) }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Zm0 0v6h6M8 13h8m-8 4h6" />
                                </svg>
                                <span>Lihat Klaim</span>
                            </a>
                        @endif
                    </div>
                </section>
            @endif

            <section class="match-panel match-review" aria-labelledby="match-results-title">
                <div class="match-review-heading">
                    <div>
                        <h2 id="match-results-title">Hasil Perbandingan</h2>
                        <p>{{ $match->status !== 'pending'
                            ? 'Skor dan faktor yang tersimpan untuk keputusan admin.'
                            : ($match->lostItem && $match->foundItem
                                ? 'Dihitung dari data kedua laporan saat halaman dibuka.'
                                : 'Salah satu laporan tidak tersedia. Berikut hasil hitung yang tersimpan.') }}</p>
                    </div>
                </div>
                @php
                    // Skor historis disimpan, sedangkan tanggal tidak lagi ditampilkan sebagai faktor.
                    $scoringReason = explode('; Alasan penolakan admin:', (string) $match->match_reason, 2)[0];
                    $hasHistoricalDateFactor = preg_match('/(?:^|;)\s*Tanggal\s/iu', $scoringReason) === 1;
                    $reasonParts = array_values(array_filter(
                        array_map('trim', explode(';', $scoringReason)),
                        fn ($part) => $part !== ''
                            && !str_contains(mb_strtolower($part), 'metode v2')
                            && !str_contains(mb_strtolower($part), 'kategori')
                            && !str_starts_with(mb_strtolower($part), 'tanggal ')
                    ));
                @endphp
                <div class="match-reason-box">
                    @if (count($reasonParts))
                        <ul class="match-reason-list">
                            @foreach ($reasonParts as $part)
                                @php
                                    $hasPoints = preg_match('/^(.+?)\s*\(([+-]?\d+)\)$/u', $part, $factor) === 1;
                                @endphp
                                <li>
                                    <span>{{ $hasPoints ? ($factor[1] === 'Nama barang mirip' ? 'Kemiripan nama barang' : $factor[1]) : $part }}</span>
                                    @if ($hasPoints)
                                        <span @class(['match-factor-points', 'is-neutral' => (int) $factor[2] <= 0])>{{ $factor[2] }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p>Rincian faktor pencocokan belum tersedia untuk kandidat ini.</p>
                    @endif
                </div>
                @if ($hasHistoricalDateFactor)
                    <p>Skor tersimpan menggunakan bobot saat keputusan dibuat.</p>
                @endif
                <p class="match-notice" role="note"><svg viewBox="0 0 24 24" aria-hidden="true" fill="none"
                        stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                        <circle cx="12" cy="12" r="9" />
                        <path d="M12 11v5M12 8h.01" />
                    </svg><span>Kemiripan bukan bukti kepemilikan. Konfirmasi hanya menandai pasangan sebagai kandidat yang
                        dinilai cocok; tidak mengubah status barang dan tidak menyelesaikan proses klaim.</span></p>
                @if ($match->status === 'pending')
                    <div class="fb-match-review">
                        <div class="fb-review-actions">
                            @foreach (['confirm' => 'Konfirmasi Pencocokan', 'reject' => 'Tolak Kandidat'] as $action => $label)
                                @php($tone = $action === 'confirm' ? 'verified' : 'rejected')
                                <button type="button" class="fb-review-button" data-review-tone="{{ $tone }}"
                                    data-review-open="fb-match-review-{{ $action }}" aria-haspopup="dialog"
                                    aria-controls="fb-match-review-{{ $action }}">
                                    @include('admin.reports.verification.event-icon', ['kind' => $tone])
                                    {{ $label }}
                                </button>
                                <dialog id="fb-match-review-{{ $action }}" class="fb-review-dialog"
                                    aria-labelledby="fb-match-review-title-{{ $action }}"
                                    @if (old('action') === $action || ($action === 'reject' && $errors->has('rejection_reason'))) data-review-reopen @endif>
                                    <form method="POST" data-fb-async-decision action="{{ route('admin.matching.review', $match) }}">
                                        @csrf
                                        <input type="hidden" name="action" value="{{ $action }}">
                                        <h2 id="fb-match-review-title-{{ $action }}">{{ $action === 'confirm' ? 'Konfirmasi pencocokan?' : 'Tolak kandidat?' }}</h2>
                                        @if ($action === 'reject')
                                            <p id="fb-match-reject-hint">Jelaskan mengapa pasangan ini tidak cocok (minimal 5 karakter).</p>
                                            <label for="fb-match-reject-reason">Alasan penolakan kandidat <span class="fb-review-required" aria-hidden="true">*</span></label>
                                            <textarea id="fb-match-reject-reason" name="rejection_reason" required minlength="5"
                                                maxlength="1000" rows="4"
                                                aria-describedby="fb-match-reject-hint @error('rejection_reason') fb-match-reject-error @enderror"
                                                @error('rejection_reason') aria-invalid="true" @enderror>{{ old('rejection_reason') }}</textarea>
                                            @error('rejection_reason')
                                                <p id="fb-match-reject-error" role="alert">{{ $message }}</p>
                                            @enderror
                                        @else
                                            <p>Pasangan laporan ini akan ditandai sebagai kandidat yang dinilai cocok.</p>
                                        @endif
                                        <div class="fb-review-actions">
                                            <button type="button" class="fb-review-button" data-review-close autofocus>Batal</button>
                                            <button type="submit" class="fb-review-button" data-review-tone="{{ $tone }}">
                                                @include('admin.reports.verification.event-icon', ['kind' => $tone])
                                                {{ $label }}
                                            </button>
                                        </div>
                                    </form>
                                </dialog>
                            @endforeach
                        </div>
                    </div>
                @endif
            </section>
        </div>
    </div>
    <dialog id="fb-match-photo-dialog" class="fb-proof-dialog" aria-labelledby="fb-match-photo-title">
        <div class="fb-proof-shell">
            <header class="fb-proof-head">
                <h2 id="fb-match-photo-title">Foto Laporan</h2>
                <div class="fb-proof-actions">
                    <button type="button" class="fb-proof-close" data-fb-photo-zoom aria-label="Perbesar foto"
                        title="Perbesar foto" aria-pressed="false" disabled>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="10" cy="10" r="6"/><path d="m15 15 5 5M7 10h6m-3-3v6"/>
                        </svg>
                    </button>
                    <button type="button" class="fb-proof-close" data-fb-photo-close aria-label="Tutup foto" autofocus>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
            </header>
            <div class="fb-proof-content">
                <img class="fb-proof-image" data-fb-photo-image alt="Foto barang yang dilaporkan" hidden>
                <p data-fb-photo-error role="status" hidden>Foto tidak dapat ditampilkan.</p>
            </div>
        </div>
    </dialog>
@endsection
