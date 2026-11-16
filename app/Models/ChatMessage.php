<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
