<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('position_applications')) {
            return;
        }

        Schema::create('position_applications', function (Blueprint $table) {
            $table->id();

            // Snapshot of the job the applicant is applying to
            $table->unsignedBigInteger('position_open_request_id');
            $table->unsignedBigInteger('position_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('department')->nullable();
            $table->string('job_title');

            // Applicant details
            $table->string('applicant_full_name');
            $table->string('applicant_email');
            $table->string('applicant_phone');
            $table->text('applicant_address');
            $table->text('cover_letter');
            $table->unsignedInteger('years_of_experience');
            $table->string('education');
            $table->date('available_start_date');

            // Optional fields
            $table->string('linkedin_url')->nullable();
            $table->string('portfolio_url')->nullable();

            // Privacy + anti-spam
            $table->boolean('privacy_consent')->default(false);
            $table->string('website')->nullable(); // honeypot

            // Uploads
            $table->string('resume_path');
            $table->json('supporting_documents_paths')->nullable();

            $table->string('status')->default('Submitted');

            $table->timestamps();

            $table->foreign('position_open_request_id')
                ->references('id')
                ->on('position_open_requests')
                ->cascadeOnDelete();

            $table->foreign('position_id')
                ->references('id')
                ->on('positions')
                ->cascadeOnDelete();

            $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('position_applications');
    }
};

