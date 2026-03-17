<?php

// Global helper functions shared across web and API routes
if (!function_exists('no_cache_view')) {
    function no_cache_view($view)
    {
        // Wrap view responses with cache-busting headers to prevent back-button issues
        return response()->view($view)
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}

if (!function_exists('testHelper')) {
    function testHelper()
    {
        return 'Helper working';
    }
}
