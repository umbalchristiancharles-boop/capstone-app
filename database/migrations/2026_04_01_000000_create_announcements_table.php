<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This creates the announcements table to store announcements sent by Super Admin
     */
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');           // Announcement title
            $table->text('message');           // Announcement content
            $table->string('target');          // Target: 'all', 'staff', 'managers'
            $table->unsignedBigInteger('sender_id'); // Super Admin user ID
            $table->timestamps();

            // Foreign key to users table
            $table->foreign('sender_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            // Index for efficient querying
            $table->index('target');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};

