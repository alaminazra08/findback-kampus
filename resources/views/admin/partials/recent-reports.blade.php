<section class="fb-panel fb-recent-reports" aria-labelledby="fb-recent-reports-title">
    <div class="fb-panel-head">
        <div>
            <h2 id="fb-recent-reports-title">{{ $recentTitle ?? "Laporan Terbaru" }}</h2>
            <p>{{ $recentCaption }}</p>
        </div><span class="fb-panel-pill">{{ $recentCountLabel ?? (count($recentRows) . " laporan") }}</span>
    </div>
    <div class="fb-dashboard-mobile-reports" aria-label="Daftar laporan terbaru">
        @forelse(($recentRows) as $report)
            <article class="fb-dashboard-mobile-card">
                <div class="fb-dashboard-mobile-top">
                    <span class="fb-dashboard-mobile-photo">
                        @if (!empty($report['photo']))
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($report['photo']) }}"
                                alt="Foto barang {{ $report['name'] ?? '' }}" loading="lazy" decoding="async"
                                data-image-fallback="hidden">
                        @endif
                        <span class="fb-dashboard-mobile-placeholder" @if (!empty($report['photo'])) hidden @endif aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8" cy="8" r="1.5"/><path d="m3 17 6-6 4 4 3-3 5 5"/></svg>
                        </span>
                    </span>
                    <div class="fb-dashboard-mobile-title">
                        <strong>{{ $report['name'] ?? '—' }}</strong>
                        <span class="fb-report-type {{ ($report['type'] ?? '') === 'found' ? 'is-found' : 'is-lost' }}">{{ ($report['type'] ?? '') === 'found' ? 'Ditemukan' : 'Hilang' }}</span>
                    </div>
                </div>
                <div class="fb-dashboard-mobile-details">
                    <span>Lokasi: <strong>{{ $report['location'] ?? '—' }}</strong></span>
                    <span>{{ $report['date'] ?? '—' }}</span>
                </div>
                <div class="fb-dashboard-mobile-footer">
                    <span class="fb-report-status status-{{ $report['status'] ?? '' }}">{{ \App\Support\WorkflowStatus::report($report['status'] ?? '') }}</span>
                    <a href="{{ route('admin.items.show', $report['id']) }}" class="fb-recent-detail"
                           aria-label="Lihat detail {{ $report['name'] ?? 'barang' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M2 12s3.6-6 10-6 10 6 10 6-3.6 6-10 6-10-6-10-6Z"/><circle cx="12" cy="12" r="2.5"/></svg><span>Lihat Detail</span></a>
                </div>
            </article>
        @empty
            <p class="fb-dashboard-mobile-empty">{{ $recentEmpty }}</p>
        @endforelse
    </div>
    <div class="fb-recent-table-wrap">
        <table class="fb-recent-table" aria-labelledby="fb-recent-reports-title">
            <colgroup>
                <col>
                <col class="fb-recent-col-type">
                <col class="fb-recent-col-location">
                <col class="fb-recent-col-date">
                <col class="fb-recent-col-status">
                <col class="fb-recent-col-action">
            </colgroup>
            <thead>
                <tr>
                    <th>Nama Barang</th>
                    <th>Jenis</th>
                    <th>Lokasi</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse(($recentRows) as $report)
                    <tr class="fb-v35-report-row">
                        <td data-label="Nama Barang">
                            <div class="fb-v37-item"><span class="fb-v37-photo">
                                    @if (!empty($report['photo']))
                                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($report['photo']) }}"
                                            alt="Foto barang {{ $report['name'] ?? '' }}" loading="lazy"
                                            decoding="async" data-image-fallback="hidden">
                                    @endif
                                    <span class="fb-v37-photo-fallback"
                                        @if (!empty($report['photo'])) hidden @endif
                                        aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="1.6">
                                            <rect x="3" y="3" width="18" height="18" rx="3" />
                                            <circle cx="8" cy="8" r="1.5" />
                                            <path d="m3 17 6-6 4 4 3-3 5 5" />
                                        </svg></span>
                                </span><strong>{{ $report['name'] ?? '—' }}</strong>
                            </div>
                        </td>
                        <td data-label="Jenis"><span
                                class="fb-report-type {{ ($report['type'] ?? '') === 'found' ? 'is-found' : 'is-lost' }}">{{ ($report['type'] ?? '') === 'found' ? 'Ditemukan' : 'Hilang' }}</span>
                        </td>
                        <td data-label="Lokasi">{{ $report['location'] ?? '—' }}</td>
                        <td data-label="Tanggal">{{ $report['date'] ?? '—' }}</td>
                        <td data-label="Status"><span
                                class="fb-report-status status-{{ $report['status'] ?? '' }}">{{ \App\Support\WorkflowStatus::report($report['status'] ?? '') }}</span>
                        </td>
                        <td data-label="Aksi"><a href="{{ route('admin.items.show', $report['id']) }}" class="fb-recent-detail"
                           aria-label="Lihat detail {{ $report['name'] ?? 'barang' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M2 12s3.6-6 10-6 10 6 10 6-3.6 6-10 6-10-6-10-6Z"/><circle cx="12" cy="12" r="2.5"/></svg><span>Lihat Detail</span></a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="fb-empty">
                            {{ $recentEmpty }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($showAllReportsLink ?? false)
        <div class="fb-dashboard-more-wrap">
            <a href="{{ route('admin.items.index') }}" class="fb-dashboard-more-link">
                <svg class="fb-recent-list-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="4" y="4" width="16" height="16" rx="3"/><path d="M8 9h8M8 15h8"/></svg>
                <span>Lihat Semua Laporan Barang</span> <svg class="fb-dashboard-more-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
            </a>
        </div>
    @endif
</section>
