<?php

namespace App\Providers;

use App\Http\Controllers\SuperAdminSandboxFileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $sessionConnectionName = config('session.connection') ?: config('database.default');
        $sessionConnection = config("database.connections.{$sessionConnectionName}");

        if (in_array($sessionConnection['driver'] ?? null, ['mysql', 'mariadb'], true)) {
            config([
                'database.connections.superadmin_session_live' => $sessionConnection,
                'session.connection' => 'superadmin_session_live',
            ]);
        }

        $helpers = app_path('Helpers/helpers.php');

        if (file_exists($helpers)) {
            require_once $helpers; // Load global helper functions once
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // NOTE: Do NOT exclude XSRF-TOKEN from encryption.
        // Laravel's CSRF middleware expects to decrypt the X-XSRF-TOKEN header.
        // The encrypted cookie value is readable by JS and sent in the header,
        // then decrypted server-side for comparison.
        $this->app->booted(function (): void {
            Route::get('/storage/{path}', [SuperAdminSandboxFileController::class, 'showStorage'])
                ->where('path', '.*')
                ->middleware('web')
                ->name('storage.public-sandbox');
        });
    }
}
