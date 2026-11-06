@extends('admin.layouts.items')
@section('title', 'Detail Klaim Barang')
@section('breadcrumb', 'Klaim Barang')
@section('content')
    <link rel="stylesheet" href="{{ asset('css/admin/admin-claims.css') }}?v=20261101-final">
    <link rel="stylesheet" href="{{ asset('css/admin/claim-layout-compact-v6.css') }}?v=6">
    <link rel="stylesheet" href="{{ asset('css/admin/claim-layout-premium-v7.css') }}?v=7">
    <link rel="stylesheet" href="{{ asset('css/admin/claim-layout-gap-v9.css') }}?v=9">
    <link rel="stylesheet" href="{{ asset('css/admin/claim-evidence-actions-v11.css') }}?v=11">
    <link rel="stylesheet" href="{{ asset('css/admin/claim-evidence-actions-v12.css') }}?v=12">

    <link rel="stylesheet" href="{{ asset('css/admin/claim-detail-responsive-v14.css') }}?v=14">
    <div class="fb-page-heading fb-claim-heading">
        <div>
            <p class="fb-eyebrow">ADMINISTRASI · FINDBACK KAMPUS</p>
            <h1>Detail <span>Klaim K{{ $claim->id }}</span></h1>
            <p class="fb-heading-text">Pemeriksaan bukti kepemilikan dan keputusan pengembalian barang.</p>
        </div>
        <a class="fb-claim-back fb-claim-back-v14" href="{{ route('admin.claims.index') }}"><svg viewBox="0 0 24 24"
                width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"
                stroke-linejoin="round" aria-hidden="true">
                <path d="M19 12H5m7 7-7-7 7-7" />
            </svg><span>Kembali ke Daftar</span></a>
    </div>

    @if (session('success'))
        <div class="fb-claim-alert" role="status">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="fb-claim-alert is-error" role="alert"><strong>Proses belum berhasil:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="fb-claim-detail-grid">
        <div class="fb-claim-left">
            <section class="fb-claim-panel">
                <div class="fb-claim-panel-head">
                    <div>
                        <h2>Informasi Barang</h2>
                        <p>Laporan barang ditemukan yang diajukan klaim.</p>
                    </div><span
                        class="fb-claim-status status-{{ $claim->status }}">{{ \App\Support\WorkflowStatus::claims()[$claim->status] ?? $claim->status }}</span>
                </div>
                @if ($claim->item?->photo_path)
                    <img class="fb-claim-photo" src="{{ asset('storage/' . $claim->item->photo_path) }}"
                        alt="Foto barang {{ $claim->item->title }}">
                @else
                    <div class="fb-claim-no-photo">Foto barang belum tersedia</div>
                @endif
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
                        <dd>{{ $claim->item?->event_date?->timezone('Asia/Jakarta')->format('d/m/Y') ?? '—' }}</dd>
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
                <h3>Deskripsi barang</h3>
                <p class="fb-claim-description">{{ $claim->item?->description ?: 'Tidak ada deskripsi.' }}</p>
            </section>
            @php($openEvidence = $claim->evidenceRequests->firstWhere('closed_at', null))

            <section class="fb-claim-panel">
                <div class="fb-claim-panel-head">
                    <div>
                        <h2>Bukti Kepemilikan</h2>
                        <p>Jangan menyetujui klaim tanpa pemeriksaan memadai.</p>
                    </div>
                </div>
                <dl class="fb-claim-details">
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
                        <dd>{{ $claim->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i') ?? '—' }} WIB</dd>
                    </div>
                </dl>
                <div class="fb-v11-evidence">
                    <div class="fb-v11-heading"><span class="fb-v11-kicker">KETERANGAN PENGKLAIM</span>
                        <h3>Penjelasan Pengaju</h3>
                    </div>
                    <blockquote class="fb-v11-statement">
                        {{ $claim->proof_description ?: 'Belum ada penjelasan dari pengaju.' }}</blockquote>
                    <div class="fb-v11-action-area">
                        <div class="fb-v11-action-heading">
                            <div><strong>Tindakan Pemeriksaan</strong><small>Periksa foto bukti dan koordinasikan serah
                                    terima barang.</small></div>
                        </div>
                        <div class="fb-v11-actions">
                            @if ($claim->status === 'approved')
                                <button type="button" class="fb-claim-chat-jump fb-v11-btn fb-v11-chat" data-fcp-open><svg
                                        class="fb-v12-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"
                                        stroke-linejoin="round" aria-hidden="true">
                                        <path
                                            d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z" />
                                    </svg> Buka Chat</button>
                            @elseif($claim->status === 'returned')
                                <button type="button" class="fb-claim-chat-jump is-archive fb-v11-btn fb-v11-chat"
                                    data-fcp-open><svg class="fb-v12-icon" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"
                                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path
                                            d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z" />
                                    </svg> Arsip Chat</button>
                            @else
                                <span class="fb-v11-btn fb-v11-disabled"><svg class="fb-v12-icon"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="1.9" stroke-linecap="round"
                                        stroke-linejoin="round" aria-hidden="true">
                                        <rect width="14" height="11" x="5" y="11" rx="2" />
                                        <path d="M8 11V7a4 4 0 0 1 8 0v4" />
                                    </svg> Chat Belum Aktif</span>
                            @endif
                            @if ($claim->proof_photo_path)
                                <a href="{{ asset('storage/' . $claim->proof_photo_path) }}"
                                    class="fb-claim-proof-link fb-v11-btn fb-v11-photo" data-fb-proof-open
                                    aria-haspopup="dialog"><svg class="fb-v12-icon" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"
                                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <rect width="18" height="18" x="3" y="3" rx="2" />
                                        <circle cx="9" cy="9" r="2" />
                                        <path d="m21 15-5-5L5 21" />
                                    </svg> Lihat Foto Bukti</a>
                            @else
                                <span class="fb-v11-btn fb-v11-disabled">Tidak Ada Foto Bukti</span>
                            @endif
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div class="fb-claim-right">
            <section class="fb-claim-panel">
                <div class="fb-claim-panel-head">
                    <div>
                        <h2>Tindakan Administrator</h2>
                        <p>Kelola keputusan klaim dan dokumentasikan serah terima barang.</p>
                    </div>
                </div>

                @if ($claim->status === 'pending' && !$openEvidence)
                    <form method="POST" action="{{ route('admin.claims.review', $claim) }}" class="fb-claim-form">
                        @csrf
                        <input type="hidden" name="decision" value="approved">
                        <label class="fh-confirm"><input type="checkbox" name="evidence_reviewed" value="1"
                                required><span>Saya telah memeriksa identitas dan bukti kepemilikan pengaju serta
                                mencocokkannya dengan barang.</span></label>
                        <button type="submit" class="fb-claim-button is-approve"
                            data-confirm="Yakin menyetujui klaim ini?" data-confirm-event="click">✓ Setujui Klaim</button>
                    </form>
                    <form method="POST" action="{{ route('admin.claims.review', $claim) }}" class="fb-claim-form">
                        @csrf
                        <input type="hidden" name="decision" value="rejected">
                        <label for="rejection_reason">Alasan penolakan <span>*</span></label>
                        <textarea id="rejection_reason" name="rejection_reason" required maxlength="2000" rows="4"
                            placeholder="Tuliskan alasan yang jelas...">{{ old('rejection_reason') }}</textarea>
                        <button type="submit" class="fb-claim-button is-reject" data-confirm="Yakin menolak klaim ini?"
                            data-confirm-event="click">Tolak Klaim</button>
                    </form>
                @elseif($openEvidence)
                    <div class="fb-claim-alert"><strong>Menunggu Bukti Tambahan</strong>
                        <p>Keputusan dan serah terima diblokir selama permintaan terbuka.</p>
                    </div>
                @elseif($claim->status === 'approved')
                    <div class="fh-intro fh-intro-v14">
                        <div class="fh-stage-head-v14"><span class="fh-stage-label-v14"><svg viewBox="0 0 24 24"
                                    width="18" height="18" fill="none" stroke="currentColor"
                                    stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                                    aria-hidden="true">
                                    <rect x="5" y="4" width="14" height="17" rx="2" />
                                    <path d="M9 4.5h6M9 11l2 2 4-4M9 17h6" />
                                </svg>TAHAP AKHIR · SERAH TERIMA</span><span class="fb-handover-stage">Menunggu Serah
                                Terima</span></div>
                        <div class="fh-intro-body-v14">
                            <h3>Catat Penyerahan Barang</h3>
                            <p>Pastikan identitas penerima sesuai dan barang telah diserahkan secara langsung.</p>
                        </div>
                        <div class="fh-intro-note-v14"><svg viewBox="0 0 24 24" width="16" height="16"
                                fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                stroke-linejoin="round" aria-hidden="true">
                                <circle cx="12" cy="12" r="9" />
                                <path d="M12 11v5m0-8h.01" />
                            </svg><span>Data penyerahan disimpan sebagai bukti pengembalian barang.</span></div>
                    </div>
                    <form method="POST" action="{{ route('admin.claims.return', $claim) }}"
                        enctype="multipart/form-data" class="fb-claim-form fh-form">
                        @csrf
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
                        <div class="fh-auto"><strong>Petugas: {{ auth()->user()->name }}</strong><span>Tanggal dan waktu
                                dicatat otomatis ketika disimpan.</span></div>
                        <div><label for="handover_evidence">Foto / dokumen bukti penyerahan <span>*</span></label><input
                                type="file" id="handover_evidence" name="handover_evidence" required
                                accept="image/jpeg,image/png,application/pdf"><small>JPG, PNG, atau PDF · maksimal 5 MB.
                                Berkas hanya dapat diakses admin.</small></div>
                        <div><label for="handover_notes">Catatan penyerahan <span
                                    class="fh-optional">opsional</span></label>
                            <textarea id="handover_notes" name="handover_notes" rows="3" maxlength="2000"
                                placeholder="Kondisi barang atau catatan saat diserahkan...">{{ old('handover_notes') }}</textarea>
                        </div>
                        <label class="fh-confirm"><input type="checkbox" name="handover_confirmed" value="1"
                                required @checked(old('handover_confirmed'))><span>Saya telah mencocokkan identitas penerima dengan
                                pengaju dan memastikan barang sudah diserahkan secara fisik.</span></label>
                        <button type="submit" class="fb-claim-button is-approve"
                            data-confirm="Simpan serah terima? Data tidak dapat diedit dan barang akan ditandai dikembalikan."
                            data-confirm-event="click">Simpan Serah Terima</button>
                        <p>Klaim lain yang masih menunggu untuk barang ini akan ditutup otomatis dengan alasan yang
                            tercatat.</p>
                    </form>
                @else
                    <p class="fb-claim-muted">Klaim ini telah diproses dan tidak dapat diubah dari halaman ini.</p>
                @endif
                @if ($claim->rejection_reason)
                    <div class="fb-claim-rejection"><strong>Alasan penolakan</strong>
                        <p>{{ $claim->rejection_reason }}</p>
                    </div>
                @endif
                @if ($claim->reviewed_at)
                    <p class="fb-claim-muted">Diperiksa oleh {{ $claim->reviewer?->name ?? 'Administrator' }} pada
                        {{ $claim->reviewed_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB.</p>
                @endif
                @if ($claim->status === 'returned')
                    @if ($claim->hasHandoverReceipt())
                        <div class="fh-complete"><span>SERAH TERIMA SELESAI</span>
                            <h3>{{ $claim->handover_code }}</h3>
                            <p>Data penyerahan telah tersimpan. Simpan PDF untuk arsip administrasi.</p>
                        </div>
                        <dl class="fb-claim-details">
                            <div>
                                <dt>Nama penerima</dt>
                                <dd>{{ $claim->recipient_name }}</dd>
                            </div>
                            @if ($claim->recipient_identifier)
                                <div>
                                    <dt>NIM</dt>
                                    <dd>{{ $claim->recipient_identifier }}</dd>
                                </div>
                            @endif
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
                                <dd>{{ $claim->returned_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</dd>
                            </div>
                        </dl>
                        @if ($claim->handover_notes)
                            <h3>Catatan serah terima</h3>
                            <p class="fb-claim-description">{{ $claim->handover_notes }}</p>
                        @endif
                        <div class="fh-downloads"><a class="fb-claim-proof-link"
                                href="{{ route('admin.claims.handover.pdf', $claim) }}">Unduh Tanda Terima PDF</a>
                            @if ($claim->handover_evidence_path)
                                <a class="fb-claim-proof-link"
                                    href="{{ route('admin.claims.handover.evidence', $claim) }}">Unduh Lampiran Bukti</a>
                            @endif
                        </div>
                    @else
                        <div class="fh-legacy"><strong>Pengembalian lama</strong>
                            <p>{{ $claim->returned_at ? 'Dikembalikan pada ' . $claim->returned_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') . ' WIB.' : 'Status sudah dikembalikan.' }}
                                Rincian serah terima belum dicatat pada versi sebelumnya, sehingga PDF tanda terima lengkap
                                belum tersedia.</p>
                        </div>
                    @endif
                @endif
            </section>

            <section class="fb-claim-panel fb-claim-evidence-section">
                <div class="fb-claim-evidence-head">
                    <p class="fb-claim-section-kicker">DOKUMENTASI KLAIM</p>
                    <h2>Permintaan Bukti Tambahan</h2>
                    <p>Catatan permintaan dan tindak lanjut bukti kepemilikan.</p>
                </div>
                @if ($claim->status === 'pending')
                    @if (!$openEvidence)
                        <form method="POST" action="{{ route('admin.claims.evidence-request', $claim) }}"
                            class="fb-claim-form">@csrf
                            <label for="evidence-note">Bukti atau penjelasan yang diperlukan</label>
                            <textarea id="evidence-note" name="note" required minlength="10" maxlength="2000" rows="4"
                                placeholder="Contoh: jelaskan ciri khusus barang atau bukti pembelian yang relevan.">{{ old('note') }}</textarea>
                            <button class="fb-claim-button is-approve" type="submit">Minta Bukti Tambahan</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.claims.evidence-request.cancel', $claim) }}"
                            class="fb-claim-form">@csrf
                            <input type="hidden" name="request_id" value="{{ $openEvidence->id }}">
                            <p>Pembatalan hanya untuk permintaan yang keliru, bukan bukti bahwa pengaju telah melengkapi
                                klaim.</p>
                            <label for="evidence-cancel">Alasan pembatalan</label>
                            <textarea id="evidence-cancel" name="closure_note" required minlength="10" maxlength="2000" rows="4">{{ old('closure_note') }}</textarea>
                            <button class="fb-claim-button is-reject" type="submit"
                                data-confirm="Batalkan permintaan dengan alasan tercatat?"
                                data-confirm-event="click">Batalkan Permintaan</button>
                        </form>
                    @endif
                @endif
                <h3>Riwayat Permintaan</h3>
                @forelse($claim->evidenceRequests as $entry)
                    <article class="fb-claim-rejection">
                        <strong>{{ !$entry->closed_at ? 'Menunggu Bukti Tambahan' : ($entry->closure_type === 'item_returned' ? 'Ditutup: barang diserahkan' : 'Dibatalkan') }}</strong>
                        <p>{{ $entry->requester_name }} ·
                            {{ $entry->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</p>
                        <p class="fb-refactor fb-preserve-lines-wrap">{{ $entry->note }}</p>
                        @if ($entry->closed_at)
                            <p>{{ $entry->closer_name }} ·
                                {{ $entry->closed_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</p>
                            <p class="fb-refactor fb-preserve-lines-wrap">{{ $entry->closure_note }}</p>
                        @endif
                    </article>
                @empty<p>Belum ada permintaan bukti tambahan.</p>
                @endforelse
            </section>
        </div>
    </div>

    @if (in_array($claim->status, ['approved', 'returned'], true) && $claim->claimant)
        <link rel="stylesheet" href="{{ asset('css/chat/findback-claim-premium-v4.css') }}?v=4">
        <dialog class="fcp-dialog" id="fcp-dialog" aria-labelledby="fcp-title">
            <div class="fcp-dialog-shell">
                <button class="fcp-dialog-close" type="button" data-fcp-close aria-label="Tutup percakapan">✕</button>
                <section class="fcp-panel" id="percakapan-klaim" aria-labelledby="fcp-title">
                    <div class="fcp-head">
                        <div class="fcp-head-copy">
                            <span class="fcp-eyebrow">KOMUNIKASI SERAH TERIMA</span>
                            <h2 id="fcp-title">Percakapan Klaim K{{ $claim->id }}</h2>
                            <p>Komunikasi dengan {{ $claim->claimant->name }} untuk mengatur pengambilan dan penyerahan
                                barang.</p>
                        </div>
                        <span
                            class="fcp-status {{ $claim->status === 'approved' ? 'is-active' : 'is-closed' }}">{{ $claim->status === 'approved' ? 'Chat Aktif' : 'Percakapan Ditutup' }}</span>
                    </div>
                    <div class="fcp-contact">
                        <span class="fcp-avatar">{{ mb_strtoupper(mb_substr($claim->claimant->name, 0, 1)) }}</span>
                        <div><strong>{{ $claim->claimant->name }}</strong><small>{{ $claim->claimant->email }}</small>
                        </div>
                        <span class="fcp-context">Klaim K{{ $claim->id }} ·
                            {{ $claim->item?->title ?? 'Barang' }}</span>
                    </div>
                    <div class="fcp-messages" id="fcp-messages"
                        data-url="{{ $claim->status === 'approved' ? route('admin.chat.messages', ['user' => $claim->claimant->id, 'claim_id' => $claim->id]) : '' }}">
                        @forelse($claimMessages as $msg)
                            <article
                                class="fcp-message {{ (int) $msg->sender_id === (int) auth()->id() ? 'mine' : 'theirs' }}"
                                data-message-id="{{ $msg->id }}">
                                <div class="fcp-bubble">
                                    <strong>{{ (int) $msg->sender_id === (int) auth()->id() ? 'Admin FindBack' : $claim->claimant->name }}</strong>
                                    @if ($msg->message)
                                        <p>{{ $msg->message }}</p>
                                    @endif
                                    @if ($msg->attachment_path)
                                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($msg->attachment_path) }}"
                                            target="_blank" rel="noopener noreferrer"><img
                                                src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($msg->attachment_path) }}"
                                                alt="Lampiran percakapan" loading="lazy"></a>
                                    @endif
                                    <small>{{ $msg->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}
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
                        <form class="fcp-compose" method="POST"
                            action="{{ route('admin.chat.store', $claim->claimant->id) }}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="from_claim" value="{{ $claim->id }}">
                            <label class="fcp-attach" title="Lampirkan foto"><span aria-hidden="true">＋</span><span
                                    class="fcp-sr">Pilih foto</span><input type="file" name="attachment"
                                    accept="image/jpeg,image/png,image/webp"></label>
                            <textarea name="message" maxlength="3000" rows="2"
                                placeholder="Tulis pesan untuk {{ $claim->claimant->name }}..." aria-label="Tulis pesan"></textarea>
                            <button type="submit">Kirim Pesan <span aria-hidden="true">↗</span></button>
                        </form>
                    @else
                        <div class="fcp-closed">Serah terima telah selesai. Percakapan tersimpan sebagai arsip dan tidak
                            dapat digunakan untuk mengirim pesan baru.</div>
                    @endif
                </section>
            </div>
        </dialog>
        <script src="{{ asset('js/admin/claim-chat-modal.js') }}"></script>
    @endif

    @if ($claim->proof_photo_path)
        <link rel="stylesheet" href="{{ asset('css/admin/claim-proof-modal-v13.css') }}?v=13">
        <dialog id="fb-proof-dialog" class="fb-proof-dialog" aria-labelledby="fb-proof-title"
            aria-describedby="fb-proof-description">
            <div class="fb-proof-shell">
                <header class="fb-proof-head">
                    <div class="fb-proof-head-copy">
                        <span class="fb-proof-eyebrow">DOKUMENTASI KLAIM</span>
                        <h2 id="fb-proof-title">Foto Bukti Klaim K{{ $claim->id }}</h2>
                        <p id="fb-proof-description">Bukti yang diunggah pengaju untuk mendukung pemeriksaan klaim.</p>
                    </div>
                    <button type="button" class="fb-proof-close" data-fb-proof-close aria-label="Tutup foto bukti">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" aria-hidden="true">
                            <path d="M18 6 6 18M6 6l12 12" />
                        </svg>
                    </button>
                </header>
                <div class="fb-proof-content">
                    @if (strtolower(pathinfo($claim->proof_photo_path, PATHINFO_EXTENSION)) === 'pdf')
                        <iframe class="fb-proof-document" title="Dokumen bukti klaim"
                            src="{{ asset('storage/' . $claim->proof_photo_path) }}"></iframe>
                    @else
                        <img class="fb-proof-image" src="{{ asset('storage/' . $claim->proof_photo_path) }}"
                            alt="Bukti kepemilikan klaim K{{ $claim->id }}" loading="lazy">
                    @endif
                    <dl class="fb-proof-info">
                        <div>
                            <dt>Pengaju</dt>
                            <dd>{{ $claim->claimant?->name ?? 'Tidak tersedia' }}</dd>
                        </div>
                        <div>
                            <dt>Email</dt>
                            <dd>{{ $claim->claimant?->email ?? 'Tidak tersedia' }}</dd>
                        </div>
                        <div>
                            <dt>Tanggal klaim</dt>
                            <dd>{{ $claim->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</dd>
                        </div>
                    </dl>
                </div>
                <footer class="fb-proof-footer">
                    <button type="button" class="fb-proof-cancel" data-fb-proof-close>Tutup</button>
                    <a class="fb-proof-download" href="{{ asset('storage/' . $claim->proof_photo_path) }}" download>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" aria-hidden="true">
                            <path d="M12 3v12m-4-4 4 4 4-4M4 17v3h16v-3" />
                        </svg>
                        Unduh Bukti
                    </a>
                </footer>
            </div>
        </dialog>
        <script src="{{ asset('js/admin/claim-proof-modal.js') }}"></script>
    @endif

    <footer class="fb-footer">© {{ date('Y') }} FindBack Kampus · Fakultas Sains dan Teknologi</footer>
@endsection
