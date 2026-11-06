<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportRevisionRequest extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['closed_at' => 'datetime', 'before_data' => 'array', 'after_data' => 'array'];
    }
}
