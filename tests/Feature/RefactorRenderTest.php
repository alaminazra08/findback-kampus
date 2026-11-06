<?php

namespace Tests\Feature;

use App\Models\{ChatMessage, ItemClaim, ItemMatch, LostFoundItem, User};
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class RefactorRenderTest extends TestCase
{
    use DatabaseMigrations;

    public function test_pages_keep_rendering_and_export_contracts(): void
    {
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-11-03 09:00:00'));
        fake()->seed(42);
        \Illuminate\Support\Facades\URL::forceRootUrl('http://127.0.0.1:8766');
        $folder = getenv('REFACTOR_RENDER_DIR') ?: storage_path('framework/testing/refactor');
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }
        foreach (['home', 'login', 'register', 'password.request'] as $route) {
            $r = $this->get(route($route));
            $r->assertOk();
            file_put_contents($folder . '/' . $route . '.html', $r->getContent());
        }
        $a = User::factory()->create([
            'name' => 'Admin Pengujian',
            'email' => 'admin@example.test',
        ]);
        $a->forceFill(['role' => 'admin'])->save();
        $u = User::factory()->create([
            'name' => 'Pelapor Pengujian',
            'email' => 'pelapor@example.test',
        ]);
        $u->forceFill(['role' => 'user'])->save();
        $this->actingAs($a);
        $pages = [
            'dashboard' => 'admin.dashboard',
            'verification' => 'admin.reports.verification',
            'items' => 'admin.items.index',
            'matching' => 'admin.matching.index',
            'claims' => 'admin.claims.index',
            'statistics' => 'admin.statistics.index',
            'users' => 'admin.users.index',
            'settings' => 'admin.settings.index',
            'chat' => 'admin.chat.index',
        ];
        foreach ($pages as $name => $route) {
            $r = $this->get(route($route));
            $r->assertOk();
            file_put_contents($folder . '/' . $name . '-empty.html', $r->getContent());
        }
        $items = [];
        foreach (['verified', 'pending', 'rejected', 'returned'] as $status) {
            $i = LostFoundItem::create([
                'user_id' => $u->id,
                'title' => 'Dompet kulit dengan ciri khusus pada jahitan bagian dalam',
                'description' => str_repeat('Deskripsi barang untuk pengujian tampilan. ', 3),
                'type' => 'found',
                'status' => $status,
                'location' => 'Laboratorium Fakultas Sains dan Teknologi',
                'event_date' => '2026-11-02',
                'photo_path' => 'reports/missing.png',
            ]);
            if ($status === 'rejected') {
                $i->forceFill(['rejection_reason' => 'Ciri barang perlu diperjelas.'])->save();
            }
            $items[$status] = $i;
        }
        $lost = LostFoundItem::create([
            'user_id' => $a->id,
            'title' => 'Dompet kulit hilang',
            'type' => 'lost',
            'status' => 'verified',
            'location' => 'FST',
            'event_date' => '2026-11-02',
            'photo_path' => 'reports/missing.png',
        ]);
        $match = ItemMatch::create([
            'lost_item_id' => $lost->id,
            'found_item_id' => $items['verified']->id,
            'match_score' => 80,
            'match_reason' => 'Nama dan lokasi serupa',
            'status' => 'pending',
        ]);
        $claims = [];
        foreach (['pending', 'approved', 'returned', 'rejected'] as $status) {
            $claims[$status] = ItemClaim::create([
                'user_id' => $u->id,
                'lost_found_item_id' =>
                    $items[$status === 'returned' ? 'returned' : 'verified']->id,
                'status' => $status,
                'proof_description' => str_repeat('Ciri kepemilikan yang dijelaskan pengaju. ', 3),
            ]);
        }
        ChatMessage::create([
            'user_id' => $u->id,
            'sender_id' => $u->id,
            'claim_id' => $claims['approved']->id,
            'message' => 'Pesan koordinasi serah terima.',
        ]);
        ChatMessage::create([
            'user_id' => $u->id,
            'sender_id' => $u->id,
            'message' => 'Arsip percakapan lama.',
        ]);
        foreach ($pages as $name => $route) {
            $r = $this->get(route($route));
            $r->assertOk();
            file_put_contents($folder . '/' . $name . '.html', $r->getContent());
        }
        $details = [
            'verification-detail' => ['admin.reports.verification.show', $items['pending']],
            'rejected' => ['admin.reports.verification', ['status' => 'rejected']],
            'item-detail' => ['admin.items.show', $items['verified']],
            'match-detail' => ['admin.matching.show', $match],
            'user-detail' => ['admin.users.show', $u],
            'chat-detail' => [
                'admin.chat.show',
                ['user' => $u->id, 'claim_id' => $claims['approved']->id],
            ],
            'reports-lost' => ['admin.reports.lost', []],
            'reports-found' => ['admin.reports.found', []],
            'print' => ['admin.statistics.print', []],
        ];
        foreach ($claims as $status => $c) {
            $details['claim-' . $status] = ['admin.claims.show', $c];
        }
        foreach ($details as $name => [$route, $param]) {
            $r = $this->get(route($route, $param));
            $r->assertOk();
            file_put_contents($folder . '/' . $name . '.html', $r->getContent());
        }
        foreach (['admin.statistics.export.pdf', 'admin.statistics.export.board'] as $route) {
            $r = $this->get(route($route));
            $r->assertOk();
            $this->assertStringStartsWith('%PDF-', $r->getContent());
            file_put_contents($folder . '/' . $route . '.pdf', $r->getContent());
        }
        $this->actingAs($u);
        foreach (
            [
                'user-dashboard' => route('dashboard'),
                'user-items' => route('user.items.index'),
                'user-item' => route('user.items.show', $items['verified']),
                'user-claim-create' => route('user.claims.create', $items['verified']),
                'user-chat' => route('user.chat.index', ['claim_id' => $claims['approved']->id]),
                'user-report' => route('user.reports.show', $items['rejected']),
                'user-reports' => route('user.reports.index'),
                'user-claims' => route('user.claims.index'),
                'user-profile' => route('user.profile.index'),
                'user-notifications' => route('user.notifications.index'),
                'user-lost' => route('user.reports.lost.create'),
                'user-found' => route('user.reports.found.create'),
            ] as $name => $url
        ) {
            if ($name === 'user-claim-create') {
                continue;
            } // Existing owner/duplicate claim redirects are intentional.
            $r = $this->get($url);
            $r->assertOk();
            file_put_contents($folder . '/' . $name . '.html', $r->getContent());
        }
        $this->get(route('user.reports.index'))->assertDontSee('@include(');
        $this->get(route('user.reports.show', $items['rejected']))->assertDontSee('@include(');
        $this->get(route('admin.dashboard'))->assertForbidden();
    }
}
