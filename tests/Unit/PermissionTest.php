<?php

namespace Tests\Unit;

use App\Models\User;
use App\Support\Permission;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    public function test_super_admin_can_pass_module_and_function_permission_checks(): void
    {
        foreach (['SUPER_ADMIN', 'SUPERADMIN'] as $role) {
            $user = new User();
            $user->forceFill([
                'id' => 1,
                'role' => $role,
                'permissions' => [],
            ]);

            $this->assertTrue(Permission::allowed($user, ['MANAGER'], ['finance'], ['finance.approve']));
        }
    }

    public function test_non_super_admin_still_requires_the_existing_permission(): void
    {
        $user = new User();
        $user->forceFill([
            'id' => 2,
            'role' => 'STAFF',
            'department' => 'KITCHEN',
            'permissions' => [],
        ]);

        $this->assertFalse(Permission::allowed($user, ['MANAGER'], ['finance'], ['finance.approve']));
    }

    public function test_assigned_permissions_can_extend_any_account_role(): void
    {
        $user = new User();
        $user->forceFill([
            'id' => 3,
            'role' => 'STAFF',
            'permissions' => [
                'modules' => ['Inventory'],
                'functions' => ['reports.sales'],
            ],
        ]);

        $this->assertTrue(Permission::allowed($user, [], ['inventory']));
        $this->assertTrue(Permission::allowed($user, [], [], ['REPORTS.SALES']));
    }

    public function test_assigned_permissions_do_not_replace_role_checks_without_a_match(): void
    {
        $user = new User();
        $user->forceFill([
            'id' => 4,
            'role' => 'STAFF',
            'permissions' => [
                'modules' => ['inventory'],
                'functions' => [],
            ],
        ]);

        $this->assertFalse(Permission::allowed($user, ['MANAGER'], ['finance']));
    }
}
