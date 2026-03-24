<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('dish_ingredients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dish_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('name');
            $table->string('quantity')->nullable();
            $table->string('unit')->nullable();
            $table->timestamps();

            $table->index('dish_id');
            $table->index('product_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('dish_ingredients');
    }
};
