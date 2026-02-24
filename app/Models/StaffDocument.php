<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffDocument extends Model
{
    use HasFactory;

    protected $table = 'staff_documents';

    protected $fillable = [
        'user_id',
        'resume_path',
        'government_id_path',
        'psa_birth_certificate_path',
        'nbi_clearance_path',
        'police_clearance_path',
        'medical_certificate_path',
        'drug_test_result_path',
        'sss_id_path',
        'philhealth_id_path',
        'pagibig_mdf_path',
        'tin_id_path',
        'diploma_transcript_path',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
