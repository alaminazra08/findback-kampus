<?php

namespace Tests\Feature;

use App\Models\{LostFoundItem, ReportAuditEvent, User};
use App\Services\ReportSubmissionService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RefactorContractTest extends TestCase
{
    use DatabaseMigrations;

    private function photo(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            'barang.png',
            base64_decode(
                'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aF1sAAAAASUVORK5CYII=',
            ),
        );
    }

    public function test_shared_submission_keeps_both_report_contracts(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $user->forceFill(['role' => 'user'])->save();
        foreach (['lost', 'found'] as $type) {
            $this->actingAs($user)
                ->post(route('user.reports.' . $type . '.store'), [
                    'title' => 'Dompet ' . $type,
                    'category' => 'Kategori historis',
                    'location' => 'FST',
                    'event_date' => now()->toDateString(),
                    'description' => 'Ciri khusus pada bagian dalam dompet.',
                    'confirmation' => 1,
                    'photo' => $this->photo(),
                ])
                ->assertRedirect(route('user.reports.index'))
                ->assertSessionHasNoErrors();
            $item = LostFoundItem::where('title', 'Dompet ' . $type)->firstOrFail();
            $this->assertSame($type, $item->type);
            $this->assertSame('pending', $item->status);
            $this->assertSame($user->id, $item->user_id);
            $this->assertSame('Kategori historis', $item->category);
            $this->assertStringStartsWith('reports/' . $type . '/', $item->photo_path);
            Storage::disk('public')->assertExists($item->photo_path);
            $this->assertSame(
                1,
                ReportAuditEvent::where('lost_found_item_id', $item->id)
                    ->where('event', 'created')
                    ->count(),
            );
        }
    }

    public function test_submission_rolls_back_record_and_upload_when_audit_fails(): void
    {
        Storage::fake('public');
        $u = User::factory()->create();
        ReportAuditEvent::creating(function () {
            throw new \RuntimeException('Simulasi kegagalan audit');
        });
        try {
            app(ReportSubmissionService::class)->submit(
                $u,
                'lost',
                [
                    'title' => 'Dompet',
                    'location' => 'FST',
                    'event_date' => now()->toDateString(),
                    'description' => 'Ciri unik',
                ],
                $this->photo(),
            );
            $this->fail('Expected failure');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Simulasi kegagalan audit', $exception->getMessage());
        } finally {
            ReportAuditEvent::flushEventListeners();
        }
        $this->assertDatabaseCount('lost_found_items', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_active_views_have_no_embedded_executable_assets(): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(resource_path('views')),
        );
        $count = 0;
        foreach ($files as $file) {
            if (!$file->isFile() || !str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }
            $source = file_get_contents($file->getPathname());
            $this->assertDoesNotMatchRegularExpression(
                "/<style\b|\sstyle\s*=|\son(?:click|submit|change|error)\s*=/i",
                $source,
                $file->getPathname(),
            );
            preg_match_all("/<script\b([^>]*)>/i", $source, $tags);
            foreach ($tags[1] as $tag) {
                $this->assertTrue(
                    str_contains($tag, 'src=') || str_contains($tag, 'application/json'),
                    $file->getPathname(),
                );
            }
            $count++;
        }
        $this->assertGreaterThan(40, $count);
    }
}
