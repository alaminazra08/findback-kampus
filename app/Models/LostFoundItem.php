<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LostFoundItem extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'category',
        'type',
        'location',
        'event_date',
        'description',
        'photo_path',
        'status',
    ];

    protected function casts(): array
    {
        return ['event_date' => 'date', 'reviewed_at' => 'datetime'];
    }

    public function revisions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ReportRevisionRequest::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
