<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = auth('api')->user();

        if (!$user) {
            return response()->json([
                'timestamp' => now()->toISOString(),
                'path'      => $request->path(),
                'requestId' => app()->bound('request-id') ? app('request-id') : null,
                'status'    => 401,
                'code'      => 'E2004',
                'message'   => 'Authentication required',
                'details'   => [],
            ], 401);
        }

        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }


        return response()->json([
            'timestamp' => now()->toISOString(),
            'path'      => $request->path(),
            'requestId' => app()->bound('request-id') ? app('request-id') : null,
            'status'    => 403,
            'code'      => 'E2005',
            'message'   => 'Insufficient permissions',
            'details'   => [],
        ], 403);
    }
}
