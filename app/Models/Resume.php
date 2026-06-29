<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resume extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'skills',
        'experience_years',
        'experience_details',
        'education',
        'certifications',
        'preferred_location',
        'current_salary',
        'expected_salary',
        'job_role',
        'keywords',
        'resume_path',
        'ai_analysis',
    ];

    protected $casts = [
        'skills' => 'array',
        'experience_details' => 'array',
        'education' => 'array',
        'certifications' => 'array',
        'keywords' => 'array',
        'ai_analysis' => 'array',
        'experience_years' => 'decimal:1',
    ];
}
