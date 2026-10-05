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
}
