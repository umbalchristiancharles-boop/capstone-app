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
        //
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

        // Define no_cache_view helper globally so serialized route closures
        // that call it won't fail after route caching or serialization.
        if (! function_exists('no_cache_view')) {
            function no_cache_view($view)
            {
                return view($view);
            }
        }
    }
}
