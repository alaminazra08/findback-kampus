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
    public function report(Request $request, LostFoundItem $report)
    {
        abort_unless((int) $report->user_id === (int) $request->user()->id, 404);
        $report->load(['revisions', 'reviewer', 'reporter']);
        $history = \App\Support\ReportAudit::history($report);
        return view('user.reports.show', compact('report', 'history'));
    }

    private function eligible(Request $request, LostFoundItem $item): void
    {
        abort_unless(
            $item->type === 'found' &&
                $item->status === 'verified' &&
                (int) $item->user_id !== (int) $request->user()->id,
            404,
        );
    }

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
                $locked = LostFoundItem::whereKey($item->id)->lockForUpdate()->firstOrFail();
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
                    $path = $request->file('proof_photo')->store('claims/proofs', 'public');
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
                'Klaim berhasil diajukan. Gunakan Chat Klaim untuk berkomunikasi dengan admin.',
            );
    }

    public function notifications(Request $request)
    {
        $notifications = $request->user()->notifications()->latest()->paginate(15);
        return view('user.notifications.index', compact('notifications'));
    }

    public function read(Request $request, string $id)
    {
        $request->user()->notifications()->whereKey($id)->firstOrFail()->markAsRead();
        return back();
    }

    public function readAll(Request $request)
    {
        $request
            ->user()
            ->unreadNotifications()
            ->update(['read_at' => now()]);
        return back()->with('success', 'Notifikasi ditandai dibaca.');
    }

    public function profile()
    {
        return view('user.profile.index');
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255']);
        $request->user()->update($data);
        return back()->with('success', 'Profil diperbarui.');
    }

    public function password(Request $request)
    {
        $data = $request->validate([
            'current_password' => 'required|current_password',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);
        $request->user()->update(['password' => $data['password']]);
        $request->session()->regenerate();
        return back()->with('success', 'Password berhasil diperbarui.');
    }
}
