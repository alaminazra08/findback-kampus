@extends('admin.layouts.items')
@section('title', 'Detail Barang')
@section('page-styles')
    <link rel="stylesheet" href="{{ asset('css/pages/admin/items/show.css') }}">
@endsection

@section('content')
    <div class="fb-items-heading">
        <div>
            <p class="fb-eyebrow">ADMINISTRASI · DETAIL BARANG</p>
            <h1>Detail <span>Barang</span></h1>
            <p>Informasi lengkap laporan barang yang tersimpan dalam sistem.</p>
        </div>
        <a href="{{ route('admin.items.index') }}" class="fb-items-back">← Kembali ke Laporan Barang</a>
    </div>
    <div class="fb-items-detail-layout">
        <section class="fb-items-panel fb-items-detail" aria-labelledby="fb-detail-title">
            <div class="fb-items-panel-head">
                <div>
                    <h2 id="fb-detail-title">{{ $item->title }}</h2>
                    <p>{{ \App\Support\WorkflowStatus::code($item->id) }}</p>
                </div><span
                    class="fb-items-badge {{ $item->type === 'lost' ? 'is-lost' : 'is-found' }}">{{ $item->type === 'lost' ? 'Hilang' : 'Ditemukan' }}</span>
            </div>
            @if ($item->photo_path)
                <img class="fb-items-photo" src="{{ \Illuminate\Support\Facades\Storage::url($item->photo_path) }}"
                    alt="Foto {{ $item->title }}">
            @else
                <div class="fb-items-no-photo">Foto barang belum tersedia</div>
            @endif
            <h3>Deskripsi</h3>
            <p class="fb-items-description">{{ $item->description ?: 'Belum ada deskripsi barang.' }}</p>
        </section>
        <div class="fb-items-detail-side-stack">
            <aside class="fb-items-panel fb-items-details-aside" aria-label="Informasi laporan">
                <h2>Informasi Laporan</h2>
                <dl>
                    <div>
                        <dt>Lokasi</dt>
                        <dd>{{ $item->location }}</dd>
                    </div>
                    <div>
                        <dt>Tanggal Kejadian</dt>
                        <dd>{{ $item->event_date?->locale('id')->translatedFormat('d F Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>Status</dt>
                        <dd><span
                                class="fb-items-badge status-{{ $item->status }}">{{ \App\Support\WorkflowStatus::report($item->status) }}</span>
                        </dd>
                    </div>
                    <div>
                        <dt>Pelapor</dt>
                        <dd>{{ $item->reporter?->name ?: 'Akun tidak tersedia' }}</dd>
                    </div>
                    <div>
                        <dt>Dicatat</dt>
                        <dd>{{ $item->created_at?->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d F Y, H:i') }}
                            WIB</dd>
                    </div>
                </dl>
                <p class="fb-items-readonly">Keputusan laporan dilakukan melalui Verifikasi Laporan. Penyerahan fisik
                    dicatat melalui Klaim &amp; Pengembalian.</p>
                <div class="fb-workflow-actions"> </div>
            </aside>
            <section class="fb-items-panel fb-v19-history" aria-label="Riwayat aktivitas barang">
                <div class="fb-items-panel-head">
                    <div>
                        <h2>Riwayat Aktivitas Barang</h2>
                        <p>Perjalanan laporan, pemeriksaan, klaim, hingga serah terima berdasarkan catatan sistem.</p>
                    </div>
                </div>
                <ol class="fb-v19-timeline">
                    @forelse($history as $event)
                        <li class="fb-v19-event fb-v19-{{ $event['kind'] }}"><span class="fb-v19-dot"
                                aria-hidden="true"></span>
                            <div><strong>{{ $event['title'] }}</strong><small>{{ $event['at']->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d M Y, H:i') }}
                                    WIB · {{ $event['actor'] }}</small>
                                <p>{{ $event['detail'] }}</p>
                            </div>
                        </li>
                    @empty
                        <li>Belum ada aktivitas tercatat.</li>
                    @endforelse
                </ol>
                @if ($item->status === 'returned' && !$history->contains(fn($event) => $event['kind'] === 'returned'))
                    <p class="fb-v19-legacy">Barang tercatat telah dikembalikan, tetapi waktu atau bukti serah terima lama
                        belum tersedia dalam riwayat terstruktur.</p>
                @endif
            </section>
        </div>
    </div>
@endsection
