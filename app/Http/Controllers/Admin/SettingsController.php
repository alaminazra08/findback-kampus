<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Support\AdminSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    public function index()
    {
        // Tabel wajib sudah dibuat sesuai SQL modul Pengaturan.
        abort_unless(Schema::hasTable('app_settings'), 503, 'Tabel app_settings belum tersedia.');

        $settings = AdminSettings::all(tableExists: true);
        request()->attributes->set(AdminSettings::class . '.branding', $settings);

        $system = [
            'laravel' => app()->version(),
            'php' => PHP_VERSION,
        ];

        return view('admin.settings.index', compact('settings', 'system'));
    }

    public function update(Request $request)
    {
        abort_unless(Schema::hasTable('app_settings'), 503, 'Tabel app_settings belum tersedia.');

        $data = $request->validate([
            'site_name' => ['required', 'string', 'max:100'],
            'site_description' => ['nullable', 'string', 'max:500'],
            'institution_name' => ['required', 'string', 'max:150'],
            'contact_email' => ['nullable', 'email:rfc', 'max:255'],
            'matching_min_score' => ['required', 'integer', 'between:0,100'],
            'notification_reports' => ['required', Rule::in(['0', '1'])],
            'notification_claims' => ['required', Rule::in(['0', '1'])],
            'notification_matching' => ['required', Rule::in(['0', '1'])],
        ]);

        // Perubahan dibandingkan nilai efektif (termasuk default), bukan sekadar
        // keberadaan baris database. Tidak mencatat nilai sensitif pada log.
        $changedKeys = DB::transaction(function () use ($data): array {
            $stored = AppSetting::query()
                ->whereIn('setting_key', array_keys(AdminSettings::DEFAULTS))
                ->lockForUpdate()
                ->pluck('setting_value', 'setting_key')
                ->all();
            $previous = array_replace(AdminSettings::DEFAULTS, $stored);
            $changed = [];

            foreach (array_keys(AdminSettings::DEFAULTS) as $key) {
                $value = (string) ($data[$key] ?? '');
                if ((string) $previous[$key] !== $value) {
                    $changed[] = $key;
                }
                // Hindari UPDATE berulang jika tidak ada perubahan.
                if (!array_key_exists($key, $stored) || (string) $stored[$key] !== $value) {
                    AppSetting::query()->updateOrCreate(
                        ['setting_key' => $key],
                        ['setting_value' => $value],
                    );
                }
            }

            return $changed;
        });

        // Log sesudah commit database supaya perubahan yang gagal tidak tercatat sukses.
        foreach ($changedKeys as $key) {
            Log::info('FindBack admin setting changed', [
                'actor_id' => $request->user()->id,
                'setting_key' => $key,
                'changed' => true,
            ]);
        }

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Pengaturan berhasil disimpan.');
    }
}
