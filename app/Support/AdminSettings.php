<?php

namespace App\Support;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Schema;

final class AdminSettings
{
    public const DEFAULTS = [
        'site_name' => 'FindBack Kampus',
        'site_description' => 'Sistem informasi barang hilang dan ditemukan di kampus',
        'institution_name' => 'Fakultas Sains dan Teknologi',
        'contact_email' => '',
        'matching_min_score' => '50',
        'notification_reports' => '1',
        'notification_claims' => '1',
        'notification_matching' => '1',
    ];

    public static function all(?bool $tableExists = null): array
    {
        if (!($tableExists ?? Schema::hasTable('app_settings'))) {
            return self::DEFAULTS;
        }
        return array_replace(
            self::DEFAULTS,
            AppSetting::query()
                ->whereIn('setting_key', array_keys(self::DEFAULTS))
                ->pluck('setting_value', 'setting_key')
                ->all(),
        );
    }

    public static function minimumScore(): int
    {
        return max(0, min(100, (int) self::all()['matching_min_score']));
    }

    public static function notificationsEnabled(string $category): bool
    {
        return (self::all()['notification_' . $category] ?? '0') === '1';
    }
}
