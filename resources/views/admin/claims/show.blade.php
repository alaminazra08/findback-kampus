@extends('admin.layouts.items')
@section('title', 'Detail Klaim Barang')
@section('breadcrumb', 'Klaim Barang')
@section('page-styles')
    <link rel="stylesheet" href="{{ asset('css/admin/claims-toast-v1.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/admin/admin-claims.css') }}?v=20261118-claims-v1">
    <link rel="stylesheet" href="{{ asset('css/admin/claim-detail-compact.css') }}?v=6">
    <link rel="stylesheet" href="{{ asset('css/admin/claim-detail-premium.css') }}?v=7">
    <link rel="stylesheet" href="{{ asset('css/admin/claim-detail-spacing.css') }}?v=9">
    <link rel="stylesheet" href="{{ asset('css/admin/claim-evidence-actions.css') }}?v=11">
    <link rel="stylesheet" href="{{ asset('css/admin/claim-evidence-icons.css') }}?v=12">
    <link rel="stylesheet" href="{{ asset('css/admin/claim-detail-responsive.css') }}?v=33">
    <link rel="stylesheet" href="{{ asset('css/chat/claim-chat-premium.css') }}?v=20261120-chat2">
    @include('shared.chat-tools-style')
    <link rel="stylesheet" href="{{ asset('css/pages/admin/detail-back.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/admin/claim-proof-modal.css') }}?v=17">
    <link rel="stylesheet" href="{{ asset('css/pages/admin/claims/handover-summary.css') }}?v=3">
@endsection

@section('content')
<div class="fb-page-heading fb-claim-heading fb-detail-return-heading">
        <a class="fb-detail-return" data-fb-detail-back href="{{ route('admin.claims.index') }}"><span aria-hidden="true">←</span> Kembali ke daftar</a>
        <div>
            <p class="fb-eyebrow">ADMINISTRASI · FINDBACK KAMPUS</p>
            <h1>Detail <span>Klaim K{{ $claim->id }}</span></h1>
            <p class="fb-heading-text">Pemeriksaan bukti kepemilikan dan keputusan pengembalian barang.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="fb-claim-toast" data-fb-claim-toast data-duration="5000" role="status" aria-live="polite" aria-atomic="true">
            <span class="fb-claim-toast-icon" aria-hidden="true">✓</span>
            <span class="fb-claim-toast-copy"><strong>Proses berhasil</strong><small>{{ session('success') }}</small></span>
            <button type="button" class="fb-claim-toast-close" aria-label="Tutup notifikasi" data-fb-claim-toast-close>&times;</button>
        </div>
    @endif
    @if ($errors->any())
        <div class="fb-claim-toast is-error" data-fb-claim-toast role="alert" aria-live="assertive" aria-atomic="true">
            <span class="fb-claim-toast-icon" aria-hidden="true">!</span>
            <span class="fb-claim-toast-copy"><strong>Proses belum berhasil</strong>
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </span>
            <button type="button" class="fb-claim-toast-close" aria-label="Tutup notifikasi" data-fb-claim-toast-close>&times;</button>
        </div>
    @endif

    @php
        $itemPhotoUrl = $claim->item?->photo_path ? asset('storage/' . $claim->item->photo_path) : null;
        $proofUrl = $claim->proof_photo_path ? asset('storage/' . $claim->proof_photo_path) : null;
        $isProofPdf = $proofUrl && strtolower(pathinfo($claim->proof_photo_path, PATHINFO_EXTENSION)) === 'pdf';
    @endphp

    <div class="fb-claim-detail-grid fb-claim-review-layout">
        <section class="fb-claim-panel fb-claim-comparison-panel">
            <div class="fb-claim-panel-head">
                <div>
                    <h2>Informasi Barang</h2>
                    <p>Laporan barang ditemukan yang diajukan klaim.</p>
                </div><span
                    class="fb-claim-status status-{{ $claim->status }}">{{ \App\Support\WorkflowStatus::claims()[$claim->status] ?? $claim->status }}</span>
            </div>
            @include('admin.claims.media-preview', [
                'url' => $itemPhotoUrl,
                'document' => false,
                'dialogId' => 'fb-item-photo-dialog',
                'action' => 'Perbesar Foto Barang',
                'empty' => 'Foto barang belum tersedia',
            ])
            <dl class="fb-claim-details">
                <div>
                    <dt>Nama barang</dt>
                    <dd>{{ $claim->item?->title ?? '—' }}</dd>
                </div>

                <div>
                    <dt>Lokasi ditemukan</dt>
                    <dd>{{ $claim->item?->location ?? '—' }}</dd>
                </div>
                <div>
                    <dt>Tanggal kejadian</dt>
                    <dd>{{ $claim->item?->event_date?->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j M Y') ?? '—' }}</dd>
                </div>
                <div>
                    <dt>Status barang</dt>
                    <dd>{{ \App\Support\WorkflowStatus::report($claim->item?->status) }}</dd>
                </div>
                <div>
                    <dt>Pelapor barang</dt>
                    <dd>{{ $claim->item?->reporter?->name ?? '—' }}</dd>
                </div>
            </dl>
            <div class="fb-claim-comparison-note">
                <h3>Deskripsi Barang</h3>
                <p class="fb-claim-description">{{ $claim->item?->description ?: 'Tidak ada deskripsi.' }}</p>
            </div>
        </section>

        <section class="fb-claim-panel fb-claim-comparison-panel">
            <div class="fb-claim-panel-head">
                <div>
                    <h2>Bukti Kepemilikan</h2>
                    <p>Jangan menyetujui klaim tanpa pemeriksaan memadai.</p>
                </div>
            </div>
            @include('admin.claims.media-preview', [
                'url' => $proofUrl,
                'document' => $isProofPdf,
                'dialogId' => 'fb-proof-dialog',
                'action' => $isProofPdf ? 'Lihat Dokumen Bukti' : 'Perbesar Foto Bukti',
                'empty' => 'Foto bukti belum tersedia',
            ])
            <dl class="fb-claim-details fb-claim-details-with-chat">
                <div>
                    <dt>Pengaju</dt>
                    <dd>{{ $claim->claimant?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt>Email</dt>
                    <dd>{{ $claim->claimant?->email ?? '—' }}</dd>
                </div>
                <div>
                    <dt>Diajukan</dt>
                    <dd>{{ $claim->created_at?->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j M Y, H:i') ?? '—' }} WIB</dd>
                </div>
                <div class="fb-claim-chat-row">
                    <dt>Chat Serah Terima</dt>
                    <dd>
                        @if ($claim->claimant && in_array($claim->status, ['approved', 'returned'], true))
                            <button type="button" class="fb-claim-chat-inline" data-fcp-open aria-haspopup="dialog">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M21 11.5a8.5 8.5 0 0 1-8.5 8.5 9 9 0 0 1-4.1-1L3 21l1.9-5.6a8.5 8.5 0 1 1 16.1-3.9Z" />
                                </svg>
                                <span>{{ $claim->status === 'approved' ? 'Buka Chat' : 'Arsip Chat' }}</span>
                                @if ($claim->status === 'approved')
                                    <span class="fb-claim-chat-count" data-fb-claim-chat-count="{{ $claim->id }}" hidden></span>
                                @endif
                            </button>
                        @else
                            <span class="fb-claim-chat-inline fb-claim-chat-unavailable">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect width="14" height="11" x="5" y="11" rx="2" />
                                    <path d="M8 11V7a4 4 0 0 1 8 0v4" />
                                </svg>
                                <span>{{ $claim->status === 'pending' ? 'Chat Belum Aktif' : 'Chat Tidak Tersedia' }}</span>
                            </span>
                        @endif
                    </dd>
                </div>
            </dl>
            <div class="fb-claim-comparison-note">
                <h3>Penjelasan Pengaju</h3>
                <blockquote class="fb-claim-description">{{ $claim->proof_description ?: 'Belum ada penjelasan dari pengaju.' }}</blockquote>
            </div>
        </section>

        <section @class(['fb-claim-panel fb-claim-actions-panel', 'fb-claim-handover-panel' => $claim->status === 'approved'])>
            <div class="fb-claim-panel-head">
                <div>
                    @if ($claim->status === 'approved')
                        <h2>Catat Penyerahan Barang</h2>
                        <p>Pastikan identitas penerima sesuai dan barang telah diserahkan secara langsung.</p>
                    @elseif ($claim->status === 'returned')
                        <h2>Ringkasan Serah Terima</h2>
                    @else
                        <h2>Tindakan Administrator</h2>
                        @if ($claim->status === 'pending')
                            <p>Periksa bukti kepemilikan sebelum mengambil keputusan.</p>
                        @else
                            <p>Kelola keputusan klaim dan dokumentasikan serah terima barang.</p>
                        @endif
                    @endif
                </div>
                @if ($claim->status === 'approved')
                    <span class="fb-handover-stage">Menunggu Serah Terima</span>

                @endif
            </div>

            @if ($claim->status === 'pending')
                <form id="fb-claim-approve-form" method="POST" action="{{ route('admin.claims.review', $claim) }}" class="fb-claim-form">
                    @csrf
                    <input type="hidden" name="decision" value="approved">
                    <label class="fh-confirm fb-claim-review-confirm"><input type="checkbox" name="evidence_reviewed" value="1"
                            required><span>Saya telah memeriksa identitas dan bukti kepemilikan pengaju serta
                            mencocokkannya dengan barang.</span></label>
                </form>
                <div class="fb-claim-decision-actions">
                    <button type="submit" form="fb-claim-approve-form" class="fb-claim-button is-approve" data-claim-tone="approve"
                        data-confirm="Yakin menyetujui klaim ini?" data-confirm-event="click">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s7-3 7-10V5l-7-3-7 3v7c0 7 7 10 7 10Z"/><path d="m9 12 2 2 4-4"/></svg>
                        <span>Setujui Klaim</span>
                    </button>
                    <button type="button" class="fb-claim-button is-reject" data-claim-tone="reject" data-fb-claim-reject-open
                        aria-haspopup="dialog" aria-controls="fb-claim-reject-dialog">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6M9 12l6 6m0-6-6 6"/></svg>
                        <span>Tolak Klaim</span>
                    </button>
                </div>
                <dialog id="fb-claim-reject-dialog" aria-labelledby="fb-claim-reject-title"
                    @if (old('decision') === 'rejected' || $errors->has('rejection_reason')) data-reopen @endif>
                    <form method="POST" action="{{ route('admin.claims.review', $claim) }}"
                        class="fb-modal-v14-panel fb-claim-form">
                        @csrf
                        <input type="hidden" name="decision" value="rejected">
                        <h2 id="fb-claim-reject-title" tabindex="-1" autofocus>Tolak Klaim?</h2>
                        <label for="rejection_reason">Alasan penolakan <span aria-hidden="true">*</span></label>
                        <textarea id="rejection_reason" name="rejection_reason" required maxlength="2000" rows="4"
                            @error('rejection_reason') aria-invalid="true" aria-describedby="fb-claim-reject-error" @enderror
                            placeholder="Tuliskan alasan penolakan…">{{ old('rejection_reason') }}</textarea>
                        @error('rejection_reason')
                            <small id="fb-claim-reject-error">{{ $message }}</small>
                        @enderror
                        @error('decision')
                            <small>{{ $message }}</small>
                        @enderror
                        <div class="fb-modal-v14-actions">
                            <button type="button" data-fb-claim-reject-cancel>Batal</button>
                            <button type="submit" class="fb-claim-reject-submit" data-claim-tone="reject">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6M9 12l6 6m0-6-6 6"/></svg>
                                <span>Tolak Klaim</span>
                            </button>
                        </div>
                    </form>
                </dialog>

            @elseif($claim->status === 'approved')
                <form method="POST" action="{{ route('admin.claims.return', $claim) }}"
                    enctype="multipart/form-data" class="fb-claim-form fh-form fb-claim-handover-form">
                    @csrf
                    <div class="fb-claim-handover-fields">
                        <div><label for="recipient_name">Nama lengkap penerima <span>*</span></label><input
                                id="recipient_name" name="recipient_name" required maxlength="150" autocomplete="name"
                                value="{{ old('recipient_name', $claim->claimant?->name) }}"><small>Penerima adalah
                                pengaju klaim yang telah diverifikasi. Penyerahan kepada perwakilan belum didukung.</small>
                        </div>
                        <div><label for="recipient_identifier">NIM <span class="fh-optional">opsional</span></label><input
                                id="recipient_identifier" name="recipient_identifier" maxlength="80"
                                value="{{ old('recipient_identifier') }}"
                                placeholder="Masukkan NIM mahasiswa bila tersedia"><small>Tidak perlu memasukkan NIK atau
                                mengunggah foto KTP.</small></div>
                        <div><label for="handover_location">Tempat serah terima barang <span>*</span></label><input
                                id="handover_location" name="handover_location" required maxlength="200"
                                value="{{ old('handover_location') }}"
                                placeholder="Contoh: Ruang Administrasi FST, lantai 1"><small>Isi tempat penyerahan barang
                                secara langsung kepada penerima, bukan tempat barang ditemukan.</small></div>
                    </div>
                    <div class="fb-claim-handover-fields">
                        <div><label for="handover_evidence">Foto / dokumen bukti penyerahan <span>*</span></label><input
                                type="file" id="handover_evidence" name="handover_evidence" required
                                accept="image/jpeg,image/png,application/pdf"><small>JPG, PNG, atau PDF · maksimal 5 MB.
                                Berkas hanya dapat diakses admin.</small></div>
                        <div class="fb-claim-handover-notes"><label for="handover_notes">Catatan penyerahan <span
                                    class="fh-optional">opsional</span></label>
                            <textarea id="handover_notes" name="handover_notes" rows="3" maxlength="2000"
                                placeholder="Kondisi barang atau catatan saat diserahkan...">{{ old('handover_notes') }}</textarea>
                        </div>
                    </div>
                    <label class="fh-confirm"><input type="checkbox" name="handover_confirmed" value="1"
                            required @checked(old('handover_confirmed'))><span>Saya telah mencocokkan identitas penerima dengan
                            pengaju dan memastikan barang sudah diserahkan secara fisik.</span></label>
                    <button type="submit" class="fb-claim-button is-approve"
                        data-confirm="Simpan serah terima? Data tidak dapat diedit dan barang akan ditandai dikembalikan."
                        data-confirm-event="click">Simpan Serah Terima</button>
                </form>
            @elseif ($claim->status !== 'returned')
                <p class="fb-claim-muted">Klaim ini telah diproses dan tidak dapat diubah dari halaman ini.</p>
            @endif
            @if ($claim->rejection_reason)
                <div class="fb-claim-rejection"><strong>Alasan penolakan</strong>
                    <p>{{ $claim->rejection_reason }}</p>
                </div>
            @endif
            @if ($claim->status === 'approved')
                <div class="fb-claim-handover-footer">
                    <p>Klaim lain yang masih menunggu untuk barang ini akan ditutup otomatis dengan alasan yang tercatat.</p>
                    @if ($claim->reviewed_at)
                        <p>Diperiksa oleh {{ $claim->reviewer?->name ?? 'Administrator' }} pada
                            {{ $claim->reviewed_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j M Y, H:i') }} WIB.</p>
                    @endif
                </div>
            @elseif ($claim->status !== 'returned' && $claim->reviewed_at)
                <p class="fb-claim-muted">Diperiksa oleh {{ $claim->reviewer?->name ?? 'Administrator' }} pada
                    {{ $claim->reviewed_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j M Y, H:i') }} WIB.</p>
            @endif
            @if ($claim->status === 'returned')
                @if ($claim->hasHandoverReceipt())
                    <p class="fh-summary-code">Nomor serah terima <strong>{{ $claim->handover_code }}</strong></p>
                    <dl class="fh-summary-facts">
                        <div>
                            <dt>Nama penerima</dt>
                            <dd>{{ $claim->recipient_name }}</dd>
                        </div>
                        <div>
                            <dt>Petugas penyerah</dt>
                            <dd>{{ $claim->handover_officer_name }}</dd>
                        </div>
                        <div>
                            <dt>Tempat serah terima barang</dt>
                            <dd>{{ $claim->handover_location }}</dd>
                        </div>
                        <div>
                            <dt>Waktu penyerahan</dt>
                            <dd>{{ $claim->returned_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j M Y, H:i') }} WIB</dd>
                        </div>
                        @if ($claim->recipient_identifier)
                            <div>
                                <dt>NIM</dt>
                                <dd>{{ $claim->recipient_identifier }}</dd>
                            </div>
                        @endif
                    </dl>
                    @if ($claim->handover_notes)
                        <div class="fh-summary-notes">
                            <h3>Catatan serah terima</h3>
                            <p>{{ $claim->handover_notes }}</p>
                        </div>
                    @endif
                    <div class="fh-summary-downloads">
                        @if ($claim->handover_evidence_path)
                            <a class="fh-summary-button" href="{{ route('admin.claims.handover.evidence', $claim) }}">
                                <svg class="fh-summary-download-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                                    aria-hidden="true" focusable="false">
                                    <path d="m20.5 11.5-8 8a5 5 0 0 1-7-7l9-9a3.5 3.5 0 0 1 5 5l-9 9a2 2 0 0 1-3-3l8-8"/>
                                </svg>
                                <span>Unduh Lampiran</span>
                            </a>
                        @endif
                        <a class="fh-summary-button fh-summary-primary"
                            href="{{ route('admin.claims.handover.pdf', $claim) }}" aria-label="Unduh PDF bukti serah terima">
                            <svg class="fh-summary-download-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                                aria-hidden="true" focusable="false">
                                <path d="M14 3H6a1 1 0 0 0-1 1v16a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V8l-5-5Z"/>
                                <path d="M14 3v5h5M12 10.5v6m-2.5-2.5 2.5 2.5 2.5-2.5"/>
                            </svg>
                            <span>Unduh PDF</span>
                        </a>
                    </div>
                @else
                    <div class="fh-legacy"><strong>Pengembalian lama</strong>
                        <p>{{ $claim->returned_at ? 'Dikembalikan pada ' . $claim->returned_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j M Y, H:i') . ' WIB.' : 'Status sudah dikembalikan.' }}
                            Rincian serah terima belum dicatat pada versi sebelumnya, sehingga PDF tanda terima lengkap
                            belum tersedia.</p>
                    </div>
                @endif
            @endif
        </section>
    </div>

    @if (in_array($claim->status, ['approved', 'returned'], true) && $claim->claimant)
        <dialog class="fcp-dialog" id="fcp-dialog" aria-labelledby="fcp-title">
            <div class="fcp-dialog-shell">
                <section class="fcp-panel" id="percakapan-klaim" aria-labelledby="fcp-title">
                    <header class="fcp-contact fcp-compact-header">
                        <span class="fcp-avatar">{{ mb_strtoupper(mb_substr($claim->claimant->name, 0, 1)) }}</span>
                        <div class="fcp-contact-copy">
                            <h2 id="fcp-title">{{ $claim->claimant->name }}</h2>
                            <small>Klaim K{{ $claim->id }} · {{ $claim->item?->title ?? 'Barang' }}</small>
                        </div>
                        @if ($claim->status === 'returned')
                            <span class="fcp-status is-closed">Arsip</span>
                        @endif
                        <button class="fcp-dialog-close" type="button" data-fcp-close aria-label="Tutup percakapan">✕</button>
                    </header>
                    <div class="fcp-messages" id="fcp-messages" @include('admin.chat.stream-actions')
                        data-receipts-url="{{ route('admin.chat.messages', ['user' => $claim->claimant->id, 'claim_id' => $claim->id]) }}"
                        data-url="{{ $claim->status === 'approved' ? route('admin.chat.messages', ['user' => $claim->claimant->id, 'claim_id' => $claim->id]) : '' }}">
                        @forelse($claimMessages as $msg)
                            <article
                                class="fcp-message {{ (int) $msg->sender_id === (int) auth()->id() ? 'mine' : 'theirs' }}"
                                data-message-id="{{ $msg->id }}" data-sent-at="{{ $msg->created_at->toIso8601String() }}" @if ($msg->read_at) data-read-at="{{ $msg->read_at->toIso8601String() }}" @endif>
                                <div class="fcp-bubble" @if ($ratio = $msg->attachmentPhotoRatio()) style="--chat-photo-ratio: {{ $ratio }}" @endif>
                                    <strong>{{ (int) $msg->sender_id === (int) auth()->id() ? 'Admin FindBack' : $claim->claimant->name }}</strong>
                                    @if ($msg->recalled_at)
                                        <p>Pesan telah ditarik</p>
                                    @elseif ($msg->message)
                                        <p>{{ $msg->message }}</p>
                                    @endif
                                    @if (!$msg->recalled_at && $msg->attachment_path)
                                        @include('shared.chat-attachment', ['path' => $msg->attachment_path, 'label' => 'Lampiran percakapan'])
                                    @endif
                                    @include('admin.chat.message-actions')
                                    <small>{{ $msg->created_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j M Y, H:i') }}
                                        WIB</small>
                                </div>
                            </article>
                        @empty
                            <div class="fcp-empty" id="fcp-empty"><span aria-hidden="true">✉</span><strong>Belum Ada
                                    Percakapan</strong>
                                <p>Mulai koordinasi serah terima melalui kolom pesan di bawah ini.</p>
                            </div>
                        @endforelse
                    </div>
                    @if ($claim->status === 'approved')
                        <div class="fb-chat-action-slot">
                        <form class="fcp-compose" data-chat-compose method="POST"
                            action="{{ route('admin.chat.store', $claim->claimant->id) }}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="from_claim" value="{{ $claim->id }}">
                            <button type="button" class="fcp-attach" data-chat-pick aria-label="Buka menu lampiran" aria-haspopup="dialog">+</button>
                            <input type="file" name="attachment" accept="image/jpeg,image/png,image/webp,video/mp4,video/webm" hidden>
                            <textarea name="message" maxlength="3000" rows="1"
                                placeholder="Tulis pesan untuk {{ $claim->claimant->name }}..." aria-label="Tulis pesan"></textarea>
                            @include('shared.chat-send-button')
                        </form>
                        </div>
                        <p class="fcp-send-error" role="alert" hidden></p>
                    @else
                        <div class="fcp-closed">Serah terima telah selesai. Percakapan tersimpan sebagai arsip dan tidak
                            dapat digunakan untuk mengirim pesan baru.</div>
                    @endif
                </section>
            </div>
        </dialog>

    @endif

    @foreach ([
        ['id' => 'fb-item-photo-dialog', 'url' => $itemPhotoUrl, 'title' => 'Foto Barang', 'pdf' => false],
        ['id' => 'fb-proof-dialog', 'url' => $proofUrl, 'title' => ($isProofPdf ? 'Dokumen' : 'Foto') . ' Bukti Klaim K' . $claim->id, 'pdf' => $isProofPdf],
    ] as $media)
        @continue(!$media['url'])
        <dialog id="{{ $media['id'] }}" class="fb-proof-dialog" aria-labelledby="{{ $media['id'] }}-title">
            <div class="fb-proof-shell">
                <header class="fb-proof-head">
                    <h2 id="{{ $media['id'] }}-title">{{ $media['title'] }}</h2>
                    <div class="fb-proof-actions">
                        <a class="fb-proof-download" href="{{ $media['url'] }}" download aria-label="Unduh lampiran" title="Unduh lampiran">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" aria-hidden="true">
                                <path d="M12 3v12m-4-4 4 4 4-4M4 17v3h16v-3" />
                            </svg>
                        </a>
                        <button type="button" class="fb-proof-close" data-fb-proof-close aria-label="Tutup pratinjau" autofocus>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" aria-hidden="true">
                                <path d="M18 6 6 18M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </header>
                <div class="fb-proof-content">
                    @if ($media['pdf'])
                        <iframe class="fb-proof-document" title="Dokumen bukti klaim" src="{{ $media['url'] }}"></iframe>
                    @else
                        <img class="fb-proof-image" src="{{ $media['url'] }}"
                            alt="{{ $media['title'] }}" loading="lazy">
                    @endif
                </div>
            </div>
        </dialog>
    @endforeach

@endsection
