<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\AuthController;
use App\Models\User;
use ReflectionMethod;
use Tests\TestCase;

class AuthRedirectPathTest extends TestCase
{
    public function test_combined_manager_roles_redirect_to_their_registered_panels(): void
    {
        $this->assertRedirects([
            'MANAGER_HR' => '/manager/hr',
            'MANAGER_FINANCE' => '/manager/finance',
            'MANAGER_LOGISTICS' => '/manager/logistics',
            'MANAGER_INVENTORY' => '/inventory',
            'MANAGER_PROCUREMENT' => '/manager/procurement',
            'BRANCH_MANAGER' => '/manager-panel',
            'SUPERADMIN' => '/super-admin/dashboard',
        ]);
    }

    private function assertRedirects(array $expectedPaths): void
    {
        $controller = new AuthController();
        $redirectPath = new ReflectionMethod($controller, 'getRedirectPath');
        $redirectPath->setAccessible(true);

        foreach ($expectedPaths as $role => $expectedPath) {
            $user = new User();
            $user->forceFill([
                'role' => $role,
                'department' => null,
                'branch_id' => null,
            ]);

            $this->assertSame($expectedPath, $redirectPath->invoke($controller, $user), $role);
        }
    }
}
