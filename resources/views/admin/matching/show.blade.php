@extends('admin.layouts.items')

@section('title', 'Detail Pencocokan')
@section('breadcrumb', 'Pencocokan Barang')

@section('page-styles')
    <link rel="stylesheet" href="{{ asset('css/admin/admin-matching.css') }}?v=46">
    <link rel="stylesheet" href="{{ asset('css/pages/admin/detail-back.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('css/admin/claim-proof-modal.css') }}?v=16">
@endsection

@section('content')
    <script defer src="{{ asset('js/pages/admin/matching/photo-viewer-v6.js') }}?v=7"></script>
<div class="match-wrap">
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
                    <h2>Perbandingan Laporan</h2>
                    <p>Periksa informasi kedua laporan sebelum mengambil keputusan.</p>
                </div>
            </div>
        </header>
        @if (session('success'))
            <div class="match-flash success" role="status">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="match-flash error" role="alert">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="match-flash error" role="alert">{{ $errors->first() }}</div>
        @endif

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
                            <dd>{{ $item?->event_date?->format('d/m/Y') ?? '—' }}</dd>
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

        <section class="match-panel match-review">
            <div class="match-review-heading">
                <div>
                    <h2>Hasil Perbandingan</h2>
                    <p>{{ $match->status !== 'pending'
                        ? 'Skor dan faktor yang tersimpan untuk keputusan admin.'
                        : ($match->lostItem && $match->foundItem
                            ? 'Dihitung dari data kedua laporan saat halaman dibuka.'
                            : 'Salah satu laporan tidak tersedia. Berikut hasil hitung yang tersimpan.') }}</p>
                </div>
            </div>
            @php
                // Alasan lama tetap dapat dibaca tanpa menampilkan istilah versi atau kategori.
                $scoringReason = explode('; Alasan penolakan admin:', (string) $match->match_reason, 2)[0];
                $reasonParts = array_values(array_filter(
                    array_map('trim', explode(';', $scoringReason)),
                    fn ($part) => $part !== ''
                        && !str_contains(mb_strtolower($part), 'metode v2')
                        && !str_contains(mb_strtolower($part), 'kategori')
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
            <p class="match-notice" role="note"><svg viewBox="0 0 24 24" aria-hidden="true" fill="none"
                    stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                    <circle cx="12" cy="12" r="9" />
                    <path d="M12 11v5M12 8h.01" />
                </svg><span>Kemiripan bukan bukti kepemilikan. Konfirmasi hanya menandai pasangan sebagai kandidat yang
                    dinilai cocok; tidak mengubah status barang dan tidak menyelesaikan proses klaim.</span></p>
            @if ($match->status === 'rejected' && str_contains((string) $match->match_reason, '; Alasan penolakan admin:'))
                <p class="match-meta">Alasan penolakan admin: {{ trim(explode('; Alasan penolakan admin:', $match->match_reason, 2)[1]) }}</p>
            @endif
            @if ($match->reviewed_at)
                <p class="match-meta"><svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor"
                        stroke-width="1.8" stroke-linecap="round">
                        <circle cx="12" cy="12" r="9" />
                        <path d="M12 7v5l3 2" />
                    </svg><span>Diperiksa oleh {{ $match->reviewer?->name ?? 'Admin' }} pada
                        {{ $match->reviewed_at->format('d/m/Y H:i') }}</span></p>
            @endif
            @if ($match->status === 'pending')
                <div class="match-review-actions">
                    <form method="POST" action="{{ route('admin.matching.review', $match) }}"
                        data-confirm="Konfirmasi pasangan pencocokan ini?" data-confirm-event="submit">
                        @csrf <input type="hidden" name="action" value="confirm">
                        <button type="submit" class="match-button">Konfirmasi Pencocokan</button>
                    </form>
                    <details class="match-rejection-details" @if ($errors->has('rejection_reason') || old('action') === 'reject') open @endif>
                        <summary class="match-button danger">Tolak Kandidat</summary>
                    <form method="POST" action="{{ route('admin.matching.review', $match) }}"
                        data-confirm="Tolak kandidat pencocokan ini?" data-confirm-event="submit">
                        @csrf <input type="hidden" name="action" value="reject">
                        <label for="fb49-reject-reason" class="match-rejection-label">Alasan penolakan kandidat <span
                                class="match-required-mark" aria-hidden="true">*</span></label>
                        <textarea id="fb49-reject-reason" name="rejection_reason" required minlength="5" maxlength="1000" rows="3"
                            class="match-rejection-textarea" placeholder="Jelaskan mengapa pasangan ini tidak cocok">{{ old('rejection_reason') }}</textarea>
                        @error('rejection_reason')
                            <p role="alert" class="fb-refactor fb-validation-error">{{ $message }}</p>
                        @enderror

                        <button type="submit" class="match-button danger">Kirim Penolakan</button>
                    </form>
                    </details>
                </div>
            @endif
        </section>
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
