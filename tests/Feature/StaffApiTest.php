<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffApiTest extends TestCase
{
    use RefreshDatabase;

    protected $staff;
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

        // Create a staff user
        $this->staff = User::create([
            'username' => 'staff_test',
            'email' => 'staff@test.com',
            'password_hash' => bcrypt('password123'),
            'full_name' => 'Test Staff',
            'role' => 'STAFF',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
    }

    /**
     * Test Staff Dashboard API
     */
    public function test_staff_can_access_dashboard()
    {
        $response = $this->actingAs($this->staff)
            ->getJson('/api/staff/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'staff' => ['id', 'full_name', 'username', 'email'],
                'branch' => ['id', 'name', 'code'],
                'stats' => ['orders', 'completed', 'pending', 'sales'],
                'recentOrders',
                'myTasks',
                'performance',
                'notifications',
            ])
            ->assertJsonPath('success', true);
    }

    /**
     * Test Staff cannot access unauthenticated
     */
    public function test_unauthenticated_cannot_access_dashboard()
    {
        $response = $this->getJson('/api/staff/dashboard');

        $response->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    /**
     * Test Clock In
     */
    public function test_staff_can_clock_in()
    {
        $response = $this->actingAs($this->staff)
            ->postJson('/api/staff/clock-in');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['staff_id', 'staff_name', 'clock_in_time', 'date'],
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Clocked in successfully');
    }

    /**
     * Test Clock Out
     */
    public function test_staff_can_clock_out()
    {
        $response = $this->actingAs($this->staff)
            ->postJson('/api/staff/clock-out');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['staff_id', 'staff_name', 'clock_out_time', 'hours_worked'],
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Clocked out successfully');
    }

    /**
     * Test Get Attendance Status
     */
    public function test_staff_can_get_attendance_status()
    {
        $response = $this->actingAs($this->staff)
            ->getJson('/api/staff/attendance/status');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'status' => [
                    'is_clocked_in',
                    'clock_in_time',
                    'clock_out_time',
                    'hours_worked',
                    'shift_start',
                    'shift_end',
                    'date',
                ],
            ])
            ->assertJsonPath('success', true);
    }

    /**
     * Test Get Attendance History
     */
    public function test_staff_can_get_attendance_history()
    {
        $response = $this->actingAs($this->staff)
            ->getJson('/api/staff/attendance/history');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'history' => [
                    '*' => ['date', 'clock_in', 'clock_out', 'hours_worked', 'status']
                ],
                'total',
            ])
            ->assertJsonPath('success', true);
    }

    /**
     * Test Get Attendance History with limit
     */
    public function test_staff_can_get_attendance_history_with_limit()
    {
        $response = $this->actingAs($this->staff)
            ->getJson('/api/staff/attendance/history?limit=5');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    /**
     * Test Manager cannot access staff endpoints
     */
    public function test_manager_cannot_access_staff_endpoints()
    {
        $manager = User::create([
            'username' => 'manager_test',
            'email' => 'manager@test.com',
            'password_hash' => bcrypt('password123'),
            'full_name' => 'Test Manager',
            'role' => 'BRANCH_MANAGER',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($manager)
            ->getJson('/api/staff/dashboard');

        $response->assertStatus(403)
            ->assertJsonPath('success', false);
    }

    /**
     * Test Staff from different branch cannot access
     */
    public function test_staff_without_branch_cannot_access()
    {
        $staffNoBranch = User::create([
            'username' => 'staff_no_branch',
            'email' => 'staffnobranch@test.com',
            'password_hash' => bcrypt('password123'),
            'full_name' => 'Staff No Branch',
            'role' => 'STAFF',
            'branch_id' => null,
            'is_active' => true,
        ]);

        $response = $this->actingAs($staffNoBranch)
            ->getJson('/api/staff/dashboard');

        $response->assertStatus(400)
            ->assertJsonPath('success', false);
    }
}
