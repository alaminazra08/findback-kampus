<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ChatMessage extends Model
{
    protected $fillable = [
        'user_id',
        'claim_id',
        'sender_id',
        'message',
        'attachment_path',
        'read_at',
        'edited_at',
        'recalled_at',
        'hidden_for_user',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'edited_at' => 'datetime',
            'recalled_at' => 'datetime',
            'hidden_for_user' => 'boolean',
        ];
    }

    public function hiddenByAdmins(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chat_admin_hidden_messages');
    }

    public function scopeVisibleToAdmin($query, int $adminId)
    {
        return $query->whereDoesntHave('hiddenByAdmins', fn ($admins) => $admins->whereKey($adminId));
    }

    public function recallDeadline(): ?\Illuminate\Support\Carbon
    {
        return $this->claim_id && !$this->recalled_at ? $this->created_at->copy()->addHours(48) : null;
    }

    public static function syncReadReceipts(Request $request, Builder $scope, ?int $incomingSender = null): JsonResponse
    {
        $data = $request->validate([
            'seen' => ['present', 'array', 'max:100'],
            'seen.*' => ['integer', 'min:1'],
            'outgoing' => ['present', 'array', 'max:100'],
            'outgoing.*' => ['integer', 'min:1'],
        ]);

        $scope->whereNull('recalled_at');
        $incoming = (clone $scope)->whereIn('id', $data['seen'])->whereNull('read_at');
        if ($incomingSender === null) {
            $incoming->where('sender_id', '!=', $request->user()->id);
        } else {
            $incoming->where('sender_id', $incomingSender);
        }
        $incoming->update(['read_at' => now()]);

        $receipts = $scope->where('sender_id', $request->user()->id)
            ->whereIn('id', $data['outgoing'])->whereNotNull('read_at')->get(['id', 'read_at'])
            ->mapWithKeys(fn (ChatMessage $message) => [$message->id => $message->read_at->toIso8601String()]);

        return response()->json(['receipts' => (object) $receipts->all()]);
    }

    public function attachmentPhotoRatio(): ?float
    {
        if ($this->recalled_at || !preg_match('/\.(jpe?g|png|webp)$/i', $this->attachment_path ?? '')) {
            return null;
        }

        $file = Storage::disk('public')->path($this->attachment_path);
        $size = @getimagesize($file);

        return $size && $size[0] > 0 && $size[1] > 0 ? $size[0] / $size[1] : null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function claim(): BelongsTo
    {
        return $this->belongsTo(ItemClaim::class, 'claim_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
