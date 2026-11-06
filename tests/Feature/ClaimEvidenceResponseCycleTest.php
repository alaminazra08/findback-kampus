<?php

namespace Tests\Feature;

use App\Models\{ItemClaim, LostFoundItem, User};
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ClaimEvidenceResponseCycleTest extends TestCase
{
    use DatabaseMigrations;

    public function test_user_response_closes_only_owned_active_request_and_preserves_original_proof(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $owner = User::factory()->create();
        $owner->forceFill(['role' => 'user'])->save();
        $other = User::factory()->create();
        $other->forceFill(['role' => 'user'])->save();
        $item = LostFoundItem::create([
            'user_id' => $owner->id,
            'title' => 'Tas test',
            'type' => 'found',
            'status' => 'verified',
            'location' => 'FST',
            'event_date' => '2026-10-30',
        ]);
        $claim = ItemClaim::create([
            'lost_found_item_id' => $item->id,
            'user_id' => $owner->id,
            'proof_description' => 'Deskripsi bukti awal milik pengguna.',
            'status' => 'pending',
        ]);
        $this->actingAs($admin)
            ->post(route('admin.claims.evidence-request', $claim), [
                'note' => 'Berikan keterangan tambahan tentang isi tas.',
            ])
            ->assertSessionHasNoErrors();
        $request = $claim->evidenceRequests()->firstOrFail();
        $url = route('user.claims.evidence-response', $claim);
        $payload = [
            'request_id' => $request->id,
            'response_note' => 'Di dalam tas terdapat buku kuliah dan kartu.',
            'confirm_truth' => '1',
        ];
        $this->actingAs($other)->post($url, $payload)->assertNotFound();
        $this->assertNull($request->fresh()->closed_at);
        $this->actingAs($owner)->post($url, $payload)->assertSessionHasNoErrors();
        $this->assertSame('resubmitted', $request->fresh()->closure_type);
        $this->assertNotNull($request->fresh()->closed_at);
        $this->assertStringContainsString(
            'Deskripsi bukti awal',
            $claim->fresh()->proof_description,
        );
        $this->assertStringContainsString(
            'Di dalam tas terdapat buku',
            $claim->fresh()->proof_description,
        );
        $this->post($url, $payload)->assertSessionHasErrors('response_note');
        $this->assertSame(1, $claim->evidenceRequests()->count());
        $this->actingAs($admin)
            ->post(route('admin.claims.review', $claim), [
                'decision' => 'approved',
                'evidence_reviewed' => '1',
            ])
            ->assertSessionHasNoErrors();
        $this->assertSame('approved', $claim->fresh()->status);
        $this->assertSame('verified', $item->fresh()->status);
    }
}
