<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
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
    }
}
