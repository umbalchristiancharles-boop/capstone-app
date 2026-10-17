<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\AuthController;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class CustomAccountRoleIsolationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('role');
            $table->string('department')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->json('permissions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('must_change_password')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function test_supplier_with_module_permissions_keeps_supplier_login_destination(): void
    {
        $supplier = new User([
            'role' => 'SUPPLIER',
            'permissions' => ['modules' => ['supplier-products']],
        ]);

        $this->assertSame('/supplier-panel', $this->redirectPathFor($supplier));
    }

    public function test_custom_account_uses_custom_panel_and_other_roles_keep_their_panel(): void
    {
        $custom = new User([
            'role' => 'CUSTOM',
            'permissions' => ['modules' => ['supplier-products']],
        ]);
        $manager = new User([
            'role' => 'MANAGER',
            'department' => 'FINANCE',
            'permissions' => ['modules' => ['supplier-products']],
        ]);

        $this->assertSame('/custom-panel', $this->redirectPathFor($custom));
        $this->assertSame('/manager/finance', $this->redirectPathFor($manager));
    }

    public function test_custom_account_permission_tools_exclude_and_cannot_modify_suppliers(): void
    {
        $admin = User::create([
            'username' => 'branch-admin',
            'password' => 'password',
            'role' => 'ADMIN',
        ]);
        $staff = User::create([
            'username' => 'branch-staff',
            'password' => 'password',
            'role' => 'STAFF',
        ]);
        $supplier = User::create([
            'username' => 'vendor-account',
            'password' => 'password',
            'role' => 'SUPPLIER',
            'permissions' => ['modules' => ['supplier-products']],
        ]);

        $this->actingAs($admin)
            ->getJson('/api/admin/custom-accounts')
            ->assertOk()
            ->assertJsonCount(1, 'accounts')
            ->assertJsonPath('accounts.0.id', $staff->id);

        $this->actingAs($admin)
            ->putJson("/api/admin/custom-accounts/{$supplier->id}", [
                'is_custom' => true,
                'role' => 'CUSTOM',
                'department' => null,
                'modules' => ['recent-transactions'],
            ])
            ->assertNotFound();

        $this->assertSame('SUPPLIER', $supplier->fresh()->role);
        $this->assertSame(['modules' => ['supplier-products']], $supplier->fresh()->permissions);
    }

    private function redirectPathFor(User $user): string
    {
        $method = new ReflectionMethod(AuthController::class, 'getRedirectPath');
        return $method->invoke(new AuthController(), $user);
    }
}
