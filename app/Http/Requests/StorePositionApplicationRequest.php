<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePositionApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Job context
            'position_open_request_id' => ['required', 'integer', 'exists:position_open_requests,id'],
            'position_id' => ['required', 'integer', 'exists:positions,id'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'department' => ['nullable', 'string', 'max:255'],
            'job_title' => ['required', 'string', 'max:255'],

            // Honeypot
            'website' => ['nullable', 'string', 'max:255'],

            // Applicant details
            'full_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:1000'],
            'cover_letter' => ['required', 'string', 'max:5000'],
            'years_of_experience' => ['required', 'numeric', 'integer', 'min:0', 'max:80'],
            'education' => ['required', 'string', 'max:255'],
            'available_start_date' => ['required', 'date'],

            // Optional
            'linkedin_url' => ['nullable', 'url', 'max:500'],
            'portfolio_url' => ['nullable', 'url', 'max:500'],

            // Privacy
            'privacy_consent' => ['required', 'accepted'],

            // Files
            'resume_cv' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'supporting_documents' => ['nullable', 'array'],
            'supporting_documents.*' => ['file', 'mimes:pdf,doc,docx', 'max:10240'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Honeypot: if filled, consider it spam.
            $website = $this->input('website');
            if (!empty($website)) {
                $validator->errors()->add('website', 'Spam detected.');
            }
        });
    }

    public function attributes(): array
    {
        return [
            'resume_cv' => 'Resume/CV',
            'supporting_documents' => 'Supporting Documents',
            'privacy_consent' => 'Data privacy consent',
        ];
    }
}

