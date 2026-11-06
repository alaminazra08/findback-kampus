<?php

namespace Tests\Feature;

use App\Models\{ItemClaim, LostFoundItem, User};
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class PremiumUserTest extends TestCase
{
    use DatabaseMigrations;

    public function test_user_pages_render_with_live_data(): void
    {
        $user = User::factory()->create(['name' => 'Amin', 'role' => 'user']);
        $this->actingAs($user);
        $empty = $this->get('/dashboard')->assertOk()->assertSee('Belum ada laporan');

        $item = LostFoundItem::create([
            'user_id' => $user->id,
            'title' => 'Dompet kulit cokelat',
            'type' => 'found',
            'status' => 'verified',
            'location' => 'Perpustakaan FST',
            'event_date' => '2026-10-30',
        ]);
        ItemClaim::create([
            'lost_found_item_id' => $item->id,
            'user_id' => $user->id,
            'proof_description' => 'Ciri pribadi barang',
            'status' => 'approved',
        ]);
        $routes = [
            'dashboard' => '/dashboard',
            'items' => '/cari-barang',
            'detail' => '/cari-barang/' . $item->id,
            'reports' => '/laporan-saya',
            'lost' => '/laporkan-barang-hilang',
            'found' => '/laporkan-barang-ditemukan',
            'claims' => '/klaim-saya',
        ];
        foreach ($routes as $key => $route) {
            $res = $this->get($route)
                ->assertOk()
                ->assertSee('campus.css')
                ->assertSee('id="main"', false);
        }
        $this->get('/dashboard')
            ->assertSee('Dompet kulit cokelat')
            ->assertSee('1 klaim telah disetujui');
    }

    public function test_claim_list_requires_user_session(): void
    {
        $this->get('/klaim-saya')->assertRedirect(route('login'));
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get('/klaim-saya')->assertForbidden();
    }
}
