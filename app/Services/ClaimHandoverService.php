<?php

namespace App\Services;

use App\Models\{ItemClaim, LostFoundItem, User};
use App\Notifications\HandoverResultNotification;
use App\Support\AdminAlerts;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{DB, Storage};
use Illuminate\Validation\ValidationException;

class ClaimHandoverService
{
    public function complete(ItemClaim $claim, User $actor, array $data, UploadedFile $upload): void
    {
        // Uploads are private. File operations cannot be rolled back by the database.
        $path = $upload->store('handovers', 'local');
        if (!$path) {
            throw ValidationException::withMessages([
                'handover_evidence' => 'Bukti gagal disimpan. Periksa izin folder storage.',
            ]);
        }
        try {
            $closedClaims = DB::transaction(function () use (
                $claim,
                $data,
                $actor,
                $path,
                $upload,
            ) {
                $item = LostFoundItem::query()
                    ->lockForUpdate()
                    ->findOrFail($claim->lost_found_item_id);
                $lockedClaim = ItemClaim::query()->lockForUpdate()->findOrFail($claim->id);
                if ($lockedClaim->evidenceRequests()->whereNull('closed_at')->exists()) {
                    throw ValidationException::withMessages([
                        'return' => 'Klaim masih menunggu bukti tambahan.',
                    ]);
                }
                if ($lockedClaim->status !== 'approved') {
                    throw ValidationException::withMessages([
                        'return' => 'Klaim sudah diproses. Serah terima tidak dapat diulang.',
                    ]);
                }
                if ($item->type !== 'found' || $item->status !== 'verified') {
                    throw ValidationException::withMessages([
                        'return' => 'Status barang tidak memungkinkan proses pengembalian.',
                    ]);
                }
                if (!$lockedClaim->user_id || !$lockedClaim->claimant) {
                    throw ValidationException::withMessages([
                        'return' =>
                            'Akun pengaju tidak tersedia. Identitas harus diklarifikasi sebelum serah terima.',
                    ]);
                }
                $at = now();
                $code =
                    'FB-ST-' .
                    $at->copy()->timezone('Asia/Jakarta')->format('Ymd') .
                    '-' .
                    str_pad((string) $lockedClaim->id, 6, '0', STR_PAD_LEFT);
                $lockedClaim
                    ->forceFill([
                        'status' => 'returned',
                        'returned_at' => $at,
                        'returned_by' => $actor->id,
                        'handover_code' => $code,
                        'recipient_name' => trim($data['recipient_name']),
                        'recipient_identifier' => isset($data['recipient_identifier'])
                            ? trim($data['recipient_identifier'])
                            : null,
                        'handover_location' => trim($data['handover_location']),
                        'handover_notes' => isset($data['handover_notes'])
                            ? trim($data['handover_notes'])
                            : null,
                        'handover_evidence_path' => $path,
                        'handover_evidence_mime' => $upload->getMimeType(),
                        'handover_officer_name' => $actor->name,
                        'handover_item_title' => $item->title,
                    ])
                    ->save();
                $item->update(['status' => 'returned']);
                $others = ItemClaim::query()
                    ->where('lost_found_item_id', $item->id)
                    ->where('id', '!=', $lockedClaim->id)
                    ->where('status', 'pending')
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();
                foreach ($others as $other) {
                    $other
                        ->evidenceRequests()
                        ->whereNull('closed_at')
                        ->update([
                            'closed_at' => $at,
                            'closed_by' => $actor->id,
                            'closer_name' => $actor->name,
                            'closure_type' => 'item_returned',
                            'closure_note' =>
                                'Ditutup otomatis karena barang diserahkan melalui ' . $code . '.',
                        ]);
                    $other->update([
                        'status' => 'rejected',
                        'rejection_reason' =>
                            'Ditutup otomatis karena barang telah diserahterimakan melalui ' .
                            $code .
                            '. Ini bukan penilaian atas keabsahan bukti pengajuan ini.',
                        'reviewed_by' => $actor->id,
                        'reviewed_at' => $at,
                    ]);
                }
                return $others;
            }, 3);
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }
        $claim->refresh();
        AdminAlerts::notify(
            $claim->claimant,
            new HandoverResultNotification($claim->handover_item_title, $claim->handover_code),
        );
        foreach ($closedClaims as $closed) {
            AdminAlerts::notify(
                $closed->claimant,
                new HandoverResultNotification(
                    $claim->handover_item_title,
                    $claim->handover_code,
                    false,
                ),
            );
        }
    }
}
