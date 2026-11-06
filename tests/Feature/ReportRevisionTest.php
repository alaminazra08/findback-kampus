<?php

namespace Tests\Feature;

use App\Models\{LostFoundItem, User};
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ReportRevisionTest extends TestCase
{
    use DatabaseMigrations;

    private function setupItem(): array
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $user = User::factory()->create();
        $item = LostFoundItem::create([
            'user_id' => $user->id,
            'title' => 'Tas uji',
            'type' => 'lost',
            'status' => 'pending',
            'location' => 'FST',
            'event_date' => '2026-10-30',
        ]);
        return [$admin, $user, $item];
    }

    public function test_request_blocks_decision_and_preserves_history_after_cancellation(): void
    {
        [$admin, $user, $item] = $this->setupItem();
        $this->actingAs($admin);
        $url = route('admin.reports.verification.revision', $item);
        $this->post($url, [
            'note' => 'Lengkapi lokasi kehilangan barang.',
        ])->assertSessionHasNoErrors();
        $this->assertSame(1, $item->revisions()->count());
        $this->assertSame(1, $user->notifications()->count());
        $this->post($url, ['note' => 'Permintaan duplikat tidak boleh.'])->assertSessionHasErrors(
            'note',
        );
        $this->post(route('admin.reports.verification.review', $item), [
            'decision' => 'verified',
        ])->assertSessionHas('warning');
        $this->assertSame('pending', $item->fresh()->status);
        $this->get(route('admin.reports.verification.show', $item))
            ->assertOk()
            ->assertSee('Menunggu Perbaikan')
            ->assertSee('Lengkapi lokasi');
        $this->get(route('admin.reports.verification', ['revision' => 'open']))
            ->assertOk()
            ->assertSee('Tas uji');
        $cancel = route('admin.reports.verification.revision.cancel', $item);
        $this->post($cancel, [
            'revision_id' => $item->revisions()->first()->id,
            'closure_note' => 'Permintaan keliru, lokasi sudah jelas.',
        ])->assertSessionHasNoErrors();
        $this->assertNotNull($item->revisions()->first()->closed_at);
        $this->post($cancel, [
            'revision_id' => $item->revisions()->first()->id,
            'closure_note' => 'Pembatalan ulang tidak diperbolehkan.',
        ])->assertSessionHasErrors();
        $this->post(route('admin.reports.verification.review', $item), [
            'decision' => 'verified',
        ])->assertSessionHasNoErrors();
        $this->assertSame('verified', $item->fresh()->status);
        $this->post($url, [
            'note' => 'Tidak boleh mengubah laporan terverifikasi.',
        ])->assertSessionHasErrors('note');
    }

    public function test_access_validation_and_scoping(): void
    {
        [$admin, $user, $item] = $this->setupItem();
        $url = route('admin.reports.verification.revision', $item);
        $this->post($url)->assertRedirect(route('login'));
        $this->actingAs($user)
            ->post($url, ['note' => 'Akses user tidak diperbolehkan.'])
            ->assertForbidden();
        $this->actingAs($admin)
            ->post($url, ['note' => '  '])
            ->assertSessionHasErrors('note');
        $this->post($url, ['note' => str_repeat('x', 2001)])->assertSessionHasErrors('note');
        $this->post($url, [
            'note' => 'Lengkapi detail barang terlebih dahulu.',
        ])->assertSessionHasNoErrors();
        [, $other, $another] = $this->setupItem();
        $this->post(route('admin.reports.verification.revision.cancel', $another), [
            'revision_id' => $item->revisions()->first()->id,
            'closure_note' => 'Tidak boleh menutup permintaan barang lain.',
        ])->assertSessionHasErrors();
        $this->assertNull($item->revisions()->first()->closed_at);
    }
}
