<?php

namespace Tests\Unit;

use App\Services\SuperAdminSandbox;
use RuntimeException;
use Tests\TestCase;

class SuperAdminSandboxTest extends TestCase
{
    public function test_precreated_sandbox_cannot_be_the_live_database(): void
    {
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.driver' => 'mysql',
            'database.connections.mysql.database' => 'live_database',
            'app.superadmin_sandbox_database' => 'live_database',
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The sandbox database must be different from the live database.');

        app(SuperAdminSandbox::class)->createDatabase(1, 'session');
    }

    public function test_precreated_sandbox_name_must_be_a_safe_identifier(): void
    {
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.driver' => 'mysql',
            'database.connections.mysql.database' => 'live_database',
            'app.superadmin_sandbox_database' => 'sandbox-database',
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The configured pre-created sandbox database name is invalid.');

        app(SuperAdminSandbox::class)->createDatabase(1, 'session');
    }

    public function test_precreated_sandbox_database_cannot_be_dropped(): void
    {
        config(['app.superadmin_sandbox_database' => 'account_sandbox']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Refusing to drop the configured pre-created sandbox database.');

        app(SuperAdminSandbox::class)->dropDatabase('account_sandbox');
    }

    public function test_sandbox_activation_cannot_switch_to_the_live_database(): void
    {
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'live_database',
            'app.superadmin_sandbox_database' => 'live_database',
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The sandbox database must be different from the live database.');

        app(SuperAdminSandbox::class)->activate('live_database');
    }

    public function test_sandbox_activation_uses_and_restores_the_dedicated_connection_credentials(): void
    {
        config([
            'database.default' => 'mysql',
            'database.connections.mysql' => [
                'driver' => 'mysql',
                'database' => 'live_database',
                'username' => 'live_user',
                'password' => 'live_password',
            ],
            'database.connections.superadmin_sandbox' => [
                'driver' => 'mysql',
                'database' => 'sandbox_database',
                'username' => 'sandbox_user',
                'password' => 'sandbox_password',
            ],
            'app.superadmin_sandbox_database' => 'sandbox_database',
        ]);

        $sandbox = app(SuperAdminSandbox::class);
        $connectionState = $sandbox->activate('sandbox_database');

        $this->assertSame('sandbox_database', config('database.connections.mysql.database'));
        $this->assertSame('sandbox_user', config('database.connections.mysql.username'));
        $this->assertSame('sandbox_password', config('database.connections.mysql.password'));

        $sandbox->restore($connectionState);

        $this->assertSame('live_database', config('database.connections.mysql.database'));
        $this->assertSame('live_user', config('database.connections.mysql.username'));
        $this->assertSame('live_password', config('database.connections.mysql.password'));
    }
}
