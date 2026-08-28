<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class ProductSupplierTransferTest extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('role')->default('SUPPLIER');
            $table->string('full_name')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->unsignedBigInteger('branch_id')->default(1);
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('supplier_name')->nullable();
            $table->integer('stock')->default(0);
            $table->integer('real_stock')->default(0);
            $table->timestamps();
        });
    }

    public function test_supplier_change_transfers_stock_and_real_stock_to_target_supplier_product(): void
    {
        $supplier = User::create([
            'role' => 'SUPPLIER',
            'full_name' => 'Umberto Batumbakal',
            'branch_id' => 31,
        ]);

        $source = Product::create([
            'name' => 'water',
            'sku' => 'SKU-1',
            'branch_id' => 31,
            'supplier_id' => 99,
            'supplier_name' => 'Old Supplier',
            'stock' => 10,
            'real_stock' => 10,
        ]);

        $destination = Product::create([
            'name' => 'water',
            'sku' => 'SKU-1',
            'branch_id' => 31,
            'supplier_id' => 98,
            'supplier_name' => 'Other Supplier',
            'stock' => 5,
            'real_stock' => 5,
        ]);

        $result = Product::transferInventoryForSupplierChange($source, $supplier, $destination);

        $this->assertSame(15, $result->stock);
        $this->assertSame(15, $result->real_stock);
        $this->assertSame(0, $source->fresh()->stock);
        $this->assertSame(0, $source->fresh()->real_stock);
        $this->assertSame($supplier->id, $result->supplier_id);
        $this->assertSame(99, $source->fresh()->supplier_id);
        $this->assertSame('Old Supplier', $source->fresh()->supplier_name);
        $this->assertSame($supplier->id, $destination->fresh()->supplier_id);
    }

    public function test_supplier_change_keeps_source_when_supplier_is_already_assigned(): void
    {
        $supplier = User::create([
            'role' => 'SUPPLIER',
            'full_name' => 'Umberto Batumbakal',
            'branch_id' => 31,
        ]);

        $source = Product::create([
            'name' => 'Frozen Hotdog',
            'sku' => 'SKU-1',
            'branch_id' => 31,
            'supplier_id' => $supplier->id,
            'supplier_name' => 'Old Supplier Name',
            'stock' => 10,
            'real_stock' => 10,
        ]);

        $result = Product::transferInventoryForSupplierChange($source, $supplier);

        $this->assertSame($source->id, $result->id);
        $this->assertSame(10, $result->stock);
        $this->assertSame(10, $result->real_stock);
        $this->assertSame('Umberto Batumbakal', $result->supplier_name);
    }

    public function test_manager_change_supplier_route_accepts_post_requests(): void
    {
        $route = Route::getRoutes()->match(Request::create('/api/manager/procurement/products/1/change-supplier', 'POST'));

        $this->assertContains('POST', $route->methods());
    }
}
