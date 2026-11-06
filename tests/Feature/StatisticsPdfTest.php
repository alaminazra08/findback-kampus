<?php

namespace Tests\Feature;

use App\Models\ItemClaim;
use App\Models\LostFoundItem;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class StatisticsPdfTest extends TestCase
{
    use DatabaseMigrations;

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        return $admin;
    }

    public function test_pdf_routes_require_admin(): void
    {
        foreach (['admin.statistics.export.pdf', 'admin.statistics.export.board'] as $route) {
            $this->get(route($route))->assertRedirect(route('login'));
        }
        $user = User::factory()->create();
        $user->forceFill(['role' => 'user'])->save();
        foreach (['admin.statistics.export.pdf', 'admin.statistics.export.board'] as $route) {
            $this->actingAs($user)->get(route($route))->assertForbidden();
        }
    }

    public function test_real_pdf_downloads_and_four_items_per_tv_page(): void
    {
        $this->actingAs($this->admin());
        foreach (
            [
                'Dompet kulit cokelat',
                'Kunci motor dengan gantungan biru',
                'Botol minum stainless hitam',
                'Tas ransel berwarna merah marun berukuran sedang',
                'Pengisi daya laptop berwarna hitam',
            ] as $i => $name
        ) {
            LostFoundItem::create([
                'title' => $name,
                'type' => $i % 2 ? 'lost' : 'found',
                'status' => 'verified',
                'location' => 'Gedung Perkuliahan FST Lantai 2, dekat ruang laboratorium',
                'event_date' => now()->toDateString(),
            ]);
        }
        $hidden = LostFoundItem::create([
            'title' => 'Barang belum disetujui',
            'type' => 'found',
            'status' => 'pending',
        ]);
        $reserved = LostFoundItem::create([
            'title' => 'Barang sudah diklaim',
            'type' => 'found',
            'status' => 'verified',
        ]);
        ItemClaim::create([
            'lost_found_item_id' => $reserved->id,
            'user_id' => auth()->id(),
            'proof_description' => 'Bukti kepemilikan',
            'status' => 'approved',
        ]);
        $board = $this->get(route('admin.statistics.export.board', ['period' => 'all']));
        $board->assertOk()->assertHeader('content-type', 'application/pdf')->assertDownload();
        $binary = $board->getContent();
        if ($folder = getenv('ADMIN_PDF_QA_DIR')) {
            if (!is_dir($folder)) {
                mkdir($folder, 0777, true);
            }
            file_put_contents($folder . '/tv.pdf', $binary);
        }
        $this->assertStringStartsWith('%PDF-', $binary);
        preg_match_all("/\/Type\s*\/Page\b/", $binary, $matches);
        $this->assertCount(2, $matches[0]);
        $admin = $this->get(route('admin.statistics.export.pdf', ['period' => 'all']));
        $admin->assertOk()->assertHeader('content-type', 'application/pdf')->assertDownload();
        $this->assertStringStartsWith('%PDF-', $admin->getContent());
        if ($folder = getenv('ADMIN_PDF_QA_DIR')) {
            if (!is_dir($folder)) {
                mkdir($folder, 0777, true);
            }
            file_put_contents($folder . '/tv.pdf', $binary);
            file_put_contents($folder . '/admin.pdf', $admin->getContent());
        }
    }

    public function test_board_view_filters_nonpublic_and_reserved_items_and_local_paths(): void
    {
        $this->actingAs($this->admin());
        $visible = LostFoundItem::create([
            'title' => 'Barang layak tayang',
            'type' => 'found',
            'status' => 'verified',
            'photo_path' => '../../.env',
        ]);
        LostFoundItem::create([
            'title' => 'Barang ditolak',
            'type' => 'found',
            'status' => 'rejected',
        ]);
        LostFoundItem::create([
            'title' => 'Barang menunggu',
            'type' => 'found',
            'status' => 'pending',
        ]);
        $reserved = LostFoundItem::create([
            'title' => 'Barang dipesan',
            'type' => 'found',
            'status' => 'verified',
        ]);
        ItemClaim::create([
            'lost_found_item_id' => $reserved->id,
            'user_id' => auth()->id(),
            'proof_description' => 'Bukti',
            'status' => 'approved',
        ]);
        $renderedItems = null;
        \Illuminate\Support\Facades\View::composer('admin.statistics.board', function ($view) use (
            &$renderedItems,
        ) {
            $renderedItems = $view->getData()['pages']->flatten(1);
        });
        $this->get(route('admin.statistics.export.board'))->assertOk();
        $this->assertCount(1, $renderedItems);
        $this->assertEquals($visible->id, $renderedItems->first()->id);
        $this->assertNull($renderedItems->first()->photoData);
    }

    public function test_local_jpeg_is_embedded_in_board_pdf(): void
    {
        $dir = storage_path('app/public/pdf-test');
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $path = $dir . '/sample.jpg';
        copy(public_path('images/login-fst-premium.jpg'), $path);
        try {
            LostFoundItem::create([
                'title' => 'Foto barang',
                'type' => 'found',
                'status' => 'verified',
                'photo_path' => 'pdf-test/sample.jpg',
            ]);
            $pdf = $this->actingAs($this->admin())->get(route('admin.statistics.export.board'));
            $pdf->assertOk()->assertDownload();
            $this->assertMatchesRegularExpression("/\/Subtype\s*\/Image/", $pdf->getContent());
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
            @rmdir($dir);
        }
    }

    public function test_empty_board_pdf_and_invalid_period(): void
    {
        $this->actingAs($this->admin());
        foreach (['admin.statistics.export.pdf', 'admin.statistics.export.board'] as $route) {
            $this->get(route($route))->assertOk()->assertDownload();
            $this->getJson(route($route, ['period' => 'oops']))->assertUnprocessable();
        }
    }
}
