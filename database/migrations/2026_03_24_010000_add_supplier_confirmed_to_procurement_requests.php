<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;

class AddSupplierConfirmedToProcurementRequests extends Migration
{
    public function up()
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            $table->boolean('supplier_confirmed')->default(false)->after('budget_approved');
        });
    }

    public function down()
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            $table->dropColumn('supplier_confirmed');
        });
    }
}
