<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Your DB already contains this table (you said it already exists).
        // Make this migration safe/idempotent.
        if (Schema::hasTable('position_open_requests')) {
            return;
        }

        Schema::create('position_open_requests', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('position_id');
            $table->unsignedBigInteger('requested_by_user_id');


            $table->unsignedInteger('quantity');
            $table->text('notes')->nullable();

            $table->enum('status', ['Pending', 'Approved', 'Rejected'])
                ->default('Pending');

            $table->timestamps();

            $table->foreign('position_id')->references('id')->on('positions');
            $table->foreign('requested_by_user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('position_open_requests');
    }
};

?>
