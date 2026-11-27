<section class="fb-activity-summary" aria-label="Ringkasan aktivitas">
    @foreach ($activityStats as $stat)
        <article class="fb-activity-stat {{ $stat['tone'] }}">
            <div class="fb-activity-stat-head">
                <span class="fb-activity-stat-label">{{ $stat['label'] }}</span>
                <span class="fb-activity-stat-icon" aria-hidden="true">
                    @if ($stat['icon'] === 'report')
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 3h7l5 5v13H7z"/><path d="M14 3v5h5M10 13h6M10 17h6"/></svg>
                    @elseif ($stat['icon'] === 'clock')
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                    @elseif ($stat['icon'] === 'check')
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 4 4L19 6"/></svg>
                    @else
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m20.5 13.5-7.5 7.5-10-10 7.5-7.5a2 2 0 0 1 2.8 0l7.2 7.2a2 2 0 0 1 0 2.8Z"/><circle cx="8.5" cy="8.5" r="1.3"/></svg>
                    @endif
                </span>
            </div>
            <div class="fb-activity-stat-value">
                <strong>{{ $stat['value'] }}</strong>
                <span>Aktivitas tercatat</span>
            </div>
            <span class="fb-activity-stat-accent" aria-hidden="true"></span>
        </article>
    @endforeach
</section>
