<?php

namespace Tests\Feature;

use App\Models\LostFoundItem;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class StatisticsExportTest extends TestCase
{
    use DatabaseMigrations;

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => 'admin'])->save();
        return $user;
    }

    private function item(string $title, $date = null): void
    {
        $item = LostFoundItem::create([
            'title' => $title,
            'type' => 'lost',
            'status' => 'pending',
            'description' => 'Deskripsi; dengan "kutip"',
            'location' => "\t@SUM(1)",
        ]);
        if ($date) {
            $item->forceFill(['created_at' => $date, 'updated_at' => $date])->save();
        }
    }

    public function test_exports_require_admin(): void
    {
        foreach (['admin.statistics.export.csv', 'admin.statistics.print'] as $route) {
            $this->get(route($route))->assertRedirect(route('login'));
        }
        $user = User::factory()->create();
        $user->forceFill(['role' => 'user'])->save();
        foreach (['admin.statistics.export.csv', 'admin.statistics.print'] as $route) {
            $this->actingAs($user)->get(route($route))->assertForbidden();
        }
    }

    public function test_csv_includes_more_than_eight_rows_and_respects_period(): void
    {
        for ($i = 1; $i <= 12; $i++) {
            $this->item('Barang-' . $i);
        }
        $this->item('Barang lama', now()->subDays(40));
        $response = $this->actingAs($this->admin())->get(
            route('admin.statistics.export.csv', ['period' => '7']),
        );
        $response->assertOk()->assertDownload();
        $csv = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('Barang-12', $csv);
        $this->assertStringNotContainsString('Barang lama', $csv);
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, substr($csv, 3));
        rewind($stream);
        fgets($stream);
        $rows = [];
        while (($row = fgetcsv($stream, 0, ';', '"', '')) !== false) {
            $rows[] = $row;
        }
        fclose($stream);
        $this->assertCount(13, $rows);
        $this->assertCount(9, $rows[0]);
        $this->assertSame('Deskripsi; dengan "kutip"', $rows[1][8]);
        $this->assertStringContainsString(
            'Barang lama',
            $this->get(
                route('admin.statistics.export.csv', ['period' => 'all']),
            )->streamedContent(),
        );
    }

    public function test_formula_prefix_is_literal(): void
    {
        $this->item('=1+1');
        $csv = $this->actingAs($this->admin())
            ->get(route('admin.statistics.export.csv'))
            ->streamedContent();
        $this->assertStringContainsString("'=1+1", $csv);
        $this->assertStringContainsString("'\t@SUM(1)", $csv);
    }

    public function test_print_all_filtered_rows_and_escape_html(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $this->item('Cetak-' . $i);
        }
        $this->item('Laporan lama', now()->subDays(40));
        $this->item('<script>alert(1)</script>');
        $this->actingAs($this->admin())
            ->get(route('admin.statistics.print', ['period' => '7']))
            ->assertOk()
            ->assertSee('Cetak-10')
            ->assertDontSee('Laporan lama')
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('Save as PDF');
    }

    public function test_empty_exports_and_invalid_period(): void
    {
        $this->actingAs($this->admin());
        $csv = $this->get(route('admin.statistics.export.csv'))->assertOk()->streamedContent();
        $this->assertStringContainsString('Nama Barang', $csv);
        $this->get(route('admin.statistics.print'))
            ->assertOk()
            ->assertSee('Belum ada laporan pada periode ini.');
        foreach (['admin.statistics.export.csv', 'admin.statistics.print'] as $route) {
            $this->getJson(route($route, ['period' => 'invalid']))->assertUnprocessable();
        }
    }
}
