<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Support\Permission;

class EnsurePermission
{
    /**
     * Middleware signature: permission:module1,module2,fn:function.a,fn:function.b
     * All non fn: params are treated as modules; fn: prefix treated as functions.
     */
    public function handle(Request $request, Closure $next, ...$params): Response
    {
        // Support both session-based and token-based (Sanctum) authentication
        $user = null;
        try {
            $user = auth('sanctum')->user() ?? Auth::user();
        } catch (\Throwable $e) {
            $user = Auth::user();
        }

        $modules = [];
        $functions = [];
        foreach ($params as $p) {
            if (str_starts_with($p, 'fn:')) {
                $functions[] = substr($p, 3);
            } else {
                $modules[] = $p;
            }
        }

        // Allow privileged administrative roles by default when modules/functions are required
        $privilegedRoles = ['SUPER_ADMIN', 'SUPERADMIN', 'OWNER', 'ADMIN'];
        if (! Permission::allowed($user, $privilegedRoles, $modules, $functions)) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => 'Forbidden'], 403);
            }
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
