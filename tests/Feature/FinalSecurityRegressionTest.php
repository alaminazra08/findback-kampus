<?php

namespace Tests\Feature;

use App\Models\{ChatMessage, ItemClaim, LostFoundItem, User};
use App\Services\ClaimChatService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FinalSecurityRegressionTest extends TestCase
{
    use DatabaseMigrations;

    public function test_registration_ignores_injected_admin_role_and_login_checks_password(): void
    {
        $this->post(route('register.store'), ['name' => 'Mahasiswa uji',
            'email' => 'student@example.test', 'password' => 'password123',
            'password_confirmation' => 'password123', 'role' => 'admin'])
            ->assertSessionHasNoErrors()->assertRedirect(route('dashboard'));
        $user = User::where('email', 'student@example.test')->firstOrFail();
        $this->assertSame('user', $user->role);
        $this->get(route('admin.dashboard'))->assertForbidden();
        $this->post(route('logout'))->assertRedirect();
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'wrong'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password123'])
            ->assertSessionHasNoErrors()->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    private function fixture(): array
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $item = LostFoundItem::create(['user_id' => $admin->id, 'title' => 'Dompet',
            'type' => 'found', 'status' => 'verified', 'location' => 'FST']);
        $claim = ItemClaim::create(['user_id' => $user->id, 'lost_found_item_id' => $item->id,
            'status' => 'approved', 'proof_description' => 'Bukti kepemilikan khusus']);
        return [$admin, $user, $claim];
    }

    public function test_attachment_only_chat_and_invalid_payloads(): void
    {
        Storage::fake('public');
        [$admin, $user, $claim] = $this->fixture();
        $this->actingAs($user)->postJson(route('user.chat.store'), ['claim_id' => $claim->id])
            ->assertUnprocessable();
        $this->postJson(route('user.chat.store'), ['claim_id' => $claim->id,
            'message' => str_repeat('x', 3001)])->assertUnprocessable();
        $this->post(route('user.chat.store'), ['claim_id' => $claim->id,
            'attachment' => UploadedFile::fake()->create('script.svg', 1, 'image/svg+xml')])
            ->assertSessionHasErrors('attachment');
        $this->post(route('user.chat.store'), ['claim_id' => $claim->id,
            'attachment' => UploadedFile::fake()->image('photo.png')])->assertSessionHasNoErrors();
        $message = ChatMessage::firstOrFail();
        $this->assertSame($claim->id, $message->claim_id);
        Storage::disk('public')->assertExists($message->attachment_path);
        $this->actingAs($admin)->getJson(route('admin.chat.messages',
            ['user' => $user->id, 'claim_id' => $claim->id]))->assertOk()->assertJsonCount(1, 'messages');
        $this->assertNotNull($message->fresh()->read_at);
    }

    public function test_stale_approved_model_cannot_send_after_handover_and_upload_is_removed(): void
    {
        Storage::fake('public');
        [$admin, $user, $claim] = $this->fixture();
        ItemClaim::whereKey($claim->id)->update(['status' => 'returned']);
        try {
            app(ClaimChatService::class)->send($claim, $user, ['message' => 'Stale'],
                UploadedFile::fake()->image('photo.png'));
            $this->fail('Stale status must be checked inside transaction');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
        $this->assertDatabaseCount('chat_messages', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_admin_pagination_keeps_selected_claim(): void
    {
        [$admin, $user, $claim] = $this->fixture();
        foreach (range(1, 51) as $i) {
            ChatMessage::create(['user_id' => $user->id, 'sender_id' => $user->id,
                'claim_id' => $claim->id, 'message' => 'Pesan ' . $i]);
        }
        $this->actingAs($admin)->get(route('admin.chat.show',
            ['user' => $user->id, 'claim_id' => $claim->id]))->assertOk()
            ->assertViewHas('messages', fn ($messages) => str_contains($messages->nextPageUrl(),
                'claim_id=' . $claim->id));
    }
    public function test_rejected_report_filter_preserves_history_mode(): void
    {
        [$admin] = $this->fixture();
        $item = LostFoundItem::create(['user_id' => $admin->id, 'title' => 'Perlu diperbaiki',
            'type' => 'lost', 'status' => 'rejected', 'location' => 'FST']);
        $this->actingAs($admin)->get(route('admin.reports.verification',
            ['revision' => 'history', 'search' => 'Perlu']))->assertOk()
            ->assertSee('name="revision" value="history"', false)
            ->assertViewHas('items', fn ($items) => $items->pluck('id')->all() === [$item->id]);
    }

}
