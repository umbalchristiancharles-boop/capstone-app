<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('messages', 'attachment_path')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->string('attachment_path')->nullable()->after('body');
            });
        }

        if (! Schema::hasColumn('messages', 'attachment_name')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->string('attachment_name')->nullable()->after('attachment_path');
            });
        }

        if (! Schema::hasColumn('messages', 'attachment_mime')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->string('attachment_mime')->nullable()->after('attachment_name');
            });
        }
    }

    public function down(): void
    {
        foreach (['attachment_path', 'attachment_name', 'attachment_mime'] as $column) {
            if (Schema::hasColumn('messages', $column)) {
                Schema::table('messages', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};