@extends('user.layouts.campus')
@section('title', 'Klaim Saya')
@section('content')
    <div class="cu-title">
        <h1>Aktivitas saya</h1>
        <p>Ikuti proses pemeriksaan klaim sampai barang dikembalikan.</p>
    </div>
    <nav class="cu-tabs"><a href="{{ route('user.reports.index') }}">Riwayat laporan</a><a class="active"
            href="{{ route('user.claims.index') }}">Pengajuan klaim</a></nav>
    <form class="cu-panel cu-inline-filter"><input name="search" value="{{ request('search') }}"
            placeholder="Cari barang yang diklaim" aria-label="Cari klaim"><select name="status" aria-label="Status klaim">
            <option value="">Semua status</option>
            @foreach (['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'returned' => 'Dikembalikan'] as $key => $label)
                <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="cu-btn">Terapkan</button><a href="{{ route('user.claims.index') }}">Reset</a>
    </form>
    @if (session('success'))
        <div class="cu-alert success" role="status">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="cu-note" role="alert"><strong>Periksa kembali isian:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="cu-claim-list">
        @forelse($claims as $claim)
            <article class="cu-panel">
                <header>
                    <div><small>KLAIM #{{ $claim->id }}</small>
                        <h2>{{ $claim->item_title ?: 'Barang tidak tersedia' }}</h2>
                        <p class="cu-muted">{{ $claim->location }} ·
                            {{ \Carbon\Carbon::parse($claim->created_at)->format('d M Y') }}</p>
                    </div>@include('user.components.status', ['status' => $claim->status])
                </header>
                <details>
                    <summary>Lihat bukti dan perkembangan</summary>
                    <p class="cu-description">{{ $claim->proof_description }}</p>
                    @if ($claim->rejection_reason)
                        <div class="cu-note">Alasan penolakan: {{ $claim->rejection_reason }}</div>
                    @endif
                    @if ($claim->returned_at)
                        <div class="cu-alert success">Barang dikembalikan pada
                            {{ \Carbon\Carbon::parse($claim->returned_at)->format('d M Y') }}.</div>
                    @endif
                </details>
                @if ($claim->status === 'pending' && ($evidenceRequest = $openEvidenceRequests->get($claim->id)))
                    <div class="cu-note fb-refactor fb-evidence-panel">
                        <strong>Admin meminta bukti tambahan</strong>
                        <p class="fb-refactor fb-preserve-lines">{{ $evidenceRequest->note }}</p>
                        <form method="POST" action="{{ route('user.claims.evidence-response', $claim->id) }}"
                            enctype="multipart/form-data" class="fb-refactor fb-evidence-form">
                            @csrf
                            <input type="hidden" name="request_id" value="{{ $evidenceRequest->id }}">
                            <label for="fb2b-note-{{ $claim->id }}">Penjelasan atau ciri kepemilikan <span
                                    aria-hidden="true">*</span></label>
                            <textarea id="fb2b-note-{{ $claim->id }}" name="response_note" required minlength="10" maxlength="2000"
                                rows="4" placeholder="Jelaskan bukti yang diminta Admin" class="fb-refactor fb-evidence-note">{{ old('response_note') }}</textarea>
                            <label for="fb2b-photo-{{ $claim->id }}">Foto bukti baru (opsional, maksimal 4 MB)</label>
                            <input id="fb2b-photo-{{ $claim->id }}" name="proof_photo" type="file"
                                accept="image/jpeg,image/png,image/webp">
                            <label><input type="checkbox" name="confirm_truth" value="1" required> Saya menyatakan
                                informasi ini benar dan milik saya.</label>
                            <button type="submit" class="cu-btn">Kirim bukti tambahan</button>
                        </form>
                    </div>
                @endif
                @if ($claim->status === 'approved')
                    <a class="cu-btn light" href="{{ route('user.chat.index', ['claim_id' => $claim->id]) }}">Hubungi Admin
                        untuk serah terima →</a>
                @endif
            </article>
        @empty<div class="cu-empty">
                <h2>Belum ada pengajuan klaim</h2>
                <p>Temukan barangmu dan ajukan klaim melalui halaman detail barang ditemukan.</p><a class="cu-btn"
                    href="{{ route('user.items.index', ['type' => 'found']) }}">Cari barang ditemukan</a>
            </div>
        @endforelse
    </div>@include('user.components.pages', ['paginator' => $claims])
@endsection
