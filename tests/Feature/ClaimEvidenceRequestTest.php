<?php

namespace Tests\Feature;

use App\Models\{ItemClaim, LostFoundItem, User};
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ClaimEvidenceRequestTest extends TestCase
{
    use DatabaseMigrations;

    private function fixture(): array
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $user = User::factory()->create();
        $item = LostFoundItem::create([
            'user_id' => $user->id,
            'title' => 'Tas bukti uji',
            'type' => 'found',
            'status' => 'verified',
            'location' => 'FST',
            'event_date' => '2026-10-30',
        ]);
        $claim = ItemClaim::create([
            'lost_found_item_id' => $item->id,
            'user_id' => $user->id,
            'proof_description' => 'Bukti kepemilikan awal.',
            'status' => 'pending',
        ]);
        return [$admin, $user, $item, $claim];
    }

    public function test_request_blocks_review_and_handover_and_keeps_cancel_history(): void
    {
        [$admin, $user, $item, $claim] = $this->fixture();
        $this->actingAs($admin);
        $url = route('admin.claims.evidence-request', $claim);
        $this->post($url, [
            'note' => 'Jelaskan ciri khusus pada bagian dalam.',
        ])->assertSessionHasNoErrors();
        $this->assertSame(1, $claim->evidenceRequests()->count());
        $this->assertSame(1, $user->notifications()->count());
        $this->post($url, [
            'note' => 'Permintaan ganda tidak diperbolehkan.',
        ])->assertSessionHasErrors('note');
        foreach (['approved', 'rejected'] as $decision) {
            $this->post(route('admin.claims.review', $claim), [
                'decision' => $decision,
                'evidence_reviewed' => '1',
                'rejection_reason' => 'Alasan penolakan uji',
            ])->assertSessionHasErrors('decision');
        }
        $this->post(route('admin.claims.return', $claim))->assertSessionHasErrors('return');
        $this->assertSame('pending', $claim->fresh()->status);
        $this->get(route('admin.claims.show', $claim))
            ->assertOk()
            ->assertSee('Menunggu Bukti Tambahan')
            ->assertDontSee('✓ Setujui Klaim');
        $this->get(route('admin.claims.index', ['evidence' => 'open']))
            ->assertOk()
            ->assertSee('Tas bukti uji');
        $cancel = route('admin.claims.evidence-request.cancel', $claim);
        $data = [
            'request_id' => $claim->evidenceRequests()->first()->id,
            'closure_note' => 'Permintaan keliru, bukti awal sudah cukup jelas.',
        ];
        $this->post($cancel, $data)->assertSessionHasNoErrors();
        $this->assertNotNull($claim->evidenceRequests()->first()->closed_at);
        $this->post($cancel, $data)->assertSessionHasErrors('closure_note');
        $this->post(route('admin.claims.review', $claim), [
            'decision' => 'approved',
            'evidence_reviewed' => '1',
        ])->assertSessionHasNoErrors();
        $this->assertSame('approved', $claim->fresh()->status);
        $this->post($url, [
            'note' => 'Tidak boleh meminta setelah disetujui.',
        ])->assertSessionHasErrors('note');
    }

    public function test_access_validation_and_cross_claim_cancellation(): void
    {
        [$admin, $user, $item, $claim] = $this->fixture();
        $url = route('admin.claims.evidence-request', $claim);
        $this->post($url)->assertRedirect(route('login'));
        $this->actingAs($user)
            ->post($url, ['note' => 'Permintaan tidak berizin.'])
            ->assertForbidden();
        $this->post(route('admin.claims.evidence-request.cancel', $claim))->assertForbidden();
        $this->actingAs($admin)
            ->post($url, ['note' => '  '])
            ->assertSessionHasErrors('note');
        $this->post($url, ['note' => str_repeat('x', 2001)])->assertSessionHasErrors('note');
        $this->post($url, [
            'note' => 'Lengkapi ciri barang yang dimiliki.',
        ])->assertSessionHasNoErrors();
        [, , , $other] = $this->fixture();
        $this->post(route('admin.claims.evidence-request.cancel', $other), [
            'request_id' => $claim->evidenceRequests()->first()->id,
            'closure_note' => 'Tidak boleh mengubah permintaan klaim lain.',
        ])->assertSessionHasErrors('closure_note');
        $this->assertNull($claim->evidenceRequests()->first()->closed_at);
    }

    public function test_returning_another_claim_closes_pending_evidence_requests(): void
    {
        Storage::fake('local');
        [$admin, $user, $item, $claim] = $this->fixture();
        $this->actingAs($admin);
        $this->post(route('admin.claims.evidence-request', $claim), [
            'note' => 'Lengkapi bukti kepemilikan tas ini.',
        ])->assertSessionHasNoErrors();
        $winner = ItemClaim::create([
            'lost_found_item_id' => $item->id,
            'user_id' => $user->id,
            'proof_description' => 'Bukti terverifikasi.',
            'status' => 'pending',
        ]);
        $this->post(route('admin.claims.review', $winner), [
            'decision' => 'approved',
            'evidence_reviewed' => '1',
        ])->assertSessionHasNoErrors();
        $this->post(route('admin.claims.return', $winner), [
            'recipient_name' => 'Penerima uji',
            'handover_location' => 'FST',
            'handover_confirmed' => '1',
            'handover_evidence' => UploadedFile::fake()->createWithContent(
                'bukti.pdf',
                "%PDF-1.4\n%%EOF",
            ),
        ])->assertSessionHasNoErrors();
        $this->assertSame('rejected', $claim->fresh()->status);
        $entry = $claim->evidenceRequests()->first();
        $this->assertNotNull($entry->closed_at);
        $this->assertSame('item_returned', $entry->closure_type);
        $this->get(route('admin.claims.show', $claim))
            ->assertOk()
            ->assertSee('Ditutup: barang diserahkan');
    }

    public function test_ineligible_item_and_other_winner_are_rejected(): void
    {
        [$admin, $user, $item, $claim] = $this->fixture();
        $this->actingAs($admin);
        $url = route('admin.claims.evidence-request', $claim);
        $item->update(['status' => 'pending']);
        $this->post($url, [
            'note' => 'Bukti tambahan belum dapat diminta.',
        ])->assertSessionHasErrors('note');
        $item->update(['status' => 'verified', 'type' => 'lost']);
        $this->post($url, [
            'note' => 'Bukti tambahan belum dapat diminta.',
        ])->assertSessionHasErrors('note');
        $item->update(['type' => 'found']);
        ItemClaim::create([
            'lost_found_item_id' => $item->id,
            'user_id' => $user->id,
            'proof_description' => 'Bukti sah',
            'status' => 'approved',
        ]);
        $this->post($url, ['note' => 'Sudah ada pemenang klaim lainnya.'])->assertSessionHasErrors(
            'note',
        );
        $this->assertSame(0, $claim->evidenceRequests()->count());
    }
}
