<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePositionApplicationRequest;
use App\Models\PositionApplication;
use App\Models\PositionOpenRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PositionApplicationController extends Controller
{
    /**
     * Determine the appropriate role based on job title and department
     */
    private function determineRoleFromPosition(?string $jobTitle, ?string $department): string
    {
        $jobTitle = strtolower($jobTitle ?? '');
        
        // Check for manager positions
        if (str_contains($jobTitle, 'manager') || str_contains($jobTitle, 'head')) {
            return 'MANAGER';
        }
        
        // Default to STAFF for non-manager positions
        return 'STAFF';
    }

    /**
     * Generate a random password
     */
    private function generateRandomPassword(int $length = 12): string
    {
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $specialChars = '!@#$%^&*';
        
        $password = '';
        
        // Ensure at least one of each character type
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $specialChars[random_int(0, strlen($specialChars) - 1)];
        
        // Fill the rest with random characters
        $allChars = $uppercase . $lowercase . $numbers . $specialChars;
        for ($i = 4; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }
        
        // Shuffle the password
        return str_shuffle($password);
    }

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
                'applicant_address',
                'cover_letter',
                'years_of_experience',
                'education',
                'available_start_date',
                'linkedin_url',
                'portfolio_url',
                'privacy_consent',
                'website',
                'status',
                'resume_path',
                'supporting_documents_paths',
                'interview_date',
                'interview_time',
                'interview_notes',
                'created_at'
            ]);

        return response()->json([
            'ok' => true,
            'applications' => $applications,
        ]);
    }

    public function sendInterviewEmail(\Illuminate\Http\Request $request, $id): JsonResponse
    {
        $application = PositionApplication::findOrFail($id);

        if (!$application->applicant_email) {
            return response()->json([
                'ok' => false,
                'message' => 'Application does not have an email address.'
            ], 400);
        }

        try {
            $submittedOn = \Carbon\Carbon::parse($application->created_at)->format('F j, Y g:i A');
            $applicantName = $application->applicant_full_name ?? 'Applicant';
            $jobTitle = $application->job_title ?? 'the position';
            
            // Get interview schedule from request
            $interviewDate = $request->input('interview_date');
            $interviewTime = $request->input('interview_time');
            $notes = $request->input('notes', '');
            
            // Format interview date and time
            $interviewDateTime = '';
            if ($interviewDate && $interviewTime) {
                $date = \Carbon\Carbon::createFromFormat('Y-m-d', $interviewDate);
                $formattedDate = $date->format('F j, Y');
                $time = \Carbon\Carbon::createFromFormat('H:i', $interviewTime);
                $formattedTime = $time->format('g:i A');
                $interviewDateTime = "{$formattedDate} at {$formattedTime}";
            }

            $interviewInstructions = "Dear {$applicantName},\n\n"
                . "Thank you for your interest in joining CHIKIN TAYO and for applying for the {$jobTitle} position.\n\n"
                . "We are pleased to inform you that your application has been reviewed and you have been shortlisted for an interview.\n\n"
                . "INTERVIEW DETAILS:\n"
                . "- Date & Time: {$interviewDateTime}\n"
                . "- Please bring a valid ID and a copy of your resume/CV\n"
                . "- Arrive 10 minutes before your scheduled interview time\n"
                . "- Dress code: Business casual\n"
                . "- The interview will take approximately 30-45 minutes\n\n";

            if ($notes) {
                $interviewInstructions .= "ADDITIONAL NOTES:\n"
                    . "- {$notes}\n\n";
            }

            $interviewInstructions .= "NEXT STEPS:\n"
                . "Please confirm your attendance by replying to this email. "
                . "If you have any questions or need to reschedule, please reply to this email or contact us at hr@chikintayo.com.\n\n"
                . "We look forward to meeting you!\n\n"
                . "Best regards,\n"
                . "CHIKIN TAYO HR Team\n"
                . "Application ID: {$application->id}\n"
                . "Applied on: {$submittedOn}";

            \send_raw_mail_notification(
                $application->applicant_email,
                'CHIKIN TAYO - Interview Scheduled',
                $interviewInstructions
            );

            // Update application status and store interview schedule
            $updateData = ['status' => 'Ready for Interview'];
            
            // Store interview schedule if date and time are provided
            if ($interviewDate && $interviewTime) {
                $updateData['interview_date'] = $interviewDate;
                $updateData['interview_time'] = $interviewTime;
                $updateData['interview_notes'] = $notes;
            }
            
            $application->update($updateData);

            \Illuminate\Support\Facades\Log::info('Interview email sent', [
                'application_id' => $application->id,
                'email' => $application->applicant_email,
                'interview_date' => $interviewDate,
                'interview_time' => $interviewTime,
            ]);

            $message = 'Interview email sent successfully to ' . $application->applicant_email;
            if ($interviewDateTime) {
                $message .= " (Scheduled: {$interviewDateTime})";
            }

            return response()->json([
                'ok' => true,
                'message' => $message,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send interview email', [
                'application_id' => $application->id,
                'email' => $application->applicant_email,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Failed to send interview email: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function markAsPassed(\Illuminate\Http\Request $request, $id): JsonResponse
    {
        $application = PositionApplication::findOrFail($id);

        try {
            // Guard: only decrement the open position quantity if this application
            // was NOT already marked as passed (prevents double-decrementing).
            $wasAlreadyPassed = $application->status === 'Passed - Ready for Hiring';

            $application->update(['status' => 'Passed - Ready for Hiring']);

            // ✅ Decrement the open position quantity when an applicant is hired/passed.
            // This ensures the landing page reflects the actual number of available slots.
            // Note: We do NOT change the status to 'Filled' because the DB column is an
            // ENUM limited to ['Pending', 'Approved', 'Rejected']. Instead, the landing
            // page endpoint filters out positions where quantity <= 0.
            if (!$wasAlreadyPassed && $application->position_open_request_id) {
                $openRequest = PositionOpenRequest::find($application->position_open_request_id);
                if ($openRequest) {
                    $newQuantity = max(0, (int) $openRequest->quantity - 1);
                    $openRequest->update([
                        'quantity' => $newQuantity,
                    ]);
                }
            }

            // Create staff account from applicant information
            // Generate random password
            $randomPassword = $this->generateRandomPassword();
            
            // Generate username from email (use part before @)
            $email = $application->applicant_email;
            $username = explode('@', $email)[0];
            
            // Ensure username is unique by adding numbers if needed
            $originalUsername = $username;
            $counter = 1;
            while (\App\Models\User::where('username', $username)->exists()) {
                $username = $originalUsername . $counter;
                $counter++;
            }

            // Determine role based on job title/position
            $role = $this->determineRoleFromPosition($application->job_title, $application->department);
            
            // Create the staff user
            $staff = \App\Models\User::create([
                'username' => $username,
                'email' => $application->applicant_email,
                'full_name' => $application->applicant_full_name,
                'phone_number' => $application->applicant_phone,
                'address' => $application->applicant_address,
                'department' => $application->department,
                'password' => $randomPassword,
                'role' => $role,
                'branch_id' => $application->branch_id,
                'is_active' => 1,
                'must_change_password' => 1,
            ]);

            // Send email notification to applicant with credentials
            $applicantName = $application->applicant_full_name ?? 'Applicant';
            $jobTitle = $application->job_title ?? 'the position';
            $submittedOn = \Carbon\Carbon::parse($application->created_at)->format('F j, Y g:i A');

            $message = "Dear {$applicantName},\n\n"
                . "Congratulations! We are pleased to inform you that you have successfully passed your interview for the {$jobTitle} position.\n\n"
                . "ACCOUNT CREDENTIALS:\n"
                . "- Username: {$username}\n"
                . "- Password: {$randomPassword}\n\n"
                . "IMPORTANT: You are required to change your password upon first login.\n\n"
                . "You can now access the CHIKIN TAYO staff portal using the credentials above.\n\n"
                . "NEXT STEPS:\n"
                . "Our HR team will contact you shortly with the employment offer and onboarding details. "
                . "Please prepare the following documents:\n"
                . "- Valid ID (2 copies)\n"
                . "- SSS ID\n"
                . "- PhilHealth ID\n"
                . "- TIN ID\n"
                . "- NBI Clearance (if applicable)\n"
                . "- Medical Certificate\n\n"
                . "If you have any questions, please reply to this email or contact us at hr@chikintayo.com.\n\n"
                . "Welcome to the CHIKIN TAYO family!\n\n"
                . "Best regards,\n"
                . "CHIKIN TAYO HR Team\n"
                . "Application ID: {$application->id}\n"
                . "Applied on: {$submittedOn}";

            \send_raw_mail_notification(
                $application->applicant_email,
                'CHIKIN TAYO - Congratulations! You Passed the Interview',
                $message
            );

            \Illuminate\Support\Facades\Log::info('Applicant marked as passed and staff account created', [
                'application_id' => $application->id,
                'email' => $application->applicant_email,
                'staff_id' => $staff->id,
                'username' => $username,
            ]);

            return response()->json([
                'ok' => true,
                'message' => 'Applicant marked as passed. Staff account created and email notification sent to ' . $application->applicant_email,
                'staff' => [
                    'id' => $staff->id,
                    'username' => $username,
                    'email' => $staff->email,
                ]
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to mark applicant as passed', [
                'application_id' => $application->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Failed to mark applicant as passed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function markAsNotPassed(\Illuminate\Http\Request $request, $id): JsonResponse
    {
        $application = PositionApplication::findOrFail($id);

        try {
            $application->update(['status' => 'Not Passed']);

            // Send email notification to applicant
            $applicantName = $application->applicant_full_name ?? 'Applicant';
            $jobTitle = $application->job_title ?? 'the position';
            $submittedOn = \Carbon\Carbon::parse($application->created_at)->format('F j, Y g:i A');

            $message = "Dear {$applicantName},\n\n"
                . "Thank you for your interest in the {$jobTitle} position at CHIKIN TAYO and for taking the time to interview with us.\n\n"
                . "After careful consideration, we regret to inform you that you have not been selected for this position. "
                . "This decision was not easy, as we were impressed by your qualifications and experience.\n\n"
                . "We encourage you to apply for future openings that match your skills and experience. "
                . "We will keep your resume on file for 6 months and may contact you if a suitable position becomes available.\n\n"
                . "If you have any questions or would like feedback on your interview, please reply to this email or contact us at hr@chikintayo.com.\n\n"
                . "We wish you the best in your job search and future endeavors.\n\n"
                . "Best regards,\n"
                . "CHIKIN TAYO HR Team\n"
                . "Application ID: {$application->id}\n"
                . "Applied on: {$submittedOn}";

            \send_raw_mail_notification(
                $application->applicant_email,
                'CHIKIN TAYO - Update on Your Application',
                $message
            );

            \Illuminate\Support\Facades\Log::info('Applicant marked as not passed', [
                'application_id' => $application->id,
                'email' => $application->applicant_email,
            ]);

            return response()->json([
                'ok' => true,
                'message' => 'Applicant marked as not passed. Email notification sent to ' . $application->applicant_email,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to mark applicant as not passed', [
                'application_id' => $application->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Failed to mark applicant as not passed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function store(StorePositionApplicationRequest $request): JsonResponse
    {

        $data = $request->validated();

        // ✅ Prevent applications on positions that are already filled
        if (isset($data['position_open_request_id'])) {
            $openRequest = PositionOpenRequest::find((int) $data['position_open_request_id']);
            if (!$openRequest || $openRequest->status !== 'Approved' || (int) $openRequest->quantity <= 0) {
                return response()->json([
                    'ok' => false,
                    'message' => 'This position is no longer accepting applications.',
                ], 400);
            }
        }

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

