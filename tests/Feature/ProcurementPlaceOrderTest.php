<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Product;
use App\Models\ProcurementRequest;

class ProcurementPlaceOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_place_order_without_quantity_uses_request_quantity()
    {
        $user = User::factory()->create([
            'role' => 'PROCUREMENT_MANAGER',
            'department' => 'PROCUREMENT',
            'branch_id' => 1,
        ]);

        $product = Product::create([
            'name' => 'Test Product',
            'price' => 100.00,
            'stock' => 5,
            'branch_id' => 1,
            'is_active' => 1,
            'is_published' => 0,
            'supplier_id' => 50,
            'logistics_request_available' => true,
        ]);

        $proc = ProcurementRequest::create([
            'logistics_user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 7,
            'price' => 100.00,
            'total_amount' => 700.00,
            'status' => 'pending_order_to_supplier',
            'budget_approved' => true,
            'branch_id' => 1,
        ]);

        $this->actingAs($user);

        $response = $this->postJson("/api/procurement.products/{$product->id}/place-order", []);
        $response->assertStatus(200);

        $this->assertDatabaseHas('supplier_orders', [
            'procurement_request_id' => $proc->id,
            'product_id' => $product->id,
            'quantity' => 7,
            'branch_id' => 1,
        ]);

        $this->assertDatabaseHas('procurement_requests', [
            'id' => $proc->id,
            'procurement_user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'logistics_request_available' => 0,
        ]);
    }

    public function test_place_order_ignores_custom_quantity_and_uses_request_quantity()
    {
        $user = User::factory()->create([
            'role' => 'PROCUREMENT_MANAGER',
            'department' => 'PROCUREMENT',
            'branch_id' => 1,
        ]);

        $product = Product::create([
            'name' => 'Test Product 2',
            'price' => 50.00,
            'stock' => 2,
            'branch_id' => 1,
            'is_active' => 1,
            'is_published' => 0,
            'supplier_id' => 60,
            'logistics_request_available' => true,
        ]);

        $proc = ProcurementRequest::create([
            'logistics_user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'price' => 50.00,
            'total_amount' => 150.00,
            'status' => 'pending_order_to_supplier',
            'budget_approved' => true,
            'branch_id' => 1,
            'supplier_confirmed' => true,
        ]);

        $this->actingAs($user);

        // Try to send a custom quantity of 5, but it should be ignored
        $payload = ['quantity' => 5];
        $response = $this->postJson("/api/procurement.products/{$product->id}/place-order", $payload);
        $response->assertStatus(200);

        // The supplier order should use the procurement request quantity (3), not the custom quantity (5)
        $this->assertDatabaseHas('supplier_orders', [
            'procurement_request_id' => $proc->id,
            'product_id' => $product->id,
            'quantity' => 3,  // Should use the procurement request quantity, not custom 5
            'branch_id' => 1,
        ]);

        $this->assertDatabaseHas('procurement_requests', [
            'id' => $proc->id,
            'procurement_user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'logistics_request_available' => 0,
        ]);
    }
}
