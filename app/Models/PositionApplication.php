<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PositionApplication extends Model
{
    use HasFactory;

    protected $table = 'position_applications';

    protected $fillable = [
        'position_open_request_id',
        'position_id',
        'branch_id',
        'department',
        'job_title',

        'applicant_full_name',
        'applicant_email',
        'applicant_phone',
        'applicant_address',
        'cover_letter',
        'years_of_experience',
        'education',
        'available_start_date',

        'linkedin_url',
        'portfolio_url',

        'privacy_consent',
        'website',

        'resume_path',
        'supporting_documents_paths',

        'status',
    ];

    protected $casts = [
        'privacy_consent' => 'boolean',
        'available_start_date' => 'date',
        'supporting_documents_paths' => 'array',
        'years_of_experience' => 'integer',
    ];
}

