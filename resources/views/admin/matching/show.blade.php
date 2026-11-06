@extends('admin.layouts.items')

@section('title', 'Detail Pencocokan')
@section('breadcrumb', 'Pencocokan Barang')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/admin/admin-matching.css') }}?v=35">
    <div class="match-wrap">
        <a class="match-back" href="{{ route('admin.matching.index') }}">← Kembali ke Pencocokan</a>
        <div class="match-heading">
            <div>
                <p class="match-kicker">DETAIL KANDIDAT · {{ $match->id }}</p>
                <h1>Detail <span>Pencocokan</span></h1>
                <p>Bandingkan dua laporan sebelum memutuskan kandidat cocok atau tidak.</p>
            </div>
            <div class="match-score-large"><small>Skor kemiripan
                    sistem</small><strong>{{ number_format((float) $match->match_score, 0) }}%</strong>
                <div class="match-score-track" role="meter" aria-label="Skor kemiripan" aria-valuemin="0"
                    aria-valuemax="100" aria-valuenow="{{ max(0, min(100, (int) round($match->match_score))) }}"><span
                        class="fb-refactor fb-percent-{{ max(0, min(100, (int) round($match->match_score))) }}"></span>
                </div><span
                    class="match-pill {{ $match->status }}">{{ \App\Support\WorkflowStatus::matches()[$match->status] ?? $match->status }}</span>
            </div>
        </div>
        @if (session('success'))
            <div class="match-flash success" role="status">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="match-flash error" role="alert">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="match-flash error" role="alert">{{ $errors->first() }}</div>
        @endif

        <div class="match-section-heading">
            <div>
                <h2>Perbandingan Laporan</h2>
                <p>Periksa informasi kedua laporan sebelum mengambil keputusan.</p>
            </div>
        </div>
        <div class="match-compare">
            @foreach ([['item' => $match->lostItem, 'label' => 'LAPORAN BARANG HILANG'], ['item' => $match->foundItem, 'label' => 'LAPORAN BARANG DITEMUKAN']] as $data)
                @php $item = $data['item']; @endphp
                <section
                    class="match-panel match-item-panel {{ $loop->first ? 'match-lost-panel' : 'match-found-panel' }}">
                    <p class="match-kicker">{{ $data['label'] }}</p>
                    <div class="match-item-title">
                        <h2>{{ $item?->title ?? 'Laporan tidak tersedia' }}</h2>
                    </div>
                    <dl class="match-details">
                        <div>
                            <dt>Nomor Laporan</dt>
                            <dd>{{ $item?->id ?? '—' }}</dd>
                        </div>

                        <div>
                            <dt>Lokasi</dt>
                            <dd>{{ $item?->location ?? '—' }}</dd>
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
                            <dt>Pelapor</dt>
                            <dd>{{ $item?->reporter?->name ?? '—' }}</dd>
                        </div>
                        <div class="full">
                            <dt>Deskripsi</dt>
                            <dd class="match-description">{{ $item?->description ?: 'Tidak ada deskripsi.' }}</dd>
                        </div>
                    </dl>
                    @if ($item && $item->photo_path)
                        <a class="match-image-link" href="{{ asset('storage/' . $item->photo_path) }}" target="_blank"
                            rel="noopener noreferrer"><svg viewBox="0 0 24 24" aria-hidden="true" fill="none"
                                stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="18" height="18" rx="3" />
                                <circle cx="8.5" cy="8.5" r="1.5" />
                                <path d="m21 15-5-5L5 21" />
                            </svg><span>Lihat Foto Laporan</span></a>
                    @endif
                </section>
            @endforeach
        </div>

        <section class="match-panel match-review">
            <div class="match-review-heading">
                <div>
                    <h2>Hasil Perbandingan</h2>
                    <p>Ringkasan alasan kandidat dipasangkan oleh sistem.</p>
                </div>
            </div>
            <div class="match-reason-box"><span class="match-reason-label">DASAR PERHITUNGAN KEMIRIPAN</span>
                @php $reasonParts = array_values(array_filter(array_map('trim', explode(';', (string) $match->match_reason)))); @endphp
                @if (count($reasonParts) > 1)
                    <ul class="match-reason-list">
                        @foreach ($reasonParts as $part)
                            <li><span class="match-reason-dot" aria-hidden="true"></span><span>{{ $part }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p>{{ $match->match_reason ?: 'Tidak ada alasan pencocokan.' }}</p>
                @endif
            </div>
            <p class="match-notice" role="note"><svg viewBox="0 0 24 24" aria-hidden="true" fill="none"
                    stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                    <circle cx="12" cy="12" r="9" />
                    <path d="M12 11v5M12 8h.01" />
                </svg><span>Kemiripan bukan bukti kepemilikan. Konfirmasi hanya menandai pasangan sebagai kandidat yang
                    dinilai cocok; tidak mengubah status barang dan tidak menyelesaikan proses klaim.</span></p>
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
                        <button type="submit" class="match-button">✓ Konfirmasi Pencocokan</button>
                    </form>
                    <form method="POST" action="{{ route('admin.matching.review', $match) }}"
                        data-confirm="Tolak kandidat pencocokan ini?" data-confirm-event="submit">
                        @csrf <input type="hidden" name="action" value="reject">
                        <label for="fb49-reject-reason" class="match-rejection-label">Alasan penolakan kandidat <span
                                aria-hidden="true">*</span></label>
                        <textarea id="fb49-reject-reason" name="rejection_reason" required minlength="5" maxlength="1000" rows="3"
                            class="match-rejection-textarea" placeholder="Jelaskan mengapa pasangan ini tidak cocok">{{ old('rejection_reason') }}</textarea>
                        @error('rejection_reason')
                            <p role="alert" class="fb-refactor fb-validation-error">{{ $message }}</p>
                        @enderror

                        <button type="submit" class="match-button danger">✕ Tolak Kandidat</button>
                    </form>
                </div>
            @endif
        </section>
    </div>
@endsection
