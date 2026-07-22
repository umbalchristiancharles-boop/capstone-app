<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_communications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_report_id')->constrained()->cascadeOnDelete();
            $table->string('sender_email');
            $table->string('sender_name');
            $table->string('recipient_email');
            $table->string('recipient_name')->nullable();
            $table->string('subject');
            $table->text('message');
            $table->enum('direction', ['outbound', 'inbound'])->default('outbound');
            // outbound: sent by admin/staff to customer
            // inbound: sent by customer to admin/staff
            $table->enum('status', ['sent', 'failed', 'pending'])->default('sent');
            $table->text('error_message')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            // User who sent the email (null if sent by customer)
            $table->timestamps();

            $table->index(['customer_report_id', 'created_at']);
            $table->index(['customer_report_id', 'direction']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_communications');
    }
};