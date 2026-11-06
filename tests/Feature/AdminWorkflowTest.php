<?php

namespace Tests\Feature;

use App\Models\{ChatMessage, ItemClaim, ItemMatch, LostFoundItem, User};
use App\Notifications\AdminMessageNotification;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AdminWorkflowTest extends TestCase
{
    use DatabaseMigrations;

    private function user(string $role = 'user'): User
    {
        $u = User::factory()->create();
        $u->forceFill(['role' => $role])->save();
        return $u;
    }

    private function item(array $data = []): LostFoundItem
    {
        return LostFoundItem::create(
            array_merge(
                [
                    'user_id' => $this->user()->id,
                    'title' => 'Dompet uji',
                    'category' => 'Lainnya',
                    'type' => 'found',
                    'status' => 'pending',
                    'description' => 'Kulit cokelat jahitan biru',
                    'location' => 'Gedung A',
                    'event_date' => '2026-10-20',
                ],
                $data,
            ),
        );
    }

    public function test_queue_only_pending_oldest_first_and_decisions_publish_correctly(): void
    {
        $admin = $this->user('admin');
        $old = $this->item(['title' => 'Antrean lama']);
        $old->forceFill(['created_at' => now()->subDays(3)])->save();
        $new = $this->item(['title' => 'Antrean baru']);
        foreach (['verified', 'rejected', 'returned', 'claimed'] as $s) {
            $this->item(['title' => 'Di luar antrean ' . $s, 'status' => $s]);
        }
        $this->actingAs($admin)
            ->get(route('admin.reports.verification', ['status' => 'returned']))
            ->assertRedirect(route('admin.items.index', ['status' => 'returned']));
        $this->get(route('admin.reports.verification'))
            ->assertOk()
            ->assertViewHas('items', fn ($p) => $p->pluck('id')->all() === [$old->id, $new->id])
            ->assertViewHas('total', 6)
            ->assertViewHas('pending', 2);
        $this->post(route('admin.reports.verification.review', $old), [
            'decision' => 'verified',
        ])->assertRedirect(route('admin.reports.verification'));
        $this->post(route('admin.reports.verification.review', $new), [
            'decision' => 'rejected',
            'reason' => 'Foto tidak sesuai dengan laporan.',
        ])->assertRedirect(route('admin.reports.verification'));
        $this->get(route('admin.reports.verification'))
            ->assertSee('Tidak ada laporan yang menunggu verifikasi.')
            ->assertViewHas('items', fn ($p) => $p->isEmpty());
        $this->assertTrue($old->reporter->notifications()->exists());
        $this->assertTrue($new->reporter->notifications()->exists());
        $this->actingAs($this->user())->get(route('user.items.show', $old))->assertOk();
        $this->get(route('user.items.show', $new))->assertNotFound();
    }

    public function test_navigation_and_codes_are_shared_no_raw_blade(): void
    {
        $admin = $this->user('admin');
        $item = $this->item();
        $this->actingAs($admin);
        foreach (
            [
                'admin.dashboard',
                'admin.items.index',
                'admin.reports.verification',
                'admin.claims.index',
                'admin.matching.index',
            ] as $r
        ) {
            $html = $this->get(route($r))->assertOk()->getContent();
            preg_match(
                '/<nav class="fb-nav" aria-label="Menu utama administrator">(.*?)<\/nav>/s',
                $html,
                $m,
            );
            $this->assertNotEmpty($m);
            $this->assertStringNotContainsString('Pesan Pengguna', $m[1]);
            $this->assertSame(6, substr_count($m[1], '<a '));
            $this->assertSame(1, substr_count($m[1], 'aria-current="page"'));
            $this->assertStringNotContainsString('@if', $m[1]);
        }
        $this->get(
            route('admin.items.index', [
                'search' => 'BRG-' . str_pad($item->id, 4, '0', STR_PAD_LEFT),
            ]),
        )
            ->assertViewHas('items', fn ($p) => $p->total() === 1)
            ->assertSee(route('admin.chat.show', $item->user_id), false);
    }

    public function test_matching_v2_ignores_category_and_keeps_historical_scores(): void
    {
        $admin = $this->user('admin');
        $lost = $this->item(['type' => 'lost', 'status' => 'verified']);
        $found = $this->item(['status' => 'verified']);
        $controller = app(\App\Http\Controllers\Admin\MatchingController::class);
        $method = new \ReflectionMethod($controller, 'calculateScore');
        [$one] = $method->invoke($controller, $lost, $found);
        $lost->category = 'Elektronik';
        $found->category = 'Dompet';
        [$two] = $method->invoke($controller, $lost, $found);
        $this->assertSame($one, $two);
        $this->assertSame(100, $one);
        $old = ItemMatch::create([
            'lost_item_id' => $lost->id,
            'found_item_id' => $found->id,
            'match_score' => 61,
            'match_reason' => 'Skor lama',
            'status' => 'confirmed',
        ]);
        $this->actingAs($admin)->post(route('admin.matching.generate'))->assertRedirect();
        $this->assertEquals(61, $old->fresh()->match_score);
        $this->assertSame('confirmed', $old->fresh()->status);
    }

    public function test_read_notification_reopens_exact_record_and_stays_scoped(): void
    {
        $a = $this->user('admin');
        $b = $this->user('admin');
        $item = $this->item();
        $a->notify(
            new AdminMessageNotification('Judul netral', 'Isi netral', 'reports', $item->id),
        );
        $n = $a->notifications()->where('data', 'like', '%Judul netral%')->first();
        $this->actingAs($a)
            ->patch(route('admin.notifications.read', $n->id))
            ->assertRedirect(route('admin.reports.verification.show', $item));
        $this->patch(route('admin.notifications.read', $n->id))->assertRedirect(
            route('admin.reports.verification.show', $item),
        );
        $this->assertNotNull($n->fresh()->read_at);
        $this->actingAs($b)->patch(route('admin.notifications.read', $n->id))->assertNotFound();
    }

    public function test_report_chat_and_paginated_read_receipts_do_not_leak(): void
    {
        $admin = $this->user('admin');
        $u = $this->user();
        $other = $this->user();
        $this->item(['user_id' => $u->id]);
        $this->actingAs($u)
            ->post(route('user.chat.store'), ['message' => 'Koordinasi laporan tanpa klaim'])
            ->assertRedirect();
        for ($i = 0; $i < 55; $i++) {
            ChatMessage::create([
                'user_id' => $u->id,
                'sender_id' => $u->id,
                'message' => 'Pesan ' . $i,
            ]);
        }
        $first = ChatMessage::where('user_id', $u->id)->oldest('id')->first();
        $this->actingAs($admin)
            ->get(route('admin.chat.show', $u))
            ->assertOk()
            ->assertSee('Pesan 54');
        $this->assertNull($first->fresh()->read_at);
        $this->assertSame(50, ChatMessage::whereNotNull('read_at')->count());
        $this->get(route('admin.chat.show', [$u, 'page' => 2]))->assertOk();
        $this->assertNotNull($first->fresh()->read_at);
        $this->actingAs($other)->get(route('admin.chat.show', $u))->assertForbidden();
        $this->getJson(route('user.chat.messages'))->assertForbidden();
    }

    public function test_claim_approval_requires_review_and_never_returns_item(): void
    {
        $admin = $this->user('admin');
        $item = $this->item(['status' => 'verified']);
        $c = ItemClaim::create([
            'user_id' => $this->user()->id,
            'lost_found_item_id' => $item->id,
            'proof_description' => 'Tanda nama pada bagian dalam',
            'status' => 'pending',
        ]);
        $this->actingAs($admin)
            ->post(route('admin.claims.review', $c), ['decision' => 'approved'])
            ->assertSessionHasErrors('evidence_reviewed');
        $this->assertSame('pending', $c->fresh()->status);
        $this->post(route('admin.claims.review', $c), [
            'decision' => 'approved',
            'evidence_reviewed' => '1',
        ])->assertSessionHasNoErrors();
        $this->assertSame('approved', $c->fresh()->status);
        $this->assertSame('verified', $item->fresh()->status);
        $this->assertNull($c->fresh()->returned_at);
    }
}
