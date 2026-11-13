@extends('admin.layouts.items')
@section('title', 'Detail Verifikasi')
@section('page-styles')
    <link rel="stylesheet" href="{{ asset('css/report-cycle.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/admin/admin-verification.css') }}?v=18">
    <link rel="stylesheet" href="{{ asset('css/admin/verification-back-button.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/admin/findback-pm-detail-date.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/admin/findback-detail-compact.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/admin/findback-detail-responsive-pm.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/admin/findback-detail-cards-spaced.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/admin/findback-detail-right-cards.css') }}?v=1">
@endsection

@section('content')
    <div class="fb-page-heading">
        <div>
            <p class="fb-eyebrow">ADMINISTRASI · VERIFIKASI LAPORAN</p>
            <h1>Detail <span>Verifikasi</span></h1>
            <p class="fb-heading-text">Periksa identitas pelapor dan detail barang sebelum mengambil keputusan.</p>
        </div><a class="fb-v-back fb-v-back-compact" href="{{ route('admin.reports.verification', array_merge(request()->only(['search', 'type', 'page']), request('from') === 'history' ? ['revision' => 'history'] : [])) }}"
            aria-label="Kembali ke Verifikasi Laporan"><span aria-hidden="true">←</span> Verifikasi Laporan</a>
    </div>
    @if (session('success'))
        <div class="fb-v-alert is-success" role="status">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="fb-v-alert" role="alert">{{ session('error') }}</div>
    @endif
    @if (session('warning'))
        <div class="fb-v-alert" role="alert">{{ session('warning') }}</div>
    @endif
    @if ($errors->any())
        <div class="fb-v-alert" role="alert"><strong>Periksa input:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="fb-v-detail-grid">
        <div class="fb-v-left-column">
            <section class="fb-panel fb-v-detail-panel">
                <div class="fb-panel-head">
                    <div>
                        <h2>{{ $item->title }}</h2>
                        <p>{{ \App\Support\WorkflowStatus::code($item->id) }} ·
                            {{ $item->type === 'lost' ? 'Barang Hilang' : 'Barang Ditemukan' }}</p>
                    </div><span
                        class="fb-v-status status-{{ $item->status }}">{{ \App\Support\WorkflowStatus::reports()[$item->status] ?? $item->status }}</span>
                </div>
                @if ($item->photo_path)
                    <div class="fb-v-photo"><img src="{{ asset('storage/' . ltrim($item->photo_path, '/')) }}"
                            alt="Foto {{ $item->title }}"></div>
                @endif
                <dl class="fb-v-facts">
                    <div>
                        <dt>Pelapor</dt>
                        <dd>{{ $item->reporter?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>Email pelapor</dt>
                        <dd>{{ $item->reporter?->email ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>Lokasi</dt>
                        <dd>{{ $item->location ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>Tanggal kejadian</dt>
                        <dd>{{ $item->event_date?->format('d/m/Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>Tanggal laporan</dt>
                        <dd>{{ $item->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i') ?? '—' }} WIB</dd>
                    </div>
                </dl>
                @if ($item->reviewed_at)
                    <dl class="fb-v-facts">
                        <div>
                            <dt>Diperiksa oleh</dt>
                            <dd>{{ $decision?->actor_name ?? ($item->reviewer?->name ?? 'Akun tidak tersedia') }}</dd>
                        </div>
                        <div>
                            <dt>Waktu pemeriksaan</dt>
                            <dd>{{ $item->reviewed_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</dd>
                        </div>
                    </dl>
                @elseif($item->status !== 'pending')
                    <p>Riwayat pemeriksaan laporan lama belum tercatat.</p>
                @endif
                <div class="fb-v-description">
                    <h3>Deskripsi Barang</h3>
                    <p>{{ $item->description ?: 'Tidak ada deskripsi.' }}</p>
                </div>
            </section>
            <div class="fb-v-repair-stack">
                @foreach ($item->revisions->where('closure_kind', 'resubmitted') as $response)
                    <section class="fb-cycle-history fb-cycle-diff fb-repair-premium" aria-label="Data perbaikan laporan">
                        <div class="fb-repair-head">
                            <div><span class="fb-repair-kicker">RIWAYAT PEMBARUAN PELAPOR</span>
                                <h2>Data Perbaikan yang Dikirim Ulang</h2>
                                <p class="fb-repair-subtitle">Bandingkan data sebelumnya dengan informasi terbaru yang
                                    dikirim pelapor.</p>
                            </div>
                            <span class="fb-repair-pill">Dikirim ulang</span>
                        </div>
                        <div class="fb-repair-meta"><span><strong>Pelapor</strong>
                                {{ $response->closer_name ?? '—' }}</span><span><strong>Waktu pengiriman</strong>
                                {{ $response->closed_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</span></div>
                        @if ($response->response_note)
                            <div class="fb-repair-note"><strong>Catatan perbaikan</strong>
                                <p>{{ $response->response_note }}</p>
                            </div>
                        @endif
                        <div class="fb-repair-changes">
                            @foreach (['title' => 'Nama barang', 'category' => 'Kategori', 'location' => 'Lokasi', 'event_date' => 'Tanggal kejadian', 'description' => 'Deskripsi', 'photo_path' => 'Foto'] as $key => $label)
                                @if (($response->before_data[$key] ?? null) !== ($response->after_data[$key] ?? null))
                                    <div class="fb-repair-change">
                                        <h3>{{ $label }}</h3>
                                        <div class="fb-repair-compare">
                                            @foreach (['before' => 'Sebelum', 'after' => 'Sesudah'] as $side => $sideLabel)
                                                @php($value = $side === 'before' ? $response->before_data[$key] ?? null : $response->after_data[$key] ?? null)
                                                <div class="fb-repair-value {{ $side === 'after' ? 'is-after' : '' }}">
                                                    <span class="fb-repair-label">{{ $sideLabel }}</span>
                                                    @if ($key === 'photo_path')
                                                        @if ($value)
                                                            <a href="{{ asset('storage/' . ltrim($value, '/')) }}"
                                                                target="_blank" rel="noopener noreferrer"
                                                                class="fb-repair-photo-link"
                                                                aria-label="Lihat foto {{ strtolower($sideLabel) }} dalam ukuran penuh">
                                                                <img src="{{ asset('storage/' . ltrim($value, '/')) }}"
                                                                    alt="Foto barang {{ strtolower($sideLabel) }} perbaikan"
                                                                    loading="lazy">
                                                                <span>Lihat foto penuh ↗</span>
                                                            </a>
                                                        @else
                                                            <p class="fb-repair-empty">Tidak ada foto</p>
                                                        @endif
                                                    @else
                                                        <p class="fb-repair-text">
                                                            {{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value ?? 'Tidak tersedia' }}
                                                        </p>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
        @php($openRevision = $item->status === 'pending' ? $item->revisions->firstWhere('closed_at', null) : null)
        <div class="fb-v-right-column">
            <aside class="fb-panel fb-v-decision">
                @if ($item->status === 'pending' && !$openRevision)
                    <h2>Keputusan Administrator</h2>
                    <p>Setujui hanya jika laporan layak dipublikasikan. Jika ditolak, pelapor dapat memperbaiki laporan yang
                        sama dan mengirim ulang.</p>
                @elseif($openRevision)
                    <h2>Menunggu Perbaikan dari Pelapor</h2>
                    <p>Permintaan perbaikan masih aktif. Keputusan verifikasi sementara diblokir.</p>
                    <div class="fb-v-revision-summary"><strong>Diminta oleh
                            {{ $openRevision->requester_name }}</strong><small>{{ $openRevision->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}
                            WIB</small>
                        <p>{{ $openRevision->note }}</p>
                    </div>
                @else
                    <h2>Hasil Verifikasi</h2>
                    <p>Keputusan pemeriksaan sebelumnya telah dicatat. Status akhir barang dapat berkembang melalui proses
                        klaim dan pengembalian.</p>
                @endif
                @if ($item->status === 'pending' && !$openRevision)
                    <div class="fb-v27-choice" id="fb-v27-choice"
                        data-default="{{ old('decision', $errors->has('reason') ? 'rejected' : 'verified') }}">
                        <div class="fb-v27-choice-head"><strong>Pilih satu keputusan</strong><span>Hanya satu tindakan yang
                                akan diproses</span></div>
                        <div class="fb-v27-options" role="group" aria-label="Pilih keputusan verifikasi">
                            <label class="fb-v27-option fb-v28-approve"><input type="radio" name="fb_v27_choice"
                                    value="verified"><span class="fb-v28-choice-surface"><i class="fb-v28-icon"><svg
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <circle cx="12" cy="12" r="9" />
                                            <path d="m8 12 2.5 2.5L16 9" />
                                        </svg></i><span class="fb-v28-option-copy"><b>Setujui Laporan</b><small>Data lengkap
                                            dan layak dipublikasikan</small></span><i class="fb-v28-selection"
                                        aria-hidden="true"></i></span></label>
                            <label class="fb-v27-option fb-v28-reject"><input type="radio" name="fb_v27_choice"
                                    value="rejected"><span class="fb-v28-choice-surface"><i class="fb-v28-icon"><svg
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <circle cx="12" cy="12" r="9" />
                                            <path d="m9 9 6 6m0-6-6 6" />
                                        </svg></i><span class="fb-v28-option-copy"><b>Tolak Laporan</b><small>Pelapor dapat
                                            memperbaiki lalu mengirim ulang</small></span><i class="fb-v28-selection"
                                        aria-hidden="true"></i></span></label>
                        </div>
                        <div data-fb-v27-panel="verified" hidden>
                            <p class="fb-v27-panel-note fb-v28-note is-approve"><strong>Persetujuan
                                    laporan</strong><span>Pastikan identitas, foto, deskripsi, lokasi, dan kelengkapan
                                    laporan telah diperiksa.</span></p>
                            <form method="POST" action="{{ route('admin.reports.verification.review', $item) }}"
                                class="fb-v27-form">@csrf<input type="hidden" name="decision" value="verified"><button
                                    class="fb-v27-submit is-approve" type="submit"
                                    data-confirm="Yakin menyetujui laporan ini? Pelapor dapat memperbaiki dan mengirim ulang."
                                    data-confirm-event="click">Konfirmasi Persetujuan</button></form>
                        </div>
                        <div data-fb-v27-panel="rejected" hidden>
                            <p class="fb-v27-panel-note fb-v28-note is-reject"><strong>Penolakan
                                    laporan</strong><span>Alasan penolakan wajib jelas, spesifik, dan dapat dipahami
                                    pelapor.</span></p>
                            <form method="POST" action="{{ route('admin.reports.verification.review', $item) }}"
                                class="fb-v27-form">@csrf<input type="hidden" name="decision" value="rejected"><label
                                    for="fb-reason">Alasan penolakan <span>*</span></label>
                                <textarea id="fb-reason" name="reason" minlength="10" maxlength="1000" rows="4"
                                    placeholder="Jelaskan alasan penolakan (minimal 10 karakter)...">{{ old('reason') }}</textarea><button class="fb-v27-submit is-reject" type="submit"
                                    data-confirm="Yakin menolak laporan ini? Pelapor dapat memperbaiki dan mengirim ulang."
                                    data-confirm-event="click">Konfirmasi Penolakan</button>
                            </form>
                        </div>
                        <noscript>
                            <p class="fb-v27-caution">Aktifkan JavaScript untuk memilih tindakan verifikasi.</p>
                        </noscript>
                    </div>
                    <script src="{{ asset('js/pages/admin/reports/verification/show.js') }}"></script>
                @elseif($openRevision)
                    <div class="fb-v-alert"><strong>Menunggu Perbaikan</strong>
                        <p>Keputusan diblokir selama permintaan perbaikan terbuka.</p>
                    </div>
                @else<div class="fb-v-finished">
                        <strong>{{ $item->status === 'rejected' ? 'Laporan ditolak' : ($item->reviewed_at ? 'Pemeriksaan laporan selesai' : 'Keputusan historis belum tercatat') }}</strong>
                        <p>Jika ditolak, pelapor dapat memperbaiki dan mengirim ulang dari halaman laporannya.</p>
                        <dl class="fb-v-result">
                            <div>
                                <dt>Petugas</dt>
                                <dd>{{ $decision?->actor_name ?? ($item->reviewer?->name ?? 'Belum tercatat') }}</dd>
                            </div>
                            <div>
                                <dt>Waktu keputusan</dt>
                                <dd>{{ $item->reviewed_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i') ?? 'Belum tercatat' }}{{ $item->reviewed_at ? ' WIB' : '' }}
                                </dd>
                            </div>
                            @if ($item->rejection_reason)
                                <div>
                                    <dt>Alasan penolakan</dt>
                                    <dd>{{ $item->rejection_reason }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                @endif
                @if ($item->status === 'pending' && $openRevision)
                    <form method="POST" action="{{ route('admin.reports.verification.revision.cancel', $item) }}"
                        class="fb-v-reject-form">@csrf
                        <input type="hidden" name="revision_id" value="{{ $openRevision->id }}">
                        <p>Pembatalan digunakan jika permintaan keliru; bukan tanda pelapor telah memperbaiki laporan.</p>
                        <label for="closure-note">Alasan pembatalan</label>
                        <textarea id="closure-note" name="closure_note" required minlength="10" maxlength="2000" rows="4">{{ old('closure_note') }}</textarea>
                        <button class="fb-v-reject" type="submit"
                            data-confirm="Batalkan permintaan ini dengan alasan yang dicatat?"
                            data-confirm-event="click">Batalkan Permintaan</button>
                    </form>
                    <p class="fb-v-note">Pelapor dapat mengirim ulang dari detail laporannya. Pembatalan hanya digunakan
                        bila permintaan keliru; bukan bukti perbaikan oleh pelapor.</p>
                @endif
            </aside>
            <section class="fb-panel fb-v-history-card" aria-label="Riwayat Verifikasi Laporan">
                <div class="fb-v-side-history">
                    @include('shared.report-history')
                </div>
            </section>
        </div>
    </div>

<script src="{{ asset('js/pages/admin/reports/verification/return-context.js') }}?v=2" defer></script>
@endsection
