<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Respondent extends Model
{
    protected $guarded = [];

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }
}
