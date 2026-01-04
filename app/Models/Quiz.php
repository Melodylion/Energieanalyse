<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'description',
        'intro_text',
        'analysis_title',
        'analysis_text',
        'analysis_graph_text',
        'analysis_report_text',
        'report_title',
        'pdf_page2_title',
        'pdf_page2_text',
        'email_body',
        'email_subject',
        'active'
    ];

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }
}
