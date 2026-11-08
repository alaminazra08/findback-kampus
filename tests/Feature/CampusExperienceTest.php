<?php

namespace Tests\Feature;

use App\Models\{ItemClaim, LostFoundItem, User};
use App\Notifications\AdminMessageNotification;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{Hash, Storage};
use Tests\TestCase;

class CampusExperienceTest extends TestCase
{
    use DatabaseMigrations;

    private function user(): User
    {
        $u = User::factory()->create();
        $u->forceFill(['role' => 'user'])->save();
        return $u;
    }

    private function item(
        User $u,
        string $type = 'found',
        string $status = 'verified',
    ): LostFoundItem {
        return LostFoundItem::create([
            'user_id' => $u->id,
            'title' => 'Dompet cokelat',
            'type' => $type,
            'status' => $status,
            'location' => 'Perpustakaan FST',
            'category' => 'Dompet',
            'event_date' => '2026-10-20',
            'description' => 'Dompet dengan jahitan khusus',
        ]);
    }

    public function test_user_pages_and_context_access(): void
    {
        $u = $this->user();
        $owner = $this->user();
        $item = $this->item($owner);
        $this->actingAs($u);
        foreach (
            [
                '/dashboard',
                '/cari-barang',
                '/cari-barang/' . $item->id,
                '/cari-barang/' . $item->id . '/klaim',
                '/laporkan-barang-hilang',
                '/laporkan-barang-ditemukan',
                '/laporan-saya',
                '/klaim-saya',
                '/notifikasi',
                '/profil',
                '/chat',
            ] as $url
        ) {
            $this->get($url)->assertOk()->assertSee('campus.css');
        }
        $this->get('/laporan-saya/' . $item->id)->assertNotFound();
        $this->actingAs($owner)
            ->get('/laporan-saya/' . $item->id)
            ->assertOk()
            ->assertSee('Dompet cokelat');
        $item->update(['status' => 'pending']);
        $this->get('/cari-barang/' . $item->id)->assertNotFound();
    }

    public function test_published_search_filters(): void
    {
        $u = $this->user();
        $item = $this->item($u);
        $this->actingAs($u);
        $this->get('/cari-barang?location=Perpustakaan&date=2026-10-20&type=found')
            ->assertOk()
            ->assertSee('Dompet cokelat');
        $this->get('/cari-barang?location=Lapangan')->assertOk()->assertDontSee('Dompet cokelat');
        $this->get('/cari-barang?date=2026-10-21')->assertOk()->assertDontSee('Dompet cokelat');
        $item->update(['status' => 'pending']);
        $this->get('/cari-barang')->assertDontSee('Dompet cokelat');
        $this->get('/cari-barang?date=invalid')->assertSessionHasErrors('date');
    }

    public function test_claim_submission_duplicate_and_chat_gating(): void
    {
        $u = $this->user();
        $owner = $this->user();
        $item = $this->item($owner);
        $this->actingAs($u);
        $this->get('/chat')->assertSee('Chat tersedia untuk laporan atau klaim');
        $this->post('/chat/send', ['message' => 'Hello'])->assertForbidden();
        $this->getJson('/chat/messages')->assertForbidden();
        $this->post('/cari-barang/' . $item->id . '/klaim', [
            'proof_description' => 'Ada tanda nama di bagian dalam.',
        ])->assertRedirect(route('user.claims.index'));
        $claim = ItemClaim::first();
        $this->assertEquals($u->id, $claim->user_id);
        $this->assertSame('pending', $claim->status);
        $this->post('/cari-barang/' . $item->id . '/klaim', [
            'proof_description' => 'Duplikat kepemilikan barang',
        ])->assertSessionHasErrors('proof_description');
        $this->assertDatabaseCount('item_claims', 1);
        $this->get('/chat')->assertSee('Klaim yang sedang berlangsung');
        $this->post('/chat/send', ['message' => 'Tentang klaim saya'])->assertRedirect();
        $this->assertDatabaseHas('chat_messages', [
            'user_id' => $u->id,
            'message' => 'Tentang klaim saya',
        ]);
        $this->actingAs($owner)
            ->getJson('/chat/messages')
            ->assertOk()
            ->assertJsonCount(0, 'messages');
        $claim->update(['status' => 'approved']);
        $this->assertSame(
            1,
            $u
                ->notifications()
                ->where('type', \App\Notifications\UserClaimStatusNotification::class)
                ->count(),
        );
        $this->actingAs($u);
        $claim->update(['status' => 'returned']);
        $this->post('/chat/send', ['message' => 'Selesai'])->assertRedirect();
    }

    public function test_only_eligible_found_items_can_be_claimed(): void
    {
        $u = $this->user();
        $owner = $this->user();
        $this->actingAs($u);
        foreach (
            [['lost', 'verified'], ['found', 'pending'], ['found', 'returned']] as [$type, $status]
        ) {
            $i = $this->item($owner, $type, $status);
            $this->post('/cari-barang/' . $i->id . '/klaim', [
                'proof_description' => 'Ciri kepemilikan saya',
            ])->assertNotFound();
        }
        $own = $this->item($u);
        $this->get('/cari-barang/' . $own->id . '/klaim')->assertNotFound();
    }

    public function test_reports_require_photo_and_remain_pending(): void
    {
        Storage::fake('public');
        $this->actingAs($this->user());
        $data = [
            'title' => 'Kunci motor',
            'category' => 'Kunci',
            'location' => 'FST Lantai 1',
            'description' => 'Kunci dengan gantungan biru',
            'event_date' => '2026-10-20',
            'confirmation' => '1',
        ];
        $this->post('/laporkan-barang-hilang', $data)->assertSessionHasErrors('photo');
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII=',
        );
        foreach (['/laporkan-barang-hilang', '/laporkan-barang-ditemukan'] as $url) {
            $data['photo'] = UploadedFile::fake()->createWithContent('barang.png', $png);
            $this->post($url, $data)
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('user.reports.index'));
        }
        $this->assertDatabaseCount('lost_found_items', 2);
        $this->assertEquals(2, LostFoundItem::where('status', 'pending')->count());
    }

    public function test_notifications_profile_and_password_are_scoped(): void
    {
        $u = $this->user();
        $other = $this->user();
        $u->notify(new AdminMessageNotification('Pesan pribadi', 'Milik user ini'));
        $other->notify(new AdminMessageNotification('RAHASIA LAIN', 'Tidak boleh terlihat'));
        $foreign = $other->notifications()->first();
        $this->actingAs($u)
            ->get('/notifikasi')
            ->assertOk()
            ->assertSee('Pesan pribadi')
            ->assertDontSee('RAHASIA LAIN');
        $this->post('/notifikasi/' . $foreign->id . '/read')->assertNotFound();
        $this->post('/notifikasi/read-all')->assertRedirect();
        $this->assertEquals(0, $u->unreadNotifications()->count());
        $this->post('/profil', ['name' => 'Nama Baru', 'role' => 'admin'])->assertRedirect();
        $this->assertSame('user', $u->fresh()->role);
        $this->post('/profil/password', [
            'current_password' => 'wrong',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertSessionHasErrors('current_password');
        $this->post('/profil/password', [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('new-password', $u->fresh()->password));
    }
}
