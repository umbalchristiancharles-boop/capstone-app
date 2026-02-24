<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code')->unique();   // ex: CT-0015
            $table->unsignedBigInteger('owner_id');   // id sa users table
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->enum('status', ['pending', 'in_kitchen', 'completed', 'cancelled']);
            $table->decimal('grand_total', 10, 2);
            $table->timestamp('ordered_at')->nullable();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
        public function down(): void
        {
            Schema::dropIfExists('orders');
        }

};
