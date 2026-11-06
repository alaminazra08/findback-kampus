<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemMatch extends Model
{
    protected $table = 'item_matches';

    protected $fillable = [
        'lost_item_id',
        'found_item_id',
        'match_score',
        'match_reason',
        'status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'match_score' => 'decimal:2',
            'reviewed_at' => 'datetime',
        ];
    }

    public function lostItem(): BelongsTo
    {
        return $this->belongsTo(LostFoundItem::class, 'lost_item_id');
    }

    public function foundItem(): BelongsTo
    {
        return $this->belongsTo(LostFoundItem::class, 'found_item_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
