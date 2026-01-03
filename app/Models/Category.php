<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Category extends Model
{
    protected $guarded = [];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }
}
