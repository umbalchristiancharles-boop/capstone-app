<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplier_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('procurement_request_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->integer('quantity')->default(0);
            $table->string('status')->default('pending');
            $table->timestamp('fulfilled_at')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->timestamps();

            // optional indexes
            $table->index('supplier_id');
            $table->index('procurement_request_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('supplier_orders');
    }
};
