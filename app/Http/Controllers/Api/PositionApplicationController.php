<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePositionApplicationRequest;
use App\Models\PositionApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PositionApplicationController extends Controller
{
    public function store(StorePositionApplicationRequest $request): JsonResponse
    {
        $data = $request->validated();

        $uuid = (string) Str::uuid();
        $baseDir = 'position-applications/' . $data['position_open_request_id'] . '/' . $uuid;

        $resumePath = null;
        if ($request->hasFile('resume_cv')) {
            $resumeFile = $request->file('resume_cv');
            $resumeExt = $resumeFile->getClientOriginalExtension() ?: 'pdf';
            $resumePath = $resumeFile->storeAs(
                $baseDir,
                'resume_cv.' . $resumeExt,
                'public'
            );
        }

        $supportPaths = null;
        if ($request->hasFile('supporting_documents')) {
            $supportPaths = [];
            foreach ($request->file('supporting_documents') as $i => $file) {
                $ext = $file->getClientOriginalExtension() ?: 'pdf';
                $stored = $file->storeAs($baseDir . '/supporting', 'supporting_' . ($i + 1) . '.' . $ext, 'public');
                $supportPaths[] = $stored;
            }
            // Store null if empty array
            if (count($supportPaths) === 0) {
                $supportPaths = null;
            }
        }

        $application = PositionApplication::create([
            'position_open_request_id' => (int) $data['position_open_request_id'],
            'position_id' => (int) $data['position_id'],
            'branch_id' => isset($data['branch_id']) ? (int) $data['branch_id'] : null,
            'department' => $data['department'] ?? null,
            'job_title' => $data['job_title'],

            'applicant_full_name' => $data['full_name'],
            'applicant_email' => $data['email'],
            'applicant_phone' => $data['phone'],
            'applicant_address' => $data['address'],
            'cover_letter' => $data['cover_letter'],
            'years_of_experience' => (int) $data['years_of_experience'],
            'education' => $data['education'],
            'available_start_date' => $data['available_start_date'],

            'linkedin_url' => $data['linkedin_url'] ?? null,
            'portfolio_url' => $data['portfolio_url'] ?? null,

            'privacy_consent' => (bool) $data['privacy_consent'],
            'website' => $data['website'] ?? null,

            'resume_path' => $resumePath,
            'supporting_documents_paths' => $supportPaths,

            'status' => 'Submitted',
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Application submitted successfully.',
            'application_id' => $application->id,
        ]);
    }
}

