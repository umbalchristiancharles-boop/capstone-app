<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance', function (Blueprint $table) {
            $table->boolean('confirmed')->default(false)->after('face_image');
            $table->unsignedBigInteger('confirmed_by')->nullable()->after('confirmed');
            $table->timestamp('confirmed_at')->nullable()->after('confirmed_by');
            
            // Add foreign key constraint
            $table->foreign('confirmed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('attendance', function (Blueprint $table) {
            $table->dropForeign(['confirmed_by']);
            $table->dropColumn(['confirmed', 'confirmed_by', 'confirmed_at']);
        });
    }
};