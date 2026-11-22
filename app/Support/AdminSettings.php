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
        'home_heading' => '',
        'brand_logo' => '',
        'brand_color' => '',
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

    public static function logoUrl(array $settings, string $fallback = 'images/logo-findback.png'): string
    {
        $path = (string) ($settings['brand_logo'] ?? '');
        if (!preg_match('~\Abranding/[A-Za-z0-9]+\.(png|jpg|jpeg|webp)\z~i', $path)) {
            return asset($fallback);
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->url($path);
    }

    public static function brandColor(array $settings): string
    {
        $color = (string) ($settings['brand_color'] ?? '');

        return preg_match('/^#[0-9a-f]{6}$/iD', $color) ? $color : '';
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
