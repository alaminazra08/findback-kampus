<?php

namespace App\Support;

use App\Models\{LostFoundItem, ReportAuditEvent, User};

final class ReportAudit
{
    public static function record(
        LostFoundItem $item,
        User $actor,
        string $event,
        ?string $detail = null,
    ): void {
        ReportAuditEvent::create([
            'lost_found_item_id' => $item->id,
            'actor_id' => $actor->id,
            'actor_name' => $actor->name,
            'event' => $event,
            'detail' => $detail,
            'snapshot' => $item->only([
                'title',
                'type',
                'category',
                'location',
                'event_date',
                'description',
                'photo_path',
                'status',
            ]),
            'occurred_at' => now(),
        ]);
    }

    public static function history(LostFoundItem $item): \Illuminate\Support\Collection
    {
        $events = ReportAuditEvent::where('lost_found_item_id', $item->id)
            ->orderBy('occurred_at')
            ->orderBy('id')
            ->get();
        $history = collect();
        foreach ($events as $event) {
            $title = match ($event->event) {
                'created' => 'Laporan dibuat',
                'verified' => 'Laporan terverifikasi',
                'rejected' => 'Laporan ditolak',
                default => 'Aktivitas laporan',
            };
            $history->push([
                'at' => $event->occurred_at,
                'title' => $title,
                'actor' => $event->actor_name,
                'detail' => $event->detail ?? '',
                'kind' => $event->event,
            ]);
        }
        if (!$events->contains('event', 'created') && $item->created_at) {
            $history->push([
                'at' => $item->created_at,
                'title' => 'Laporan dibuat',
                'actor' => $item->reporter?->name ?? 'Akun tidak tersedia',
                'detail' =>
                    'Catatan lama dari waktu pembuatan laporan; nama historis belum disimpan.',
                'kind' => 'created',
            ]);
        }
        if (
            !$events->contains(fn ($e) => in_array($e->event, ['verified', 'rejected'])) &&
            $item->reviewed_at
        ) {
            // Legacy review timestamp is real; do not infer a decision from operational status.
            $history->push([
                'at' => $item->reviewed_at,
                'title' => 'Pemeriksaan laporan tercatat (data lama)',
                'actor' => $item->reviewer?->name ?? 'Petugas tidak tersedia',
                'detail' =>
                    $item->rejection_reason ?:
                    'Keputusan historis belum memiliki snapshot audit. Lihat status terkini secara terpisah.',
                'kind' => 'legacy',
            ]);
        }
        foreach ($item->revisions as $r) {
            $history->push([
                'at' => $r->created_at,
                'title' => 'Perbaikan laporan diminta',
                'actor' => $r->requester_name ?? 'Admin',
                'detail' => $r->note,
                'kind' => 'revision',
            ]);
            if ($r->closed_at) {
                $history->push([
                    'at' => $r->closed_at,
                    'title' =>
                        $r->closure_kind === 'resubmitted'
                            ? 'Perbaikan dikirim ulang oleh pelapor'
                            : ($r->closure_kind === 'cancelled'
                                ? 'Permintaan perbaikan dibatalkan'
                                : 'Permintaan perbaikan ditutup (data lama)'),
                    'actor' => $r->closer_name ?? 'Akun tidak tersedia',
                    'detail' => $r->response_note ?: $r->closure_note,
                    'kind' => 'revision',
                ]);
            }
        }
        return $history
            ->filter(fn ($e) => $e['at'])
            ->sortBy(fn ($e) => $e['at']->timestamp)
            ->values();
    }
}
