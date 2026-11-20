<?php

namespace App\Services;

use App\Models\{ChatMessage, ItemClaim, LostFoundItem, User};
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{DB, Storage};

class ClaimChatService
{
    public function recall(ChatMessage $message, User $sender): void
    {
        abort_unless(
            (int) $message->sender_id === (int) $sender->id &&
            $message->claim()->where('user_id', $message->user_id)->exists(),
            403,
        );

        $attachment = DB::transaction(function () use ($message) {
            $locked = ChatMessage::lockForUpdate()->findOrFail($message->id);
            if ($locked->recalled_at) {
                return null;
            }

            $now = now();
            abort_unless($locked->recallDeadline()?->gt($now), 422, 'Batas hapus untuk semua orang adalah 48 jam.');
            $path = $locked->attachment_path;
            $locked->update([
                'message' => null,
                'attachment_path' => null,
                'recalled_at' => $now,
            ]);

            return $path;
        }, 3);

        if ($attachment) {
            Storage::disk('public')->delete($attachment);
        }
    }

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
