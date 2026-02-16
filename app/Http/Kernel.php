<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    // ... existing code ...

    /**
     * The application's route middleware aliases.
     */
  protected $middlewareAliases = [
    'auth' => \App\Http\Middleware\Authenticate::class,
    'admin' => \App\Http\Middleware\AdminMiddleware::class,
    'ensure.admin' => \App\Http\Middleware\EnsureAdmin::class,
    'no-cache' => \App\Http\Middleware\NoCache::class,
    // ... other middleware
];
}
