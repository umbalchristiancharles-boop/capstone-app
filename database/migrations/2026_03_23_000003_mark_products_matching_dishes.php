<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Mark products as kitchen dish if their name matches a Dish record (same branch)
        $sql = "UPDATE products p
                JOIN dishes d ON p.branch_id = d.branch_id AND TRIM(UPPER(p.name)) = TRIM(UPPER(d.name))
                SET p.is_kitchen_dish = 1";
        DB::statement($sql);
    }

    public function down()
    {
        $sql = "UPDATE products p
                JOIN dishes d ON p.branch_id = d.branch_id AND TRIM(UPPER(p.name)) = TRIM(UPPER(d.name))
                SET p.is_kitchen_dish = 0";
        DB::statement($sql);
    }
};
