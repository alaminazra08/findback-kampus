@extends('user.layouts.campus')
@section('title', 'Aktivitas Saya')
@section('content')
    <link rel="stylesheet" href="{{ asset('css/report-cycle.css') }}?v=1">
    <div class="cu-title">
        <h1>Aktivitas saya</h1>
        <p>Pantau perkembangan laporan dan klaim barangmu.</p>
    </div>
    <nav class="cu-tabs"><a class="active" href="{{ route('user.reports.index') }}">Riwayat laporan</a><a
            href="{{ route('user.claims.index') }}">Pengajuan klaim</a></nav>
    <section class="cu-panel fb-cycle-report-list">
        <form class="cu-inline-filter"><input name="search" value="{{ request('search') }}"
                placeholder="Cari nama barang atau lokasi" aria-label="Cari laporan"><select name="type"
                aria-label="Jenis laporan">
                <option value="">Semua jenis</option>
                <option value="lost" @selected(request('type') === 'lost')>Barang hilang</option>
                <option value="found" @selected(request('type') === 'found')>Barang ditemukan</option>
            </select><select name="status" aria-label="Status">
                <option value="">Semua status</option>
                @foreach (['pending' => 'Menunggu', 'verified' => 'Terverifikasi', 'rejected' => 'Ditolak', 'returned' => 'Dikembalikan'] as $key => $label)
                    <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="cu-btn">Terapkan</button><a href="{{ route('user.reports.index') }}">Reset</a>
        </form>
        <div class="cu-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Jenis</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                        <tr>
                            <td data-label="Barang">
                                <strong>{{ $report->title }}</strong><small>{{ $report->location }}</small>
                            </td>
                            <td data-label="Jenis">{{ $report->type === 'lost' ? 'Hilang' : 'Ditemukan' }}</td>
                            <td data-label="Tanggal">{{ $report->event_date?->format('d M Y') }}</td>
                            <td data-label="Status">
                                @if ($report->status === 'rejected')
                                    <span class="cu-badge amber">Ditolak · Perlu Perbaikan</span>
                                @elseif($report->status === 'pending' && $report->awaiting_revision)
                                    <span class="cu-badge amber">Menunggu perbaikan Anda</span>
                                @else
                                    @include('user.components.status', ['status' => $report->status])
                                @endif
                            </td>
                            <td data-label="Aksi"><a class="cu-btn light small"
                                    href="{{ route('user.reports.show', $report) }}">{{ $report->status === 'rejected' || ($report->status === 'pending' && $report->awaiting_revision) ? 'Perbaiki laporan' : 'Lihat' }}</a>
                            </td>
                    </tr>@empty<tr>
                            <td colspan="5">
                                <div class="cu-empty">Belum ada laporan yang sesuai.<br><a
                                        href="{{ route('user.reports.lost.create') }}">Buat laporan pertama →</a></div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>@include('user.components.pages', ['paginator' => $reports])
    </section>
@endsection
