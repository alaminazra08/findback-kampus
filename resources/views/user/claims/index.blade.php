
@extends('user.layouts.campus')

@section('title', 'Klaim Saya')

@section('content')
<div class="ucx-page">

    {{-- HEADER HALAMAN --}}
    <section class="ucx-hero">
        <div class="ucx-hero-icon">✓</div>
        <div>
            <h1>Aktivitas Saya</h1>
            <p>Pantau laporan dan proses pengajuan klaim barang kamu.</p>
        </div>
    </section>

    {{-- TAB --}}
    <nav class="ucx-tabs">
        <a href="{{ route('user.reports.index') }}">
            <span>▤</span> Riwayat Laporan
        </a>

        <a class="active" href="{{ route('user.claims.index') }}">
            <span>◆</span> Pengajuan Klaim
        </a>
    </nav>

    {{-- NOTIFIKASI --}}
    @if (session('success'))
        <div class="ucx-alert success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="ucx-alert error">
            <strong>Periksa kembali isian:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

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
            <div>
                <span>RIWAYAT PENGAJUAN</span>
                <h2>Daftar Klaim Saya</h2>
            </div>

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
                        <div class="ucx-item-icon">▣</div>

                        {{-- INFORMASI KLAIM --}}
                        <div class="ucx-item-info">

                            <span class="ucx-number">
                                KLAIM #{{ $claim->id }}
                            </span>

                            <h3>
                                {{ $claim->item_title ?: 'Barang tidak tersedia' }}
                            </h3>

                            <div class="ucx-meta">
                                <span>⌖ {{ $claim->location ?: 'Lokasi tidak tersedia' }}</span>
                                <span>•</span>
                                <span>▦ {{ \Carbon\Carbon::parse($claim->created_at)->format('d M Y') }}</span>
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
                    <details class="ucx-details" open>

                        <summary>
                            <span>⌄</span>
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
