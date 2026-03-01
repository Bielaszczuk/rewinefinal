<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class JwtAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return $this->unauthorized($request, 'User not found');
            }
        } catch (TokenExpiredException $e) {
            return $this->unauthorized($request, 'Token expired', 'E2006');
        } catch (TokenInvalidException $e) {
            return $this->unauthorized($request, 'Token invalid', 'E2004');
        } catch (JWTException $e) {
            return $this->unauthorized($request, 'Token absent', 'E2004');
        }

        return $next($request);
    }

    private function unauthorized(Request $request, string $message, string $code = 'E2004'): Response
    {
        return response()->json([
            'timestamp' => now()->toISOString(),
            'path'      => $request->path(),
            'requestId' => app()->bound('request-id') ? app('request-id') : null,
            'status'    => 401,
            'code'      => $code,
            'message'   => $message,
            'details'   => [],
        ], 401);
    }
}
