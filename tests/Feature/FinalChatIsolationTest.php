<?php

namespace Tests\Feature;

use App\Models\{ChatMessage, ItemClaim, LostFoundItem, User};
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class FinalChatIsolationTest extends TestCase
{
    use DatabaseMigrations;

    private function fixture(): array
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $owner = User::factory()->create(['role' => 'user']);
        $claims = [];
        foreach (range(1, 2) as $i) {
            $item = LostFoundItem::create(['user_id' => $owner->id, 'type' => 'found',
                'status' => 'verified', 'title' => 'Barang ' . $i, 'location' => 'FST']);
            $claims[] = ItemClaim::create(['user_id' => $user->id,
                'lost_found_item_id' => $item->id, 'status' => 'approved',
                'proof_description' => 'Ada tanda khusus milik saya']);
        }
        return [$admin, $user, $owner, $claims];
    }

    public function test_multiple_approved_claims_without_history_show_selector(): void
    {
        [$admin, $user, $owner, $claims] = $this->fixture();
        $this->actingAs($user)->get(route('user.chat.index'))->assertOk()
            ->assertSee('Pilih percakapan klaim')->assertSee('Barang 1')->assertSee('Barang 2');
    }

    public function test_inbox_reads_only_displayed_claim_and_not_other_claims(): void
    {
        [$admin, $user, $owner, $claims] = $this->fixture();
        $first = ChatMessage::create(['user_id' => $user->id, 'sender_id' => $user->id,
            'claim_id' => $claims[0]->id, 'message' => 'Pertama']);
        $second = ChatMessage::create(['user_id' => $user->id, 'sender_id' => $user->id,
            'claim_id' => $claims[1]->id, 'message' => 'Kedua']);
        $this->actingAs($admin)->get(route('admin.chat.index', ['user' => $user->id]))->assertOk();
        $this->assertNull($first->fresh()->read_at);
        $this->get(route('admin.chat.index', ['user' => $user->id, 'claim_id' => $claims[0]->id]))
            ->assertOk()->assertSee('Pertama')->assertDontSee('Kedua');
        $this->assertNotNull($first->fresh()->read_at);
        $this->assertNull($second->fresh()->read_at);
    }

    public function test_bidirectional_polling_and_ownership_do_not_mix_claims(): void
    {
        [$admin, $user, $owner, $claims] = $this->fixture();
        $this->actingAs($user)->post(route('user.chat.store'),
            ['claim_id' => $claims[0]->id, 'message' => 'User A'])->assertSessionHasNoErrors();
        $this->actingAs($admin)->post(route('admin.chat.store', $user),
            ['from_claim' => $claims[1]->id, 'message' => 'Admin B'])->assertSessionHasNoErrors();
        $this->actingAs($user)->getJson(route('user.chat.messages', ['claim_id' => $claims[0]->id]))
            ->assertOk()->assertJsonCount(1, 'messages')->assertJsonPath('messages.0.message', 'User A');
        $this->getJson(route('user.chat.messages', ['claim_id' => $claims[1]->id]))
            ->assertOk()->assertJsonCount(1, 'messages')->assertJsonPath('messages.0.message', 'Admin B');
        $this->actingAs($owner)->getJson(route('user.chat.messages', ['claim_id' => $claims[0]->id]))
            ->assertForbidden();
        $this->post(route('user.chat.store'), ['claim_id' => $claims[0]->id, 'message' => 'Invalid'])
            ->assertForbidden();
        $claims[0]->update(['status' => 'returned']);
        $this->actingAs($user)->post(route('user.chat.store'),
            ['claim_id' => $claims[0]->id, 'message' => 'Closed'])->assertForbidden();
        $this->actingAs($admin)->post(route('admin.chat.store', $user),
            ['from_claim' => $claims[0]->id, 'message' => 'Closed'])->assertForbidden();
    }

    public function test_archive_link_does_not_select_single_active_claim(): void
    {
        [$admin, $user, $owner, $claims] = $this->fixture();
        $claims[1]->update(['status' => 'returned']);
        ChatMessage::create(['user_id' => $user->id, 'sender_id' => $admin->id,
            'message' => 'Arsip unik']);
        $this->actingAs($user)->get(route('user.chat.index', ['legacy' => 1]))
            ->assertOk()->assertSee('Arsip unik')->assertViewHas('selectedClaim', null);
        $this->assertSame(0, ChatMessage::whereNull('read_at')->count());
    }

    public function test_claim_review_creates_exactly_one_result_notification(): void
    {
        [$admin, $user, $owner, $claims] = $this->fixture();
        $claims[0]->update(['status' => 'pending']);
        $user->notifications()->delete();
        $this->actingAs($admin)->post(route('admin.claims.review', $claims[0]),
            ['decision' => 'approved', 'evidence_reviewed' => 1])->assertSessionHasNoErrors();
        $this->assertSame(1, $user->notifications()->count());
        $this->assertSame('approved', $claims[0]->fresh()->status);
    }
    public function test_rejected_report_can_be_corrected_and_reverified(): void
    {
        [$admin, $user, $owner] = $this->fixture();
        $item = LostFoundItem::create(['user_id' => $user->id, 'type' => 'lost',
            'status' => 'pending', 'title' => 'Tas awal', 'location' => 'FST',
            'photo_path' => 'reports/lost/test.jpg']);
        $this->actingAs($admin)->post(route('admin.reports.verification.review', $item),
            ['decision' => 'rejected', 'reason' => 'Lengkapi lokasi dan ciri barang.'])->assertSessionHasNoErrors();
        $this->actingAs($user)->get(route('user.reports.show', $item))->assertOk()->assertSee('Lengkapi lokasi');
        $this->post(route('user.reports.revision', $item), ['revision_id' => 0,
            'title' => 'Tas diperbaiki', 'category' => 'Tas', 'location' => 'Laboratorium',
            'event_date' => now()->toDateString(), 'description' => 'Tas dengan label nama',
            'response_note' => 'Sudah memperjelas lokasi barang', 'confirmation' => 1])
            ->assertSessionHasNoErrors();
        $this->assertSame('pending', $item->fresh()->status);
        $this->assertSame('Tas', $item->fresh()->category);
        $this->actingAs($admin)->post(route('admin.reports.verification.review', $item),
            ['decision' => 'verified'])->assertSessionHasNoErrors();
        $this->actingAs($user)->get(route('user.reports.show', $item))->assertOk()->assertSee('Laporan terverifikasi');
        $this->assertSame('verified', $item->fresh()->status);
    }

}
