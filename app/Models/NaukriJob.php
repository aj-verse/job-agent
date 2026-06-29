<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NaukriJob extends Model
{
    protected $table = 'naukri_jobs';

    protected $fillable = [
        'user_id',
        'job_id',
        'title',
        'company',
        'location',
        'salary',
        'experience_required',
        'description',
        'posted_date',
        'job_url',
        'match_score',
        'skills_match_score',
        'experience_match_score',
        'location_match_score',
        'status',
        'rejection_reason',
        'applied_at',
        'application_id',
    ];

    protected $casts = [
        'applied_at' => 'datetime',
        'match_score' => 'integer',
        'skills_match_score' => 'integer',
        'experience_match_score' => 'integer',
        'location_match_score' => 'integer',
    ];
}
