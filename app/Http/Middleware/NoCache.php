<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NoCache
{
    /**
     * Handle an incoming request and add no-cache headers to the response.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        try {
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        } catch (\Exception $e) {
            // ignore if response does not support header modifications
        }

        return $response;
    }
}
