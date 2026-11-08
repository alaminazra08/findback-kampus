<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\ItemClaim;
use App\Models\ItemMatch;
use App\Models\LostFoundItem;
use App\Models\User;
use App\Notifications\AdminMessageNotification;
use App\Support\AdminSettings;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminFoundationTest extends TestCase
{
    use DatabaseMigrations;

    private function account(string $role = 'user'): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => $role])->save();
        return $user;
    }

    private function item(array $attributes = []): LostFoundItem
    {
        return LostFoundItem::create(
            array_merge(
                [
                    'user_id' => $this->account()->id,
                    'title' => 'Dompet hitam',
                    'category' => 'Dompet',
                    'type' => 'found',
                    'status' => 'verified',
                    'location' => 'Gedung A',
                    'description' => 'Kulit hitam',
                    'event_date' => '2026-10-29',
                ],
                $attributes,
            ),
        );
    }

    private function setting(string $key, string $value): void
    {
        AppSetting::updateOrCreate(['setting_key' => $key], ['setting_value' => $value]);
    }

    public function test_admin_routes_reject_regular_users_and_guests(): void
    {
        $this->get(route('admin.settings.index'))->assertRedirect(route('login'));
        $this->actingAs($this->account())->get(route('admin.settings.index'))->assertForbidden();
    }

    public function test_all_admin_list_pages_render_with_unread_notification(): void
    {
        $admin = $this->account('admin');
        $admin->notify(new AdminMessageNotification('Tes', 'Notifikasi belum dibaca'));
        $lost = $this->item(['type' => 'lost', 'status' => 'pending']);
        $found = $this->item();
        ItemClaim::create([
            'lost_found_item_id' => $found->id,
            'user_id' => $this->account()->id,
            'proof_description' => 'Bukti pengujian daftar',
            'status' => 'pending',
        ]);
        ItemMatch::create([
            'lost_item_id' => $lost->id,
            'found_item_id' => $found->id,
            'match_score' => 80,
            'status' => 'pending',
        ]);
        $this->actingAs($admin);
        foreach (
            [
                'dashboard',
                'items.index',
                'reports.lost',
                'reports.found',
                'reports.verification',
                'claims.index',
                'matching.index',
                'statistics.index',
                'users.index',
                'settings.index',
            ] as $route
        ) {
            $this->get(route('admin.' . $route))->assertOk();
        }
        $this->get(route('admin.dashboard'))->assertSee('Dompet hitam');
        $this->get(route('admin.statistics.index'))->assertSee('Dompet hitam');
    }

    public function test_notification_read_is_scoped_to_current_admin(): void
    {
        $admin = $this->account('admin');
        $other = $this->account('admin');
        $admin->notify(new AdminMessageNotification('Tes', 'Milik admin'));
        $other->notify(new AdminMessageNotification('Tes', 'Milik admin lain'));
        $mine = $admin->notifications()->first();
        $theirs = $other->notifications()->first();
        $this->actingAs($admin)
            ->patch(route('admin.notifications.read', $theirs->id))
            ->assertNotFound();
        $this->patch(route('admin.notifications.read', $mine->id))->assertRedirect();
        $this->assertNotNull($mine->fresh()->read_at);
        $admin->notify(new AdminMessageNotification('Baru', 'Pesan kedua'));
        $this->post(route('admin.notifications.read-all'))->assertRedirect();
        $this->assertSame(0, $admin->unreadNotifications()->count());
        $this->assertNull($theirs->fresh()->read_at);
    }

    public function test_report_rejection_keeps_audit_and_sends_user_notification_even_when_admin_alerts_disabled(): void
    {
        $this->setting('notification_reports', '0');
        $admin = $this->account('admin');
        $item = $this->item(['status' => 'pending']);
        $this->actingAs($admin)
            ->post(route('admin.reports.verification.review', $item), [
                'decision' => 'rejected',
                'reason' => 'Informasi lokasi belum lengkap.',
            ])
            ->assertRedirect();
        $item->refresh();
        $this->assertSame('rejected', $item->status);
        $this->assertEquals($admin->id, $item->reviewed_by);
        $this->assertNotNull($item->reviewed_at);
        $this->assertSame('Informasi lokasi belum lengkap.', $item->rejection_reason);
        $this->assertSame(1, $item->reporter->notifications()->count());
        $this->get(route('admin.reports.verification.show', $item))
            ->assertOk()
            ->assertSee($item->rejection_reason);
        $this->post(route('admin.reports.verification.review', $item), [
            'decision' => 'verified',
        ])->assertRedirect();
        $this->assertSame('rejected', $item->fresh()->status);
    }

    public function test_report_rejection_requires_reason(): void
    {
        $item = $this->item(['status' => 'pending']);
        $this->actingAs($this->account('admin'))
            ->post(route('admin.reports.verification.review', $item), [
                'decision' => 'rejected',
            ])
            ->assertSessionHasErrors('reason');
        $this->assertSame('pending', $item->fresh()->status);
    }

    public function test_admin_alert_preferences_apply_to_new_reports_and_claims(): void
    {
        $admin = $this->account('admin');
        $this->setting('notification_reports', '0');
        $this->item(['status' => 'pending']);
        $this->assertSame(0, $admin->notifications()->count());
        $this->setting('notification_reports', '1');
        $this->item(['status' => 'pending']);
        $this->assertSame(1, $admin->notifications()->count());
        $found = $this->item();
        foreach (['0', '1'] as $enabled) {
            $this->setting('notification_claims', $enabled);
            ItemClaim::create([
                'lost_found_item_id' => $found->id,
                'user_id' => $this->account()->id,
                'proof_description' => 'Ada kartu identitas di dalam',
                'status' => 'pending',
            ]);
        }
        $this->assertSame(2, $admin->notifications()->count());
    }

    public function test_matching_uses_saved_threshold_and_does_not_duplicate_pairs(): void
    {
        $admin = $this->account('admin');
        $this->item(['type' => 'lost']);
        $this->item(['title' => 'Tas merah']);
        $this->setting('matching_min_score', '100');
        $this->actingAs($admin)->post(route('admin.matching.generate'))->assertRedirect();
        $this->assertSame(0, ItemMatch::count());
        $this->setting('matching_min_score', '0');
        $this->post(route('admin.matching.generate'))->assertRedirect();
        $this->assertSame(1, ItemMatch::count());
        $this->post(route('admin.matching.generate'))->assertRedirect();
        $this->assertSame(1, ItemMatch::count());
    }

    public function test_matching_alert_switch_controls_generated_candidate_alerts(): void
    {
        $admin = $this->account('admin');
        $this->item(['type' => 'lost']);
        $this->item();
        $this->setting('notification_matching', '0');
        $this->actingAs($admin)->post(route('admin.matching.generate'))->assertRedirect();
        $this->assertSame(0, $admin->notifications()->count());
        $this->item();
        $this->setting('notification_matching', '1');
        $this->post(route('admin.matching.generate'))->assertRedirect();
        $this->assertSame(1, $admin->notifications()->count());
    }

    public function test_conflicting_match_cannot_be_confirmed(): void
    {
        $lost = $this->item(['type' => 'lost']);
        $found = $this->item();
        $first = ItemMatch::create([
            'lost_item_id' => $lost->id,
            'found_item_id' => $found->id,
            'match_score' => 90,
            'status' => 'pending',
        ]);
        $second = ItemMatch::create([
            'lost_item_id' => $lost->id,
            'found_item_id' => $this->item()->id,
            'match_score' => 80,
            'status' => 'pending',
        ]);
        $this->actingAs($this->account('admin'));
        $this->post(route('admin.matching.review', $first), [
            'action' => 'confirm',
        ])->assertRedirect();
        $this->post(route('admin.matching.review', $second), [
            'action' => 'confirm',
        ])->assertSessionHas('error');
        $this->assertSame('confirmed', $first->fresh()->status);
        $this->assertSame('pending', $second->fresh()->status);
    }

    public function test_claim_cannot_have_two_winners_and_can_only_be_returned_once(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        $found = $this->item();
        $claims = collect([1, 2])->map(
            fn () => ItemClaim::create([
                'lost_found_item_id' => $found->id,
                'user_id' => $this->account()->id,
                'proof_description' => 'Ciri khusus kepemilikan',
                'status' => 'pending',
            ]),
        );
        $this->actingAs($this->account('admin'));
        $this->post(route('admin.claims.review', $claims[0]), [
            'decision' => 'approved',
            'evidence_reviewed' => '1',
        ])->assertRedirect();
        $this->post(route('admin.claims.review', $claims[1]), [
            'decision' => 'approved',
            'evidence_reviewed' => '1',
        ])->assertSessionHasErrors('decision');
        $this->post(route('admin.claims.return', $claims[0]), [
            'recipient_name' => 'Penerima terverifikasi',
            'handover_location' => 'Ruang FST',
            'handover_confirmed' => '1',
            'handover_evidence' => \Illuminate\Http\UploadedFile::fake()->createWithContent(
                'bukti.pdf',
                "%PDF-1.4\n%%EOF",
            ),
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();
        $this->assertSame('returned', $found->fresh()->status);
        $this->post(route('admin.claims.return', $claims[0]))->assertSessionHasErrors('return');
    }

    public function test_admin_cannot_demote_own_account(): void
    {
        $admin = $this->account('admin');
        $this->actingAs($admin)
            ->patch(route('admin.users.update-role', $admin), ['role' => 'user'])
            ->assertSessionHas('error');
        $this->assertSame('admin', $admin->fresh()->role);
    }

    public function test_settings_save_updates_identity_and_threshold(): void
    {
        $admin = $this->account('admin');
        $data = array_replace(AdminSettings::DEFAULTS, [
            'site_name' => 'FindBack FST',
            'institution_name' => 'FST UIN Imam Bonjol',
            'matching_min_score' => '75',
            'contact_email' => 'admin@example.test',
        ]);
        $this->actingAs($admin)
            ->put(route('admin.settings.update'), $data)
            ->assertSessionHasNoErrors()
            ->assertRedirect();
        $this->assertSame(75, AdminSettings::minimumScore());
        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('FindBack FST')
            ->assertSee('admin@example.test');
        $this->get(route('admin.items.index'))->assertOk()->assertSee('FST UIN Imam Bonjol');
    }

    public function test_migration_preserves_both_legacy_name_and_title_columns(): void
    {
        $item = $this->item();
        Schema::table('lost_found_items', fn ($t) => $t->string('name')->default(''));
        DB::table('lost_found_items')
            ->where('id', $item->id)
            ->update(['title' => '', 'name' => 'Barang lama']);
        $migration = require database_path(
            'migrations/2026_10_30_000001_complete_admin_schema.php',
        );
        $migration->up();
        $this->assertSame('Barang lama', $item->fresh()->title);
        $this->assertNotNull($this->item()->id);
    }

    public function test_migration_preserves_legacy_name_data_and_existing_claims(): void
    {
        $item = $this->item();
        $claim = ItemClaim::create([
            'lost_found_item_id' => $item->id,
            'user_id' => $this->account()->id,
            'proof_description' => 'Bukti lama',
            'status' => 'pending',
        ]);
        Schema::table('lost_found_items', fn ($t) => $t->renameColumn('title', 'name'));
        $migration = require database_path(
            'migrations/2026_10_30_000001_complete_admin_schema.php',
        );
        $migration->up();
        $this->assertSame('Dompet hitam', $item->fresh()->title);
        $this->assertSame('Bukti lama', $claim->fresh()->proof_description);
        $migration->up(); // Re-running compatibility operations must preserve rows.
        $this->assertDatabaseCount('item_claims', 1);
    }
}
