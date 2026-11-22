<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Support\AdminSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

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
            'home_heading' => ['nullable', 'string', 'max:120'],
            'site_description' => ['present', 'nullable', 'string', 'max:500'],
            'institution_name' => ['required', 'string', 'max:150'],
            'brand_color' => ['nullable', 'regex:/^#[0-9a-f]{6}$/iD'],
            'brand_logo_upload' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048', 'dimensions:max_width=1600,max_height=1600'],
            'brand_logo_reset' => ['nullable', 'boolean'],
            'brand_color_reset' => ['nullable', 'boolean'],
            'matching_min_score' => ['required', 'integer', 'between:0,100'],
        ]);

        $upload = $request->file('brand_logo_upload');
        $newLogo = null;
        if ($upload) {
            $newLogo = $upload->store('branding', 'public');
            if (!$newLogo) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'brand_logo_upload' => 'Logo gagal disimpan. Coba lagi.',
                ]);
            }
            $data['brand_logo'] = $newLogo;
        } elseif ($request->boolean('brand_logo_reset')) {
            $data['brand_logo'] = '';
        }

        if ($request->boolean('brand_color_reset')) {
            $data['brand_color'] = '';
        }

        unset($data['brand_logo_upload'], $data['brand_logo_reset'], $data['brand_color_reset']);

        $oldLogo = null;
        try {
            // Perubahan dibandingkan nilai efektif (termasuk default), bukan sekadar
            // keberadaan baris database. Tidak mencatat nilai sensitif pada log.
            $changedKeys = DB::transaction(function () use ($data, &$oldLogo): array {
                $stored = AppSetting::query()
                    ->whereIn('setting_key', array_keys(AdminSettings::DEFAULTS))
                    ->lockForUpdate()
                    ->pluck('setting_value', 'setting_key')
                    ->all();
                $previous = array_replace(AdminSettings::DEFAULTS, $stored);
                $oldLogo = $previous['brand_logo'];
                $changed = [];

                foreach ($data as $key => $submittedValue) {
                    $value = (string) $submittedValue;
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

        } catch (\Throwable $error) {
            if ($newLogo) {
                Storage::disk('public')->delete($newLogo);
            }
            throw $error;
        }

        if (in_array('brand_logo', $changedKeys, true)
            && preg_match('~\Abranding/[A-Za-z0-9]+\.(png|jpg|jpeg|webp)\z~i', (string) $oldLogo)) {
            Storage::disk('public')->delete($oldLogo);
        }

        // Log sesudah commit database supaya perubahan yang gagal tidak tercatat sukses.
        foreach ($changedKeys as $key) {
            Log::info('FindBack admin setting changed', [
                'actor_id' => $request->user()->id,
                'setting_key' => $key,
                'changed' => true,
            ]);
        }

        if ($request->expectsJson()) {
            $request->session()->flash('success', 'Pengaturan berhasil disimpan.');

            return response()->json([
                'message' => 'Pengaturan berhasil disimpan.',
            ]);
        }

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Pengaturan berhasil disimpan.');
    }
}
