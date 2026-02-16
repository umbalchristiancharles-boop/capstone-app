<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cookie\Middleware\EncryptCookies as EncryptCookiesMiddleware;

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
        // Ensure the XSRF-TOKEN cookie is not encrypted so front-end JS can read it.
        try {
            EncryptCookiesMiddleware::except(['XSRF-TOKEN']);
        } catch (\Throwable $__e) {
            // ignore if the middleware class isn't available for any reason
        }

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
