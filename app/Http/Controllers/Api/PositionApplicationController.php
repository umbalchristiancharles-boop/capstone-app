<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePositionApplicationRequest;
use App\Models\PositionApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PositionApplicationController extends Controller
{
    public function listForHrBranch(\Illuminate\Http\Request $request): JsonResponse
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        $branchId = null;
        if ($user && $user->branch_id) {
            $branchId = (int) $user->branch_id;
        } elseif ($request->query('branch_id')) {
            // fallback for testing; real HR usage should rely on logged-in branch
            $branchId = (int) $request->query('branch_id');
        }

        $query = PositionApplication::query()
            ->with([]);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        } else {
            // If no branch is associated, return empty for safety
            return response()->json([
                'ok' => true,
                'applications' => [],
                'message' => 'No branch associated with HR user.'
            ]);
        }

        $applications = $query
            ->orderByDesc('created_at')
            ->get([
                'id',
                'position_open_request_id',
                'position_id',
                'branch_id',
                'department',
                'job_title',
                'applicant_full_name',
                'applicant_email',
                'applicant_phone',
                'available_start_date',
                'status',
                'resume_path',
                'supporting_documents_paths',
                'created_at'
            ]);

        return response()->json([
            'ok' => true,
            'applications' => $applications,
        ]);
    }

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

        try {
            $submittedOn = now()->format('F j, Y g:i A');

            \send_raw_mail_notification(
                $application->applicant_email,
                'CHIKIN TAYO - Application Submitted',
                "Hi {$application->applicant_full_name},\n\nYour application for {$application->job_title} has been submitted successfully. Our team will review your details and contact you if you are shortlisted for the next step. Please wait for further updates from us.\n\nApplication ID: {$application->id}\nSubmitted on: {$submittedOn}\n\nThank you for your interest in joining CHIKIN TAYO."
            );

            Log::info('Application submission email sent', [
                'application_id' => $application->id,
                'email' => $application->applicant_email,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to send application submission email', [
                'application_id' => $application->id,
                'email' => $application->applicant_email,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Application submitted successfully.',
            'application_id' => $application->id,
        ]);
    }
}

