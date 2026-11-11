<?php

namespace App\Services;

use App\Models\{ChatMessage, ItemClaim, LostFoundItem, User};
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{DB, Storage};

class ClaimChatService
{
    public function send(ItemClaim $claim, User $sender, array $data, ?UploadedFile $upload): ChatMessage
    {
        $path = null;
        try {
            if ($upload) {
                $path = $upload->store('chat-attachments', 'public');
                if (!$path) {
                    throw new \RuntimeException('Lampiran gagal disimpan.');
                }
            }
            return DB::transaction(function () use ($claim, $sender, $data, $path) {
                // Same lock order as approval and handover: item, then claim.
                LostFoundItem::lockForUpdate()->findOrFail($claim->lost_found_item_id);
                $locked = ItemClaim::lockForUpdate()->findOrFail($claim->id);
                abort_unless(
                    $locked->status === 'approved' &&
                    (int) $locked->user_id === (int) $claim->user_id &&
                    ($sender->role === 'admin' ||
                        ($sender->role === 'user' && (int) $locked->user_id === (int) $sender->id)),
                    403,
                    'Chat hanya tersedia untuk klaim disetujui yang belum selesai.',
                );
                return ChatMessage::create([
                    'user_id' => $locked->user_id,
                    'claim_id' => $locked->id,
                    'sender_id' => $sender->id,
                    'message' => $data['message'] ?? null,
                    'attachment_path' => $path,
                ]);
            }, 3);
        } catch (\Throwable $exception) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }
            throw $exception;
        }
    }
}
