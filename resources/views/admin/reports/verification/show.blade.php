@extends('admin.layouts.items')
@section('title', 'Detail Verifikasi')
@section('page-styles')
    <link rel="stylesheet" href="{{ asset('css/admin/claim-proof-modal.css') }}?v=17">
    <link rel="stylesheet" href="{{ asset('css/admin/verification-actions-history.css') }}?v=4">
    <link rel="stylesheet" href="{{ asset('css/admin/admin-verification.css') }}?v=18">
    <link rel="stylesheet" href="{{ asset('css/admin/findback-pm-detail-date.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/admin/findback-detail-compact.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/admin/findback-detail-responsive-pm.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/admin/findback-detail-cards-spaced.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/admin/verification-detail-card.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('css/pages/admin/detail-back.css') }}?v=1">
@endsection

@section('content')
    <div class="fb-page-heading fb-detail-return-heading">
        <a class="fb-detail-return" data-fb-detail-back href="{{ route('admin.reports.verification', array_merge(request()->only(['search', 'type', 'page']), request('from') === 'history' ? ['revision' => 'history'] : [])) }}"><span aria-hidden="true">←</span> Kembali ke daftar</a>
        <div>
            <p class="fb-eyebrow">ADMINISTRASI · VERIFIKASI LAPORAN</p>
            <h1>Detail <span>Verifikasi</span></h1>
            <p class="fb-heading-text">Periksa identitas pelapor dan detail barang sebelum mengambil keputusan.</p>
        </div>
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
    <div class="fb-v-detail-grid fb-v-single-card">
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
                <div @class(['fb-v-card-body', 'has-photo' => $item->photo_path])>
                    @if ($item->photo_path)
                        @include('admin.partials.report-main-photo')
                    @endif
                    <div class="fb-v-card-facts">
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
                                <dt>Laporan dibuat</dt>
                                <dd>{{ $item->created_at ? $item->created_at->copy()->timezone('Asia/Jakarta')->format('d/m/Y H:i') . ' WIB' : '—' }}</dd>
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
                    </div>
                </div>
                @php($openRevision = $item->status === 'pending' ? $item->revisions->firstWhere('closed_at', null) : null)
                <section class="fb-v-decision fb-v-card-decision">
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
                        <div class="fb-review-actions">
                            @foreach (['verified' => 'Setujui Laporan', 'rejected' => 'Tolak Laporan'] as $value => $label)
                                <button type="button" class="fb-review-button" data-review-tone="{{ $value }}"
                                    data-review-open="fb-review-{{ $value }}" aria-haspopup="dialog"
                                    aria-controls="fb-review-{{ $value }}">
                                    @include('admin.reports.verification.event-icon', ['kind' => $value])
                                    {{ $label }}
                                </button>
                                <dialog id="fb-review-{{ $value }}" class="fb-review-dialog" aria-labelledby="fb-review-title-{{ $value }}"
                                    @if (old('decision') === $value || ($value === 'rejected' && $errors->has('reason'))) data-review-reopen @endif>
                                    <form method="POST" action="{{ route('admin.reports.verification.review', $item) }}">
                                        @csrf
                                        <input type="hidden" name="decision" value="{{ $value }}">
                                        <h2 id="fb-review-title-{{ $value }}">{{ $label }}?</h2>
                                        @if ($value === 'rejected')
                                            <p id="fb-review-reason-hint">Jelaskan apa yang perlu diperbaiki (minimal 10 karakter). Pelapor dapat memperbaiki laporan dan mengirimkannya ulang.</p>
                                            <label for="fb-review-reason">Alasan penolakan <span class="fb-review-required" aria-hidden="true">*</span></label>
                                            <textarea id="fb-review-reason" name="reason" required minlength="10" maxlength="1000" rows="4"
                                                aria-describedby="fb-review-reason-hint @error('reason') fb-review-reason-error @enderror"
                                                @error('reason') aria-invalid="true" @enderror>{{ old('reason') }}</textarea>
                                            @error('reason')
                                                <p id="fb-review-reason-error" role="alert">{{ $message }}</p>
                                            @enderror
                                        @else
                                            <p>Laporan akan dipublikasikan setelah disetujui.</p>
                                        @endif
                                        @error('decision')
                                            <p role="alert">{{ $message }}</p>
                                        @enderror
                                        <div class="fb-review-actions">
                                            <button type="button" class="fb-review-button" data-review-close autofocus>Batal</button>
                                            <button type="submit" class="fb-review-button" data-review-tone="{{ $value }}">
                                                @include('admin.reports.verification.event-icon', ['kind' => $value])
                                                {{ $label }}
                                            </button>
                                        </div>
                                    </form>
                                </dialog>
                            @endforeach
                        </div>
    
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
                </section>
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
                                {{ $response->closer_name ?? '—' }}</span><span class="fb-repair-sent-at"><strong>Waktu pengiriman</strong>
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
                                                                class="fb-repair-photo-link" data-fb-proof-open="fb-proof-dialog"
                                                                data-fb-proof-title="Foto {{ $sideLabel }} Perbaikan"
                                                                aria-haspopup="dialog" aria-controls="fb-proof-dialog"
                                                                aria-label="Perbesar foto {{ strtolower($sideLabel) }} perbaikan">
                                                                <img src="{{ asset('storage/' . ltrim($value, '/')) }}"
                                                                    alt="Foto barang {{ strtolower($sideLabel) }} perbaikan"
                                                                    loading="lazy">
                                                                <span>
                                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                                        stroke="currentColor" stroke-width="2" aria-hidden="true" stroke-linecap="round">
                                                                        <circle cx="10" cy="10" r="6"/><path d="m15 15 5 5"/>
                                                                    </svg>
                                                                    Perbesar Foto
                                                                </span>
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
    </div>
    @include('admin.partials.report-photo-dialog')
@endsection
