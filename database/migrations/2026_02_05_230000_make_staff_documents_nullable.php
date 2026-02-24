<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE staff_documents MODIFY resume_path VARCHAR(255) NULL");
        DB::statement("ALTER TABLE staff_documents MODIFY government_id_path VARCHAR(255) NULL");
        DB::statement("ALTER TABLE staff_documents MODIFY psa_birth_certificate_path VARCHAR(255) NULL");
        DB::statement("ALTER TABLE staff_documents MODIFY nbi_clearance_path VARCHAR(255) NULL");
        DB::statement("ALTER TABLE staff_documents MODIFY police_clearance_path VARCHAR(255) NULL");
        DB::statement("ALTER TABLE staff_documents MODIFY medical_certificate_path VARCHAR(255) NULL");
        DB::statement("ALTER TABLE staff_documents MODIFY drug_test_result_path VARCHAR(255) NULL");
        DB::statement("ALTER TABLE staff_documents MODIFY sss_id_path VARCHAR(255) NULL");
        DB::statement("ALTER TABLE staff_documents MODIFY philhealth_id_path VARCHAR(255) NULL");
        DB::statement("ALTER TABLE staff_documents MODIFY pagibig_mdf_path VARCHAR(255) NULL");
        DB::statement("ALTER TABLE staff_documents MODIFY tin_id_path VARCHAR(255) NULL");
        DB::statement("ALTER TABLE staff_documents MODIFY diploma_transcript_path VARCHAR(255) NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE staff_documents MODIFY resume_path VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE staff_documents MODIFY government_id_path VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE staff_documents MODIFY psa_birth_certificate_path VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE staff_documents MODIFY nbi_clearance_path VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE staff_documents MODIFY police_clearance_path VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE staff_documents MODIFY medical_certificate_path VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE staff_documents MODIFY drug_test_result_path VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE staff_documents MODIFY sss_id_path VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE staff_documents MODIFY philhealth_id_path VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE staff_documents MODIFY pagibig_mdf_path VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE staff_documents MODIFY tin_id_path VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE staff_documents MODIFY diploma_transcript_path VARCHAR(255) NOT NULL");
    }
};
