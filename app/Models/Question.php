<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Question extends Model
{
    protected $guarded = [];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'question_category_weights')
                    ->withPivot('weight')
                    ->withTimestamps();
    }
}
