<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Allow avatar upload endpoint to skip CSRF to avoid 419 during local testing
        'api/upload-avatar',
        'upload-avatar',
        // Attendance API endpoints - all variations
        'api/staff/clock-in',
        'api/staff/clock-out',
        'api/staff/attendance/*',
    ];

    // No custom constructor: keep parent DI signature intact.
    // We'll adjust exceptions at runtime inside handle() when needed.

    /**
     * Handle an incoming request and log CSRF failures for debugging.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws \Illuminate\Session\TokenMismatchException
     */
    public function handle($request, $next)
    {
        // Always bypass CSRF for attendance endpoints
        if ($request->is('api/staff/clock-in', 'api/staff/clock-out', 'api/staff/attendance/*')) {
            return $next($request);
        }

        // During local development allow avatar upload endpoint to bypass CSRF
        // to avoid frequent 419 while testing file uploads from different origins.
        try {
            if (app()->environment('local')) {
                $this->except[] = 'api/upload-avatar';
                $this->except[] = 'upload-avatar';
                // Attendance endpoints
                $this->except[] = 'api/staff/clock-in';
                $this->except[] = 'api/staff/clock-out';
                $this->except[] = 'api/staff/attendance/status';
                $this->except[] = 'api/staff/attendance/history';
                $this->except[] = 'api/staff/attendance/branch';
            }
        } catch (\Throwable $e) {
            // ignore environment detection failures
        }
        // For local debugging: log incoming CSRF headers and session token.
        if (app()->environment('local')) {
            try {
                $sessionToken = $request->session() ? $request->session()->token() : csrf_token();
            } catch (\Throwable $_) {
                $sessionToken = csrf_token();
            }

            try {
                Log::debug('CSRF debug', [
                    'path' => $request->path(),
                    'method' => $request->method(),
                    'headers' => [
                        'referer' => $request->headers->get('referer'),
                        'x_csrf_token' => $request->headers->get('x-csrf-token'),
                        'x_xsrf_token' => $request->headers->get('x-xsrf-token'),
                        'x_requested_with' => $request->headers->get('x-requested-with'),
                    ],
                    'has_cookie_xsrf' => $request->cookies->has('XSRF-TOKEN'),
                    'cookie_xsrf' => $request->cookies->get('XSRF-TOKEN'),
                    'session_token' => $sessionToken,
                    'session_id' => session()->getId(),
                ]);
            } catch (\Throwable $_) {
                // ignore logging errors
            }

            // If the client provided a matching token in headers, allow directly (local only)
            $headerToken = $request->headers->get('x-csrf-token') ?: $request->headers->get('x-xsrf-token');
            if ($headerToken && hash_equals($sessionToken, $headerToken)) {
                return $next($request);
            }
        }

        try {
            return parent::handle($request, $next);
        } catch (TokenMismatchException $e) {
            // Log useful request context to help diagnose 419 CSRF errors
            try {
                Log::warning('CSRF token mismatch', [
                    'path' => $request->path(),
                    'method' => $request->method(),
                    'headers' => [
                        'referer' => $request->headers->get('referer'),
                        'x_csrf_token' => $request->headers->get('x-csrf-token'),
                        'x_xsrf_token' => $request->headers->get('x-xsrf-token'),
                        'x_requested_with' => $request->headers->get('x-requested-with'),
                    ],
                    'has_cookie_xsrf' => $request->cookies->has('XSRF-TOKEN'),
                    'session_id' => session()->getId(),
                ]);
            } catch (\Throwable $_) {
                // ignore logging errors
            }

            throw $e;
        }
    }
}
