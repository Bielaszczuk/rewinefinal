<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class JwtOptionalMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            if ($request->bearerToken()) {
                JWTAuth::parseToken()->authenticate();
            }
        } catch (JWTException $e) {
        }

        return $next($request);
    }
}
