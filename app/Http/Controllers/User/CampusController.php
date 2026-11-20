<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\{ItemClaim, LostFoundItem};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Storage};
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class CampusController extends Controller
{
    /**
     * Menampilkan detail laporan milik pengguna.
     */
    public function report(Request $request, LostFoundItem $report)
    {
        abort_unless(
            (int) $report->user_id === (int) $request->user()->id,
            404
        );

        $report->load(['revisions', 'reviewer', 'reporter']);

        $history = \App\Support\ReportAudit::history($report);

        return view('user.reports.show', compact('report', 'history'));
    }

    /**
     * Memeriksa kelayakan barang untuk diklaim.
     * (Syarat "bukan pelapor" dimatikan sementara untuk tes.)
     */
    private function eligible(Request $request, LostFoundItem $item): void
    {
        abort_unless(
            $item->type === 'found' &&
            $item->status === 'verified',
            404
        );
    }

    /**
     * Menampilkan halaman pengajuan klaim.
     */
    public function claim(Request $request, LostFoundItem $item)
    {
        $this->eligible($request, $item);

        if (
            ItemClaim::where('lost_found_item_id', $item->id)
                ->where('user_id', $request->user()->id)
                ->whereIn('status', ['pending', 'approved', 'returned'])
                ->exists()
        ) {
            return redirect()
                ->route('user.claims.index')
                ->with('success', 'Klaim untuk barang ini sudah tercatat.');
        }

        return view('user.claims.create', compact('item'));
    }

    /**
     * Menyimpan pengajuan klaim barang.
     */
    public function storeClaim(Request $request, LostFoundItem $item)
    {
        $this->eligible($request, $item);

        $data = $request->validate([
            'proof_description' => 'required|string|min:10|max:3000',
            'proof_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $path = null;

        try {
            DB::transaction(function () use ($request, $item, $data, &$path) {

                $locked = LostFoundItem::whereKey($item->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->eligible($request, $locked);

                if (
                    ItemClaim::where('lost_found_item_id', $item->id)
                        ->where('user_id', $request->user()->id)
                        ->whereIn('status', ['pending', 'approved', 'returned'])
                        ->exists()
                ) {
                    throw ValidationException::withMessages([
                        'proof_description' =>
                            'Klaim untuk barang ini sudah ada. Periksa Aktivitas Saya.',
                    ]);
                }

                if (
                    ItemClaim::where('lost_found_item_id', $item->id)
                        ->whereIn('status', ['approved', 'returned'])
                        ->exists()
                ) {
                    throw ValidationException::withMessages([
                        'proof_description' =>
                            'Barang sudah dalam proses penyerahan kepada pemiliknya.',
                    ]);
                }

                if ($request->hasFile('proof_photo')) {
                    $path = $request->file('proof_photo')
                        ->store('claims/proofs', 'public');
                }

                ItemClaim::create([
                    'user_id' => $request->user()->id,
                    'lost_found_item_id' => $item->id,
                    'proof_description' => $data['proof_description'],
                    'proof_photo_path' => $path,
                    'status' => 'pending',
                ]);
            });

        } catch (\Throwable $e) {

            if ($path) {
                Storage::disk('public')->delete($path);
            }

            throw $e;
        }

        return redirect()
            ->route('user.claims.index')
            ->with(
                'success',
                'Klaim berhasil diajukan. Chat tersedia setelah klaim disetujui admin.'
            );
    }

    /**
     * Menampilkan halaman notifikasi pengguna.
     */
    public function notifications(Request $request)
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('user.notifications.index', compact('notifications'));
    }

    /**
     * Menandai satu notifikasi sebagai sudah dibaca.
     */
    public function read(Request $request, string $id)
    {
        $notification = $request->user()
            ->notifications()
            ->whereKey($id)
            ->firstOrFail();

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        return back()->with('success', 'Notifikasi berhasil ditandai dibaca.');
    }

    /**
     * Menandai seluruh notifikasi sebagai sudah dibaca.
     */
    public function readAll(Request $request)
    {
        $updated = $request->user()
            ->unreadNotifications()
            ->update([
                'read_at' => now(),
            ]);

        return back()->with(
            'success',
            $updated > 0
                ? 'Semua notifikasi berhasil ditandai dibaca.'
                : 'Tidak ada notifikasi baru yang perlu dibaca.'
        );
    }

    /**
     * Menampilkan halaman profil pengguna.
     */
    public function profile()
    {
        return view('user.profile.index');
    }

    /**
     * Menampilkan foto profil pengguna.
     */
    public function profilePhoto(Request $request)
    {
        $path = $request->user()->profile_photo_path;
        $disk = Storage::disk('public');

        abort_unless($path && $disk->exists($path), 404);

        return $disk->response($path, null, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ], 'inline');
    }

    /**
     * Memperbarui nama dan foto pengguna.
     */
    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'photo.image' => 'File harus berupa gambar.',
            'photo.mimes' => 'Gunakan foto JPG, PNG, atau WebP.',
            'photo.max' => 'Ukuran foto maksimal 2 MB.',
        ]);

        $userId = $request->user()->getKey();
        $disk = Storage::disk('public');
        $newPath = null;

        // Simpan foto baru jika pengguna memilih file.
        if ($request->hasFile('photo')) {
            $newPath = $disk->putFile(
                "profile-photos/{$userId}",
                $request->file('photo')
            );

            if (!$newPath) {
                return back()
                    ->withInput($request->only('name'))
                    ->withErrors([
                        'photo' => 'Foto gagal disimpan. Silakan pilih ulang foto.',
                    ]);
            }
        }

        try {
            $oldPath = DB::transaction(function () use ($request, $userId, $data, $newPath) {
                $user = $request->user()
                    ->newQuery()
                    ->lockForUpdate()
                    ->findOrFail($userId);

                $previousPath = $user->profile_photo_path;
                $user->name = $data['name'];

                if ($newPath !== null) {
                    $user->profile_photo_path = $newPath;
                } elseif ($request->boolean('remove_photo')) {
                    $user->profile_photo_path = null;
                }

                $user->save();

                return $previousPath !== $user->profile_photo_path
                    ? $previousPath
                    : null;
            });
        } catch (\Throwable $exception) {
            if ($newPath !== null) {
                $disk->delete($newPath);
            }

            throw $exception;
        }

        // Hapus foto lama setelah perubahan berhasil disimpan.
        if ($oldPath) {
            $disk->delete($oldPath);
        }

        $message = 'Profil berhasil diperbarui.';

        if ($request->hasFile('photo')) {
            $message = 'Foto profil berhasil diperbarui.';
        } elseif ($request->boolean('remove_photo')) {
            $message = 'Foto profil berhasil dihapus.';
        }

        return redirect()
            ->route('user.profile.index')
            ->with('profile_toast', $message);
    }

    /**
     * Memperbarui kata sandi pengguna.
     */
    public function password(Request $request)
    {
        $data = $request->validate([
            'current_password' => [
                'required',
                'current_password',
            ],
            'password' => [
                'required',
                'confirmed',
                Password::min(8),
            ],
        ], [
            'current_password.required' =>
                'Kata sandi saat ini wajib diisi.',

            'current_password.current_password' =>
                'Kata sandi saat ini tidak sesuai.',

            'password.required' =>
                'Kata sandi baru wajib diisi.',

            'password.confirmed' =>
                'Konfirmasi kata sandi tidak cocok.',
        ]);

        $request->user()->update([
            'password' => $data['password'],
        ]);

        $request->session()->regenerate();

        return redirect()
            ->route('user.profile.index')
            ->with(
                'profile_toast',
                'Kata sandi berhasil diperbarui.'
            );
    }
}