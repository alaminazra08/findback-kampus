<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ItemClaim;
use App\Models\LostFoundItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ClaimEvidenceResponseController extends Controller
{
    public function update(Request $request, ItemClaim $claim): RedirectResponse
    {
        abort_unless((int) $claim->user_id === (int) $request->user()->id, 404);
        $data = $request->validate([
            'request_id' => ['required', 'integer'],
            'response_note' => ['required', 'string', 'min:10', 'max:2000'],
            'proof_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'confirm_truth' => ['required', 'accepted'],
        ]);
        $uploadedPath = null;
        try {
            DB::transaction(function () use ($request, $claim, $data, &$uploadedPath): void {
                // Consistent locking order: item, then claim.
                $item = LostFoundItem::query()
                    ->lockForUpdate()
                    ->findOrFail($claim->lost_found_item_id);
                $locked = ItemClaim::query()->lockForUpdate()->findOrFail($claim->id);
                if ((int) $locked->user_id !== (int) $request->user()->id) {
                    abort(404);
                }
                $entry = $locked
                    ->evidenceRequests()
                    ->whereKey($data['request_id'])
                    ->whereNull('closed_at')
                    ->lockForUpdate()
                    ->first();
                if (
                    !$entry ||
                    $locked->status !== 'pending' ||
                    $item->type !== 'found' ||
                    $item->status !== 'verified'
                ) {
                    throw ValidationException::withMessages([
                        'response_note' =>
                            'Permintaan tidak aktif atau status barang telah berubah. Muat ulang halaman.',
                    ]);
                }
                if (
                    ItemClaim::query()
                        ->where('lost_found_item_id', $item->id)
                        ->where('id', '!=', $locked->id)
                        ->whereIn('status', ['approved', 'returned'])
                        ->exists()
                ) {
                    throw ValidationException::withMessages([
                        'response_note' => 'Barang sudah memiliki klaim yang disetujui.',
                    ]);
                }
                $oldPhoto = $locked->proof_photo_path;
                if ($request->hasFile('proof_photo')) {
                    $uploadedPath = $request->file('proof_photo')->store('claims/proofs', 'public');
                    if (!$uploadedPath) {
                        throw ValidationException::withMessages([
                            'proof_photo' => 'Foto gagal disimpan.',
                        ]);
                    }
                }
                // Preserve original description and append the submitted response for the admin's review.
                $response = trim($data['response_note']);
                $locked->proof_description =
                    trim((string) $locked->proof_description) .
                    "\n\n[Bukti tambahan " .
                    now()->timezone('Asia/Jakarta')->format('d/m/Y H:i') .
                    " WIB]\n" .
                    $response;
                if ($uploadedPath) {
                    $locked->proof_photo_path = $uploadedPath;
                }
                $locked->save();
                $entry->update([
                    'closed_at' => now(),
                    'closed_by' => $request->user()->id,
                    'closer_name' => $request->user()->name,
                    'closure_type' => 'resubmitted',
                    'closure_note' =>
                        'Jawaban: ' .
                        $response .
                        ($uploadedPath
                            ? '; Foto bukti sebelumnya: ' .
                                ($oldPhoto ?: 'tidak ada') .
                                '; Foto bukti baru: ' .
                                $uploadedPath
                            : ''),
                ]);
            }, 3);
        } catch (\Throwable $exception) {
            if ($uploadedPath) {
                Storage::disk('public')->delete($uploadedPath);
            }
            throw $exception;
        }
        return redirect()
            ->route('user.claims.index')
            ->with('success', 'Bukti tambahan terkirim. Klaim kembali menunggu pemeriksaan Admin.');
    }
}
