<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('resume_path');
            $table->string('government_id_path');
            $table->string('psa_birth_certificate_path');
            $table->string('nbi_clearance_path');
            $table->string('police_clearance_path');
            $table->string('medical_certificate_path');
            $table->string('drug_test_result_path');
            $table->string('sss_id_path');
            $table->string('philhealth_id_path');
            $table->string('pagibig_mdf_path');
            $table->string('tin_id_path');
            $table->string('diploma_transcript_path');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_documents');
    }
};
