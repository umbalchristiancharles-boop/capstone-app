<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_communications', function (Blueprint $table) {
            $table->string('message_id')->nullable()->after('status');
            $table->string('in_reply_to')->nullable()->after('message_id');
            $table->text('references')->nullable()->after('in_reply_to');
            
            $table->index(['message_id']);
            $table->index(['in_reply_to']);
        });
    }

    public function down(): void
    {
        Schema::table('email_communications', function (Blueprint $table) {
            $table->dropIndex(['message_id']);
            $table->dropIndex(['in_reply_to']);
            $table->dropColumn(['message_id', 'in_reply_to', 'references']);
        });
    }
};