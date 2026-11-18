
@extends('user.layouts.campus')

@section('title', 'Klaim Saya')

@section('content')
<div class="ucx-page">

    {{-- HEADER HALAMAN --}}
        <link rel="stylesheet" href="{{ asset('css/report-cycle.css') }}?v=7">

    {{-- HEADER AKTIVITAS SAYA --}}
    <div class="fb-activity-heading"
        style="display:flex !important; flex-direction:row !important; align-items:center !important; gap:16px !important;">
        <div class="fb-activity-heading-icon" style="flex:0 0 64px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"
                aria-hidden="true">
                <path d="m5 12.5 4.5 4.5L19 7.5"/>
            </svg>
        </div>

        <div class="fb-activity-heading-text">
            <h1>Aktivitas Saya</h1>
            <p>Pantau perkembangan laporan dan klaim barangmu.</p>
        </div>
    </div>

    {{-- TAB AKTIVITAS --}}
    <nav class="fb-activity-tabs" aria-label="Navigasi aktivitas">
        <a href="{{ route('user.reports.index') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                aria-hidden="true">
                <path d="M6 3h9l4 4v14H6z"/>
                <path d="M14 3v5h5M9 12h7M9 16h7"/>
            </svg>
            <span>Riwayat Laporan</span>
        </a>

        <a class="active" href="{{ route('user.claims.index') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                aria-hidden="true">
                <path d="m20.5 13.5-7.5 7.5-10-10 7.5-7.5a2 2 0 0 1 2.8 0l7.2 7.2a2 2 0 0 1 0 2.8Z"/>
                <circle cx="8.5" cy="8.5" r="1.3"/>
            </svg>
            <span>Pengajuan Klaim</span>
        </a>
    </nav>

   
    {{-- FILTER PENCARIAN --}}
    <section class="ucx-filter-card">

        <div class="ucx-filter-heading">
            <div class="ucx-search-icon">⌕</div>
            <div>
                <h2>Cari Pengajuan Klaim</h2>
                <p>Temukan pengajuan berdasarkan nama barang dan status.</p>
            </div>
        </div>

        <form method="GET"
              action="{{ route('user.claims.index') }}"
              class="ucx-filter-form">

            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="⌕   Cari nama barang..."
                aria-label="Cari nama barang"
            >

            <select name="type" aria-label="Jenis barang">
                <option value="">Semua jenis barang</option>
                <option value="lost" @selected(request('type') === 'lost')>
                    Barang Hilang
                </option>
                <option value="found" @selected(request('type') === 'found')>
                    Barang Ditemukan
                </option>
            </select>

            <select name="status" aria-label="Status klaim">
                <option value="">Semua status</option>
                @foreach ([
                    'pending' => 'Menunggu',
                    'approved' => 'Disetujui',
                    'rejected' => 'Ditolak',
                    'returned' => 'Dikembalikan'
                ] as $key => $label)
                    <option value="{{ $key }}"
                        @selected(request('status') === $key)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>

            <button type="submit">⌕ &nbsp; Terapkan Filter</button>

            <a href="{{ route('user.claims.index') }}" class="ucx-reset">
                ↻ &nbsp; Reset
            </a>
        </form>
    </section>

    {{-- JUDUL DAFTAR --}}
    <section class="ucx-results">

        <div class="ucx-results-heading">

            <div class="ucx-total">
                ▤ &nbsp; {{ $claims->total() }} Pengajuan
            </div>
        </div>

        {{-- DAFTAR KLAIM --}}
        <div class="ucx-list">

            @forelse($claims as $claim)

                @php
                    $steps = [
                        'pending' => 1,
                        'approved' => 2,
                        'rejected' => 2,
                        'returned' => 4,
                    ];

                    $currentStep = $steps[$claim->status] ?? 1;
                @endphp

                <article class="ucx-card">

                    <div class="ucx-card-main">

                        {{-- IKON BARANG TANPA FOTO --}}
                        <div class="ucx-item-icon">
                             @if($claim->item_photo)
                                <img
                                    src="{{ asset('storage/' . $claim->item_photo) }}"
                                    alt="Foto Barang"
                                >
                            @else
                                <span>Foto tidak tersedia</span>
                             @endif
                        </div>

                        {{-- INFORMASI KLAIM --}}
                        <div class="ucx-item-info">

                            <h3>
                                {{ $claim->item_title ?: 'Barang tidak tersedia' }}
                            </h3>

                            <div class="ucx-meta">
                                <span>⌖ {{ $claim->location ?: 'Lokasi tidak tersedia' }}</span>
                                <span>•</span>
                                <span>📅 {{ \Carbon\Carbon::parse($claim->created_at)->format('d M Y') }}</span>
                            </div>

                        </div>

                        {{-- STATUS --}}
                        <div class="ucx-status">
                            @include('user.components.status', [
                                'status' => $claim->status
                            ])
                        </div>

                    </div>

                    <div class="ucx-divider"></div>

                    {{-- BUKTI DAN PERKEMBANGAN --}}
                    <details class="ucx-details">

                        <summary>
                            <span></span>
                            Lihat bukti dan perkembangan
                        </summary>

                        <div class="ucx-progress">

                            @foreach ([
                                1 => 'Pengajuan Klaim',
                                2 => 'Verifikasi Admin',
                                3 => 'Proses Klaim',
                                4 => 'Selesai'
                            ] as $step => $label)

                                <div class="ucx-step
                                    {{ $step <= $currentStep ? 'completed' : '' }}
                                    {{ $claim->status === 'rejected' && $step === 2 ? 'rejected' : '' }}">

                                    <div class="ucx-step-circle">
                                        @if ($step < $currentStep || ($claim->status === 'returned' && $step === 4))
                                            ✓
                                        @elseif ($step === $currentStep && $claim->status !== 'rejected')
                                            ✓
                                        @else
                                            {{ $step }}
                                        @endif
                                    </div>

                                    <div class="ucx-step-label">
                                        {{ $label }}

                                        @if ($step === 1)
                                            <small>
                                                {{ \Carbon\Carbon::parse($claim->created_at)->format('d M Y, H:i') }}
                                            </small>
                                        @elseif ($step === 2 && $claim->reviewed_at)
                                            <small>
                                                {{ \Carbon\Carbon::parse($claim->reviewed_at)->format('d M Y') }}
                                            </small>
                                        @elseif ($step === 4 && $claim->returned_at)
                                            <small>
                                                {{ \Carbon\Carbon::parse($claim->returned_at)->format('d M Y') }}
                                            </small>
                                        @endif
                                    </div>

                                </div>

                            @endforeach

                        </div>

                        <div class="ucx-proof">
                            <h4>Bukti Kepemilikan</h4>

                            <p>
                                {{ $claim->proof_description ?: 'Belum ada deskripsi bukti.' }}
                            </p>

                            @if ($claim->rejection_reason)
                                <div class="ucx-alert error">
                                    <strong>Alasan penolakan:</strong>
                                    {{ $claim->rejection_reason }}
                                </div>
                            @endif

                            @if ($claim->returned_at)
                                <div class="ucx-alert success">
                                    Barang dikembalikan pada
                                    {{ \Carbon\Carbon::parse($claim->returned_at)->format('d M Y') }}.
                                </div>
                            @endif
                        </div>

                    </details>

                    {{-- BUKTI TAMBAHAN DARI ADMIN --}}
                    @if ($claim->status === 'pending' &&
                        ($evidenceRequest = $openEvidenceRequests->get($claim->id)))

                        <div class="ucx-evidence">
                            <h4>Admin Meminta Bukti Tambahan</h4>

                            <p>{{ $evidenceRequest->note }}</p>

                            <form method="POST"
                                  action="{{ route('user.claims.evidence-response', $claim->id) }}"
                                  enctype="multipart/form-data">

                                @csrf

                                <input type="hidden"
                                       name="request_id"
                                       value="{{ $evidenceRequest->id }}">

                                <label for="fb2b-note-{{ $claim->id }}">
                                    Penjelasan atau ciri kepemilikan *
                                </label>

                                <textarea
                                    id="fb2b-note-{{ $claim->id }}"
                                    name="response_note"
                                    required
                                    minlength="10"
                                    maxlength="2000"
                                    rows="4"
                                    placeholder="Jelaskan bukti yang diminta Admin">{{ old('response_note') }}</textarea>

                                <label for="fb2b-photo-{{ $claim->id }}">
                                    Foto bukti baru (opsional, maksimal 4 MB)
                                </label>

                                <input
                                    id="fb2b-photo-{{ $claim->id }}"
                                    name="proof_photo"
                                    type="file"
                                    accept="image/jpeg,image/png,image/webp">

                                <label class="ucx-confirm">
                                    <input type="checkbox"
                                           name="confirm_truth"
                                           value="1"
                                           required>
                                    Saya menyatakan informasi ini benar dan milik saya.
                                </label>

                                <button type="submit">
                                    Kirim Bukti Tambahan
                                </button>

                            </form>
                        </div>
                    @endif

                    {{-- HUBUNGI ADMIN --}}
                    @if ($claim->status === 'approved')
                        <div class="ucx-contact">
                            <a href="{{ route('user.chat.index', ['claim_id' => $claim->id]) }}">
                                ☏ &nbsp; Hubungi Admin →
                            </a>
                        </div>
                    @endif

                </article>

            @empty

                <div class="ucx-empty">
                    <div>▣</div>
                    <h3>Belum Ada Pengajuan Klaim</h3>
                    <p>
                        Pengajuan klaim barang kamu akan muncul di halaman ini.
                        Silakan temukan barang yang ingin diklaim.
                    </p>
                    <a href="{{ route('user.items.index', ['type' => 'found']) }}">
                        Cari Barang Ditemukan →
                    </a>
                </div>

            @endforelse

        </div>

        {{-- PAGINATION --}}
        <div class="ucx-pagination">
            @include('user.components.pages', ['paginator' => $claims])
        </div>

    </section>

</div>
@endsection
