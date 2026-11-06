<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemClaim extends Model
{
    protected $table = 'item_claims';

    protected $fillable = [
        'lost_found_item_id',
        'user_id',
        'proof_description',
        'proof_photo_path',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
        'returned_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'returned_at' => 'datetime',
        ];
    }

    public function evidenceRequests(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ClaimEvidenceRequest::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(LostFoundItem::class, 'lost_found_item_id');
    }

    public function claimant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function handoverOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by');
    }

    public function hasHandoverReceipt(): bool
    {
        return $this->status === 'returned' &&
            $this->returned_at !== null &&
            filled($this->handover_code) &&
            filled($this->recipient_name) &&
            filled($this->handover_officer_name) &&
            filled($this->handover_location);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
