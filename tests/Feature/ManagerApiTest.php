<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagerApiTest extends TestCase
{
    use RefreshDatabase;

    protected $manager;
    protected $branch;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a branch
        $this->branch = Branch::create([
            'name' => 'Test Branch',
            'code' => 'TEST',
            'address' => '123 Test St',
        ]);

        // Create a manager user
        $this->manager = User::create([
            'username' => 'manager_test',
            'email' => 'manager@test.com',
            'password_hash' => bcrypt('password123'),
            'full_name' => 'Test Manager',
            'role' => 'BRANCH_MANAGER',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
    }

    /**
     * Test Manager Dashboard API
     */
    public function test_manager_can_access_dashboard()
    {
        $response = $this->actingAs($this->manager)
            ->getJson('/api/manager/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'branch' => ['id', 'name', 'code', 'address'],
                'stats' => ['orders', 'completed', 'pending', 'sales'],
                'summary' => ['totalEmployees', 'activeStaff'],
                'recentOrders',
                'productionQueue',
                'staffActivity',
                'topProducts',
                'lowStockItems',
            ])
            ->assertJsonPath('success', true);
    }

    /**
     * Test Dashboard with date range filter
     */
    public function test_manager_dashboard_with_date_range()
    {
        $response = $this->actingAs($this->manager)
            ->getJson('/api/manager/dashboard?range=thisMonth');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    /**
     * Test Manager cannot access without authentication
     */
    public function test_unauthenticated_cannot_access_dashboard()
    {
        $response = $this->getJson('/api/manager/dashboard');

        $response->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    /**
     * Test Get Inventory
     */
    public function test_manager_can_get_inventory()
    {
        $response = $this->actingAs($this->manager)
            ->getJson('/api/manager/inventory');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'inventory' => [
                    '*' => ['id', 'item_name', 'category', 'quantity', 'unit', 'status']
                ],
                'summary' => ['total_items', 'low_stock', 'critical_stock'],
            ])
            ->assertJsonPath('success', true);
    }

    /**
     * Test Inventory Search
     */
    public function test_manager_can_search_inventory()
    {
        $response = $this->actingAs($this->manager)
            ->getJson('/api/manager/inventory?search=Chicken');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    /**
     * Test Inventory Status Filter
     */
    public function test_manager_can_filter_inventory_by_status()
    {
        $response = $this->actingAs($this->manager)
            ->getJson('/api/manager/inventory?status=critical');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    /**
     * Test Update Stock
     */
    public function test_manager_can_update_stock()
    {
        $response = $this->actingAs($this->manager)
            ->putJson('/api/manager/inventory/1', [
                'quantity' => 50,
                'action' => 'add',
                'note' => 'Restocked',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Stock updated successfully');
    }

    /**
     * Test Record Delivery
     */
    public function test_manager_can_record_delivery()
    {
        $response = $this->actingAs($this->manager)
            ->postJson('/api/manager/inventory/delivery', [
                'item_id' => 1,
                'quantity' => 100,
                'supplier' => 'Test Supplier',
                'note' => 'New delivery',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Delivery recorded successfully');
    }

    /**
     * Test Get Staff List
     */
    public function test_manager_can_get_staff_list()
    {
        // Create a staff member
        User::create([
            'username' => 'staff_test',
            'email' => 'staff@test.com',
            'password_hash' => bcrypt('password123'),
            'full_name' => 'Test Staff',
            'role' => 'STAFF',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->manager)
            ->getJson('/api/manager/staff');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'staff' => [
                    '*' => ['id', 'username', 'full_name', 'email', 'phone_number', 'is_active']
                ],
                'total',
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('total', 1);
    }

    /**
     * Test Add Staff
     */
    public function test_manager_can_add_staff()
    {
        $response = $this->actingAs($this->manager)
            ->postJson('/api/manager/staff', [
                'username' => 'newstaff',
                'email' => 'newstaff@test.com',
                'full_name' => 'New Staff Member',
                'phone_number' => '09123456789',
                'password' => 'password123',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Staff member created successfully');

        $this->assertDatabaseHas('users', [
            'username' => 'newstaff',
            'role' => 'STAFF',
            'branch_id' => $this->branch->id,
        ]);
    }

    /**
     * Test Update Staff
     */
    public function test_manager_can_update_staff()
    {
        $staff = User::create([
            'username' => 'staff_test',
            'email' => 'staff@test.com',
            'password_hash' => bcrypt('password123'),
            'full_name' => 'Test Staff',
            'role' => 'STAFF',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->manager)
            ->putJson("/api/manager/staff/{$staff->id}", [
                'full_name' => 'Updated Staff',
                'email' => 'updated@test.com',
                'phone_number' => '09987654321',
                'is_active' => true,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Staff updated successfully');

        $this->assertDatabaseHas('users', [
            'id' => $staff->id,
            'full_name' => 'Updated Staff',
        ]);
    }

    /**
     * Test Get Staff Schedules
     */
    public function test_manager_can_get_staff_schedules()
    {
        $response = $this->actingAs($this->manager)
            ->getJson('/api/manager/staff/schedules');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'schedules',
            ])
            ->assertJsonPath('success', true);
    }

    /**
     * Test Get Staff Attendance
     */
    public function test_manager_can_get_attendance()
    {
        $response = $this->actingAs($this->manager)
            ->getJson('/api/manager/staff/attendance');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'date',
                'attendance',
                'summary',
            ])
            ->assertJsonPath('success', true);
    }

    /**
     * Test Sales Report
     */
    public function test_manager_can_get_sales_report()
    {
        $response = $this->actingAs($this->manager)
            ->getJson('/api/manager/reports/sales');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'report' => [
                    'period',
                    'summary' => ['total_sales', 'total_orders', 'completed_orders'],
                    'daily_sales',
                ],
            ])
            ->assertJsonPath('success', true);
    }

    /**
     * Test Staff Performance Report
     */
    public function test_manager_can_get_performance_report()
    {
        $response = $this->actingAs($this->manager)
            ->getJson('/api/manager/reports/staff-performance');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'report' => ['total_staff', 'staff_performance'],
            ])
            ->assertJsonPath('success', true);
    }

    /**
     * Test Inventory Report
     */
    public function test_manager_can_get_inventory_report()
    {
        $response = $this->actingAs($this->manager)
            ->getJson('/api/manager/reports/inventory');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'report',
            ])
            ->assertJsonPath('success', true);
    }

    /**
     * Test Export CSV
     */
    public function test_manager_can_export_csv()
    {
        $response = $this->actingAs($this->manager)
            ->getJson('/api/manager/reports/export?type=sales');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    /**
     * Test Staff cannot access manager endpoints
     */
    public function test_staff_cannot_access_manager_endpoints()
    {
        $staff = User::create([
            'username' => 'staff_test',
            'email' => 'staff@test.com',
            'password_hash' => bcrypt('password123'),
            'full_name' => 'Test Staff',
            'role' => 'STAFF',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($staff)
            ->getJson('/api/manager/dashboard');

        $response->assertStatus(403)
            ->assertJsonPath('success', false);
    }
}
