<?php

namespace Tests\Feature;

use App\Models\ItemClaim;
use App\Models\LostFoundItem;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ClaimHandoverTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function account(string $role = 'user'): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => $role])->save();
        return $user;
    }

    private function claim(string $status = 'approved'): ItemClaim
    {
        $item = LostFoundItem::create([
            'title' => 'Dompet kulit cokelat',
            'type' => 'found',
            'status' => 'verified',
            'location' => 'Gedung A',
        ]);
        return ItemClaim::create([
            'lost_found_item_id' => $item->id,
            'user_id' => $this->account()->id,
            'proof_description' => 'Ada kartu kampus milik pengaju di dalam dompet.',
            'status' => $status,
        ]);
    }

    private function data(array $overrides = []): array
    {
        return array_replace(
            [
                'recipient_name' => 'Mahasiswa Penerima',
                'recipient_identifier' => '24100001',
                'handover_location' => 'Ruang Administrasi FST',
                'handover_notes' => 'Barang diterima dalam kondisi baik.',
                'handover_confirmed' => '1',
                'handover_evidence' => UploadedFile::fake()->createWithContent(
                    'bukti.pdf',
                    "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF",
                ),
            ],
            $overrides,
        );
    }

    public function test_handover_requires_admin_and_rejects_guest(): void
    {
        $claim = $this->claim();
        $this->post(route('admin.claims.return', $claim), $this->data())->assertRedirect(
            route('login'),
        );
        $this->actingAs($this->account())
            ->post(route('admin.claims.return', $claim), $this->data())
            ->assertForbidden();
        foreach (['admin.claims.handover.pdf', 'admin.claims.handover.evidence'] as $route) {
            $this->get(route($route, $claim))->assertForbidden();
        }
    }

    public function test_complete_handover_records_snapshots_closes_pending_and_downloads_pdf(): void
    {
        $admin = $this->account('admin');
        $claim = $this->claim();
        $loser = ItemClaim::create([
            'lost_found_item_id' => $claim->lost_found_item_id,
            'user_id' => $this->account()->id,
            'proof_description' => 'Bukti pengaju lain',
            'status' => 'pending',
        ]);
        $before = now()->subSeconds(2);
        $this->actingAs($admin)
            ->post(route('admin.claims.return', $claim), $this->data())
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.claims.show', $claim));
        $claim->refresh();
        $this->assertSame('returned', $claim->status);
        $this->assertSame('returned', $claim->item->status);
        $this->assertSame('Mahasiswa Penerima', $claim->recipient_name);
        $this->assertEquals($admin->id, $claim->returned_by);
        $this->assertSame($admin->name, $claim->handover_officer_name);
        $this->assertTrue($claim->returned_at->between($before, now()->addSeconds(2)));
        $this->assertTrue($claim->hasHandoverReceipt());
        Storage::disk('local')->assertExists($claim->handover_evidence_path);
        $this->assertStringStartsWith('handovers/', $claim->handover_evidence_path);
        $this->assertSame('rejected', $loser->fresh()->status);
        $this->assertStringContainsString('Ditutup otomatis', $loser->fresh()->rejection_reason);
        $this->assertSame(1, $claim->claimant->notifications()->count());
        $this->assertSame(1, $loser->claimant->notifications()->count());
        $this->get(route('admin.claims.show', $claim))
            ->assertOk()
            ->assertSee($claim->handover_code)
            ->assertSee('Unduh Tanda Terima PDF');
        $pdf = $this->get(route('admin.claims.handover.pdf', $claim))
            ->assertOk()
            ->assertDownload()
            ->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $pdf->getContent());
        $this->get(route('admin.claims.handover.evidence', $claim))->assertOk()->assertDownload();
        $claim->item->update(['title' => 'Nama diubah setelah selesai']);
        $admin->update(['name' => 'Nama admin berubah']);
        $this->assertSame('Dompet kulit cokelat', $claim->fresh()->handover_item_title);
        $this->assertNotSame('Nama admin berubah', $claim->fresh()->handover_officer_name);
        if ($dir = getenv('HANDOVER_QA_DIR')) {
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            file_put_contents($dir . '/tanda-terima.pdf', $pdf->getContent());
            file_put_contents(
                $dir . '/detail.html',
                $this->get(route('admin.claims.show', $claim))->getContent(),
            );
        }
    }

    public function test_required_fields_and_file_validation_do_not_change_status(): void
    {
        $claim = $this->claim();
        $this->actingAs($this->account('admin'));
        $this->post(route('admin.claims.return', $claim), [])->assertSessionHasErrors([
            'recipient_name',
            'handover_location',
            'handover_evidence',
            'handover_confirmed',
        ]);
        $this->post(
            route('admin.claims.return', $claim),
            $this->data([
                'handover_evidence' => UploadedFile::fake()->create(
                    'program.exe',
                    20,
                    'application/octet-stream',
                ),
            ]),
        )->assertSessionHasErrors('handover_evidence');
        $this->post(
            route('admin.claims.return', $claim),
            $this->data([
                'handover_evidence' => UploadedFile::fake()->create(
                    'large.pdf',
                    5121,
                    'application/pdf',
                ),
            ]),
        )->assertSessionHasErrors('handover_evidence');
        $this->assertSame('approved', $claim->fresh()->status);
        $this->assertCount(0, Storage::disk('local')->allFiles('handovers'));
    }

    public function test_only_approved_claim_can_be_returned_and_return_cannot_repeat(): void
    {
        $claim = $this->claim('pending');
        $this->actingAs($this->account('admin'));
        $this->post(route('admin.claims.return', $claim), $this->data())->assertSessionHasErrors(
            'return',
        );
        $claim->update(['status' => 'approved']);
        $this->post(
            route('admin.claims.return', $claim),
            $this->data(),
        )->assertSessionHasNoErrors();
        $code = $claim->fresh()->handover_code;
        $this->post(route('admin.claims.return', $claim), $this->data())->assertSessionHasErrors(
            'return',
        );
        $this->assertSame($code, $claim->fresh()->handover_code);
        $this->assertCount(1, Storage::disk('local')->allFiles('handovers'));
    }

    public function test_failed_transaction_cleans_up_uploaded_file(): void
    {
        $claim = $this->claim();
        $claim->item->update(['status' => 'returned']);
        $this->actingAs($this->account('admin'))
            ->post(route('admin.claims.return', $claim), $this->data())
            ->assertSessionHasErrors('return');
        $this->assertCount(0, Storage::disk('local')->allFiles('handovers'));
        $this->assertSame('approved', $claim->fresh()->status);
    }

    public function test_legacy_returns_have_no_fabricated_receipt_and_missing_files_return_404(): void
    {
        $claim = $this->claim('returned');
        $claim->update(['returned_at' => now()]);
        $this->actingAs($this->account('admin'))
            ->get(route('admin.claims.show', $claim))
            ->assertOk()
            ->assertSee('Pengembalian lama');
        $this->get(route('admin.claims.handover.pdf', $claim))->assertNotFound();
        $this->get(route('admin.claims.handover.evidence', $claim))->assertNotFound();
        $claim->forceFill(['handover_evidence_path' => '../.env'])->save();
        $this->get(route('admin.claims.handover.evidence', $claim))->assertNotFound();
    }

    public function test_admin_identity_and_time_cannot_be_forged_by_form(): void
    {
        $admin = $this->account('admin');
        $claim = $this->claim();
        $this->actingAs($admin)
            ->post(
                route('admin.claims.return', $claim),
                $this->data([
                    'returned_by' => 99999,
                    'handover_officer_name' => 'Petugas palsu',
                    'returned_at' => '2000-01-01',
                    'handover_code' => 'PALSU',
                ]),
            )
            ->assertSessionHasNoErrors();
        $claim->refresh();
        $this->assertEquals($admin->id, $claim->returned_by);
        $this->assertSame($admin->name, $claim->handover_officer_name);
        $this->assertNotSame('PALSU', $claim->handover_code);
        $this->assertTrue($claim->returned_at->isToday());
    }

    public function test_additive_migration_keeps_existing_claims_and_is_repeatable(): void
    {
        $claim = $this->claim();
        $migration = require database_path(
            'migrations/2026_10_30_010000_add_claim_handover_details.php',
        );
        $migration->up();
        $this->assertNotNull($claim->fresh());
        $this->assertTrue(Schema::hasColumn('item_claims', 'handover_code'));
    }
}
