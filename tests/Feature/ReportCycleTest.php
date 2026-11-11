<?php

namespace Tests\Feature;

use App\Models\{LostFoundItem, ReportAuditEvent, User};
use App\Support\ReportAudit;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{Notification, Storage};
use Tests\TestCase;

class ReportCycleTest extends TestCase
{
    use DatabaseMigrations;

    private function fixture(): array
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $user = User::factory()->create();
        $user->forceFill(['role' => 'user'])->save();
        $item = LostFoundItem::create([
            'user_id' => $user->id,
            'title' => 'Tas awal',
            'type' => 'lost',
            'status' => 'pending',
            'location' => 'FST',
            'description' => 'Tas berwarna hitam',
            'photo_path' => 'reports/lost/old.jpg',
            'event_date' => '2026-10-01',
        ]);
        $admin->notifications()->delete();
        return [$admin, $user, $item];
    }

    private function requestRevision($admin, $item): int
    {
        $this->actingAs($admin);
        $item->revisions()->create([
            'requested_by' => $admin->id,
            'requester_name' => $admin->name,
            'note' => 'Lengkapi lokasi serta ciri khusus barang.',
        ]);
        $item->reporter->notify(new \App\Notifications\ReportRevisionNotification(
            $item->id, $item->title, 'Lengkapi lokasi serta ciri khusus barang.'
        ));
        return $item->revisions()->latest('id')->first()->id;
    }

    private function payload($id): array
    {
        return [
            'revision_id' => $id,
            'title' => 'Tas diperbaiki',
            'location' => 'Laboratorium FST',
            'description' => 'Tas hitam dengan label pemilik',
            'category' => 'Tas',
            'event_date' => '2026-10-01',
            'response_note' => 'Lokasi dan ciri khusus sudah dilengkapi.',
            'confirmation' => 1,
        ];
    }

    public function test_complete_revision_resubmission_and_verification_cycle(): void
    {
        [$admin, $user, $item] = $this->fixture();
        $id = $this->requestRevision($admin, $item);
        $this->actingAs($user)
            ->get(route('user.reports.show', $item))
            ->assertOk()
            ->assertSee('Kirim Ulang untuk Pemeriksaan');
        $this->post(route('user.reports.revision', $item), $this->payload($id))
            ->assertRedirect(route('user.reports.show', $item))
            ->assertSessionHasNoErrors();
        $r = $item->revisions()->first();
        $this->assertSame('resubmitted', $r->closure_kind);
        $this->assertSame('Tas awal', $r->before_data['title']);
        $this->assertSame('Tas diperbaiki', $r->after_data['title']);
        $this->assertSame($user->id, $r->closed_by);
        $this->assertSame('pending', $item->fresh()->status);
        $this->assertSame(1, $admin->notifications()->count());
        $this->actingAs($admin)
            ->get(route('admin.reports.verification'))
            ->assertSee('Tas diperbaiki');
        $this->get(route('admin.reports.verification', ['revision' => 'history']))->assertViewHas(
            'items',
            fn ($items) => $items->isEmpty(),
        );
        $this->get(route('admin.reports.verification.show', $item))
            ->assertOk()
            ->assertSee('Tas awal')
            ->assertSee('Perbaikan dikirim ulang oleh pelapor')
            ->assertSee('Konfirmasi Persetujuan');
        $this->post(route('admin.reports.verification.review', $item), [
            'decision' => 'verified',
        ])->assertSessionHasNoErrors();
        $this->assertSame('verified', $item->fresh()->status);
        $this->assertDatabaseHas('report_audit_events', [
            'event' => 'verified',
            'actor_name' => $admin->name,
        ]);
        $this->actingAs($user)
            ->get(route('user.reports.show', $item))
            ->assertSee('Laporan terverifikasi')
            ->assertDontSee('id="report-revision-form"', false);
    }

    public function test_owner_role_stale_and_tampered_fields(): void
    {
        [$admin, $user, $item] = $this->fixture();
        $id = $this->requestRevision($admin, $item);
        $url = route('user.reports.revision', $item);
        $other = User::factory()->create();
        $other->forceFill(['role' => 'user'])->save();
        $this->actingAs($other)->post($url, $this->payload($id))->assertNotFound();
        $this->actingAs($admin)->post($url, $this->payload($id))->assertForbidden();
        $this->actingAs($user)
            ->post(
                $url,
                $this->payload($id) + [
                    'status' => 'verified',
                    'user_id' => $other->id,
                    'type' => 'found',
                    'reviewed_by' => $user->id,
                ],
            )
            ->assertSessionHasNoErrors();
        $this->assertSame('pending', $item->fresh()->status);
        $this->assertSame('lost', $item->fresh()->type);
        $this->assertSame($user->id, $item->fresh()->user_id);
        $this->assertNull($item->fresh()->reviewed_by);
        $this->post($url, $this->payload($id))->assertSessionHasErrors('revision_id');
        $this->assertSame(1, $admin->notifications()->count());
    }

    public function test_cancellation_and_new_round_do_not_accept_old_response(): void
    {
        [$admin, $user, $item] = $this->fixture();
        $old = $this->requestRevision($admin, $item);
        $this->post(route('admin.reports.verification.revision.cancel', $item), [
            'revision_id' => $old,
            'closure_note' => 'Permintaan keliru dan perlu diganti.',
        ])->assertSessionHasNoErrors();
        $this->assertSame('cancelled', $item->revisions()->find($old)->closure_kind);
        $new = $this->requestRevision($admin, $item);
        $this->actingAs($user)
            ->post(route('user.reports.revision', $item), $this->payload($old))
            ->assertSessionHasErrors('revision_id');
        $this->post(
            route('user.reports.revision', $item),
            $this->payload($new),
        )->assertSessionHasNoErrors();
        $this->get(route('user.reports.show', $item))
            ->assertSee('Permintaan perbaikan dibatalkan')
            ->assertSee('Perbaikan dikirim ulang oleh pelapor');
    }

    public function test_photo_validation_and_replacement_keep_original_evidence(): void
    {
        Storage::fake('public');
        [$admin, $user, $item] = $this->fixture();
        $id = $this->requestRevision($admin, $item);
        Storage::disk('public')->put($item->photo_path, 'old-image');
        $this->actingAs($user);
        $this->post(
            route('user.reports.revision', $item),
            $this->payload($id) + [
                'photo' => UploadedFile::fake()->create('payload.svg', 1, 'image/svg+xml'),
            ],
        )->assertSessionHasErrors('photo');
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aF1sAAAAASUVORK5CYII=',
        );
        $this->post(
            route('user.reports.revision', $item),
            $this->payload($id) + [
                'photo' => UploadedFile::fake()->createWithContent('photo.png', $png),
            ],
        )->assertSessionHasNoErrors();
        Storage::disk('public')->assertExists('reports/lost/old.jpg');
        Storage::disk('public')->assertExists($item->fresh()->photo_path);
        $this->assertNotSame('reports/lost/old.jpg', $item->fresh()->photo_path);
    }

    public function test_invalid_response_does_not_close_request(): void
    {
        [$admin, $user, $item] = $this->fixture();
        $id = $this->requestRevision($admin, $item);
        $data = $this->payload($id);
        $data['event_date'] = now()->addDay()->toDateString();
        $data['response_note'] = '';
        unset($data['confirmation']);
        $this->actingAs($user)
            ->post(route('user.reports.revision', $item), $data)
            ->assertSessionHasErrors(['event_date', 'response_note', 'confirmation']);
        $this->assertNull($item->revisions()->first()->closed_at);
        $this->assertSame('Tas awal', $item->fresh()->title);
    }

    public function test_notification_failure_rolls_back_resubmission_and_photo(): void
    {
        Storage::fake('public');
        [$admin, $user, $item] = $this->fixture();
        $id = $this->requestRevision($admin, $item);
        Notification::shouldReceive('send')->andThrow(
            new \RuntimeException('Simulated notification failure'),
        );
        $this->actingAs($user)
            ->post(route('user.reports.revision', $item), $this->payload($id))
            ->assertSessionHas('error');
        $this->assertSame('Tas awal', $item->fresh()->title);
        $this->assertNull($item->revisions()->first()->closed_at);
        $this->assertSame(0, $admin->notifications()->count());
    }

    public function test_decision_history_survives_operational_status_and_account_rename(): void
    {
        [$admin, $user, $item] = $this->fixture();
        $name = $admin->name;
        $this->actingAs($admin)
            ->post(route('admin.reports.verification.review', $item), ['decision' => 'verified'])
            ->assertSessionHasNoErrors();
        $admin->update(['name' => 'Nama baru']);
        $item->update(['status' => 'returned']);
        $history = ReportAudit::history($item->fresh());
        $event = $history->firstWhere('kind', 'verified');
        $this->assertSame('Laporan terverifikasi', $event['title']);
        $this->assertSame($name, $event['actor']);
        $this->get(route('admin.items.show', $item))
            ->assertSee('Laporan terverifikasi')
            ->assertSee('Barang Dikembalikan');
        $this->post(route('admin.reports.verification.review', $item), [
            'decision' => 'rejected',
            'reason' => 'Tidak boleh menolak laporan final.',
        ])->assertSessionHas('warning');
        $this->assertSame(1, ReportAuditEvent::where('event', 'verified')->count());
    }

    public function test_legacy_history_does_not_invent_verified_decision(): void
    {
        [$admin, $user, $item] = $this->fixture();
        $item
            ->forceFill([
                'status' => 'returned',
                'reviewed_at' => now(),
                'reviewed_by' => $admin->id,
            ])
            ->save();
        $h = ReportAudit::history($item);
        $this->assertFalse($h->contains('title', 'Laporan terverifikasi'));
        $this->assertTrue($h->contains('kind', 'legacy'));
    }

    public function test_filter_intersection_queue_partition_and_dashboard_counts(): void
    {
        [$admin, $user, $item] = $this->fixture();
        $this->requestRevision($admin, $item);
        $ready = LostFoundItem::create([
            'user_id' => $user->id,
            'title' => 'Siap diperiksa',
            'type' => 'found',
            'status' => 'pending',
            'location' => 'FST',
        ]);
        LostFoundItem::create([
            'user_id' => $user->id,
            'title' => 'Selesai kembali',
            'type' => 'found',
            'status' => 'returned',
            'location' => 'FST',
        ]);
        $this->actingAs($admin)
            ->get(route('admin.reports.verification'))
            ->assertViewHas('pendingReadyCount', 2)
            ->assertViewHas('items', fn ($items) => $items->total() === 2);
        $this->get(
            route('admin.reports.verification', [
                'search' => 'BRG-' . str_pad($ready->id, 4, '0', STR_PAD_LEFT),
            ]),
        )->assertSee('Siap diperiksa');
        $this->get(route('admin.items.index', ['group' => 'selesai', 'status' => 'verified']))
            ->assertViewHas('items', fn ($items) => $items->total() === 0)
            ;
        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas(
                'reportAnalytics',
                fn ($a) => $a['total'] === 1 && $a['returned'] === 1,
            );
    }

    public function test_rejection_reason_and_public_exclusion(): void
    {
        [$admin, $user, $item] = $this->fixture();
        $this->actingAs($admin);
        $this->post(route('admin.reports.verification.review', $item), [
            'decision' => 'rejected',
        ])->assertSessionHasErrors('reason');
        $this->post(route('admin.reports.verification.review', $item), [
            'decision' => 'rejected',
            'reason' => 'Informasi lokasi tidak dapat diverifikasi.',
        ])->assertSessionHasNoErrors();
        $this->actingAs($user)
            ->get(route('user.reports.show', $item))
            ->assertSee('Informasi lokasi tidak dapat diverifikasi.');
        $this->get(route('user.items.index'))->assertDontSee('Tas awal');
        $this->get(route('user.items.show', $item))->assertNotFound();
    }

    public function test_creation_logs_actor_and_notifies_admin_for_both_report_types(): void
    {
        Storage::fake('public');
        [$admin, $user] = $this->fixture();
        $this->actingAs($user);
        foreach (['lost', 'found'] as $type) {
            $png = base64_decode(
                'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aF1sAAAAASUVORK5CYII=',
            );
            $data = $this->payload(1);
            $data['photo'] = UploadedFile::fake()->createWithContent('photo.png', $png);
            $this->post(route('user.reports.' . $type . '.store'), $data)
                ->assertSessionHasNoErrors()
                ->assertSessionHas('success');
        }
        $this->assertSame(2, ReportAuditEvent::where('event', 'created')->count());
        $this->assertSame(2, $admin->notifications()->count());
    }

    public function test_failed_review_notification_rolls_back_decision_and_audit(): void
    {
        [$admin, $user, $item] = $this->fixture();
        Notification::shouldReceive('send')->andThrow(new \RuntimeException('Simulated failure'));
        $this->actingAs($admin)
            ->post(route('admin.reports.verification.review', $item), ['decision' => 'verified'])
            ->assertSessionHas('error');
        $this->assertSame('pending', $item->fresh()->status);
        $this->assertNull($item->fresh()->reviewed_at);
        $this->assertSame(0, ReportAuditEvent::where('event', 'verified')->count());
    }

    public function test_migration_rerun_preserves_existing_reports_and_history(): void
    {
        [$admin, $user, $item] = $this->fixture();
        $this->requestRevision($admin, $item);
        $migration = require database_path(
            'migrations/2026_11_01_030000_complete_report_revision_cycle.php',
        );
        $migration->up();
        $this->assertSame('Tas awal', $item->fresh()->title);
        $this->assertSame(1, $item->revisions()->count());
    }

    public function test_user_revision_status_and_notification_destination(): void
    {
        [$admin, $user, $item] = $this->fixture();
        $this->requestRevision($admin, $item);
        $this->actingAs($user)
            ->get(route('user.reports.index'))
            ->assertOk()
            ->assertSee('Menunggu perbaikan Anda')
            ->assertSee('Perbaiki laporan');
        $this->get(route('user.notifications.index'))
            ->assertOk()
            ->assertSee(route('user.reports.show', $item), false);
        $this->get(route('user.reports.show', $item))->assertSee('Menunggu perbaikan Anda');
    }
}
