<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\LostFoundItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    /**
     * Form laporan barang hilang.
     */
    public function createLost(): View
    {
        return view('user.reports.lost.create');
    }

    /**
     * Simpan laporan barang hilang.
     */
    public function storeLost(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            [
                'title' => ['required', 'string', 'max:255'],
                'location' => ['required', 'string', 'max:255'],
                'event_date' => ['required', 'date', 'before_or_equal:today'],
                'description' => ['required', 'string', 'max:3000'],
                'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            ],
            [
                'title.required' => 'Nama barang wajib diisi.',
                'location.required' => 'Lokasi kehilangan wajib diisi.',
                'event_date.required' => 'Tanggal kehilangan wajib diisi.',
                'event_date.before_or_equal' => 'Tanggal kehilangan tidak boleh melebihi hari ini.',
                'description.required' => 'Deskripsi barang wajib diisi.',
                'photo.image' => 'File harus berupa gambar.',
                'photo.mimes' => 'Format foto harus JPG, JPEG, PNG, atau WEBP.',
                'photo.max' => 'Ukuran foto maksimal 5 MB.',
            ],
        );

        $photoPath = null;

        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('lost-found', 'public');
        }

        try {
            LostFoundItem::create([
                'user_id' => $request->user()->id,
                'title' => $validated['title'],
                'category' => 'Lainnya', // kompatibilitas kolom database lama
                'type' => 'lost',
                'location' => $validated['location'],
                'event_date' => $validated['event_date'],
                'description' => $validated['description'],
                'photo_path' => $photoPath,
                'status' => 'pending',
            ]);
        } catch (\Throwable $exception) {
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }

            throw $exception;
        }

        return redirect()
            ->route('dashboard')
            ->with(
                'success',
                'Laporan barang hilang berhasil dikirim dan menunggu verifikasi Admin.',
            );
    }
}
