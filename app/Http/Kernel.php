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
  'super.admin' => \App\Http\Middleware\SuperAdminOnly::class,
    'ensure.admin' => \App\Http\Middleware\EnsureAdmin::class,
    'no-cache' => \App\Http\Middleware\NoCache::class,
    'owner.only' => \App\Http\Middleware\OwnerOnly::class,
    'permission' => \App\Http\Middleware\EnsurePermission::class,

    // Ensure SPA stateful requests are recognized by Sanctum when using API middleware
    'ensure.frontend' => \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    // ... other middleware
  ];
}
