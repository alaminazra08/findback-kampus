<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\User;
use App\Support\AdminSettings;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class AdminSettingsAccountRegressionTest extends TestCase
{
    use DatabaseMigrations;

    private function account(string $role): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => $role])->save();
        return $user;
    }

    public function test_saving_defaults_does_not_falsely_log_changes(): void
    {
        $admin = $this->account('admin');
        Log::spy();
        $this->actingAs($admin)
            ->put(route('admin.settings.update'), AdminSettings::DEFAULTS)
            ->assertRedirect(route('admin.settings.index'));
        $this->assertSame(AdminSettings::DEFAULTS, AdminSettings::all());
        Log::shouldNotHaveReceived('info');

        $this->put(route('admin.settings.update'), AdminSettings::DEFAULTS)->assertRedirect(
            route('admin.settings.index'),
        );
        Log::shouldNotHaveReceived('info');
    }

    public function test_change_is_saved_and_invalid_score_does_not_modify_settings(): void
    {
        $admin = $this->account('admin');
        $data = AdminSettings::DEFAULTS;
        $data['matching_min_score'] = '65';
        $this->actingAs($admin)
            ->put(route('admin.settings.update'), $data)
            ->assertRedirect(route('admin.settings.index'));
        $this->assertSame(
            '65',
            AppSetting::where('setting_key', 'matching_min_score')->value('setting_value'),
        );
        $data['matching_min_score'] = '101';
        $this->put(route('admin.settings.update'), $data)->assertSessionHasErrors(
            'matching_min_score',
        );
        $this->assertSame(
            '65',
            AppSetting::where('setting_key', 'matching_min_score')->value('setting_value'),
        );
    }

    public function test_account_role_guard_and_guest_authorization(): void
    {
        $admin = $this->account('admin');
        $target = $this->account('user');
        $url = route('admin.users.update-role', $target);
        $this->patch($url, ['role' => 'admin'])->assertRedirect(route('login'));
        $this->actingAs($target)
            ->patch($url, ['role' => 'admin'])
            ->assertForbidden();
        $this->actingAs($admin)
            ->patch($url, ['role' => 'admin'])
            ->assertRedirect();
        $this->assertSame('admin', $target->fresh()->role);
        $this->patch(route('admin.users.update-role', $admin), [
            'role' => 'user',
        ])->assertSessionHas('error');
        $this->assertSame('admin', $admin->fresh()->role);
    }
}
