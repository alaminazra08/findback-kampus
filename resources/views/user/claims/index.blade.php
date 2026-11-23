@extends('user.layouts.campus')

@section('title', 'Aktivitas Saya')
@section('content')
    

    @include('user.components.activity-heading', ['activityTab' => 'claims'])

    <section class="cu-panel fb-cycle-report-list">
        <form method="GET" action="{{ route('user.claims.index') }}" class="fb-activity-filter fb-claim-list-filter">
            <div class="fb-filter-search">
                <input type="search" name="search" value="{{ request('search') }}" maxlength="100"
                    placeholder="Cari nama barang atau lokasi..." aria-label="Cari klaim">
            </div>
            <select name="type" aria-label="Jenis barang">
                <option value="">Semua jenis</option>
                <option value="lost" @selected(request('type') === 'lost')>Barang hilang</option>
                <option value="found" @selected(request('type') === 'found')>Barang ditemukan</option>
            </select>
            <select name="status" aria-label="Status klaim">
                <option value="">Semua status</option>
                @foreach (['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'returned' => 'Dikembalikan'] as $key => $label)
                    <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="fb-filter-submit"><span>Terapkan</span></button>
            <a href="{{ route('user.claims.index') }}" class="fb-filter-reset">Reset</a>
        </form>

        <div class="cu-table-wrap">
            <table class="fb-report-table">
                <thead>
                    <tr><th>No.</th><th>Barang</th><th>Jenis</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse ($claims as $claim)
                        <tr>
                            <td data-label="No."><span class="fb-report-number">{{ $claims->firstItem() + $loop->index }}</span></td>
                            <td data-label="Barang">
                                <div class="fb-report-item">
                                    <div class="fb-report-photo">
                                        @if ($claim->item_photo)
                                            <img src="{{ asset('storage/' . $claim->item_photo) }}" alt="" loading="lazy">
                                        @else
                                            <div class="fb-report-photo-placeholder" aria-hidden="true">▧</div>
                                        @endif
                                    </div>
                                    <div class="fb-report-item-info">
                                        <strong>{{ $claim->item_title ?: 'Barang tidak tersedia' }}</strong>
                                        <small>{{ $claim->location ?: 'Lokasi tidak tersedia' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Jenis">
                                @if ($claim->item_type)
                                    <span class="fb-report-type {{ $claim->item_type === 'lost' ? 'lost' : 'found' }}">{{ $claim->item_type === 'lost' ? 'Hilang' : 'Ditemukan' }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td data-label="Tanggal">{{ \Carbon\Carbon::parse($claim->created_at)->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j M Y') }}</td>
                            <td data-label="Status">@include('user.components.status', ['status' => $claim->status])</td>
                            <td data-label="Aksi" class="fb-activity-action-cell">
                                @include('user.components.activity-detail-link', ['url' => route('user.claims.show', $claim->id)])
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="cu-empty">Belum ada pengajuan klaim yang sesuai.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($claims->hasPages())
            <div class="ucx-pagination">{{ $claims->links() }}</div>
        @endif
    </section>
@endsection
