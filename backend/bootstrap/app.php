<?php

use App\Exceptions\Handler;
use App\Http\Middleware\JwtAuthMiddleware;
use App\Http\Middleware\JwtOptionalMiddleware;
use App\Http\Middleware\RequestIdMiddleware;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api/v1',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(RequestIdMiddleware::class);
        $middleware->api(append: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
        $middleware->alias([
            'jwt.auth'     => JwtAuthMiddleware::class,
            'jwt.optional' => JwtOptionalMiddleware::class,
            'role'         => RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Render all exceptions as JSON for API requests
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            // Only intercept API requests
            if (!$request->is('api/*') && !$request->expectsJson()) {
                return null;
            }

            $requestId = app()->bound('request-id') ? app('request-id') : null;
            $path      = $request->path();

            // HttpException – thrown via abort()
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                $status  = $e->getStatusCode();
                $payload = json_decode($e->getMessage(), true);
                if (is_array($payload) && isset($payload['code'])) {
                    return response()->json([
                        'timestamp' => now()->toISOString(),
                        'path'      => $path,
                        'requestId' => $requestId,
                        'status'    => $status,
                        'code'      => $payload['code'],
                        'message'   => $payload['message'] ?? $e->getMessage(),
                        'details'   => $payload['details'] ?? [],
                    ], $status);
                }
                return response()->json([
                    'timestamp' => now()->toISOString(),
                    'path'      => $path,
                    'requestId' => $requestId,
                    'status'    => $status,
                    'code'      => 'E' . $status,
                    'message'   => $e->getMessage() ?: 'HTTP Error',
                    'details'   => [],
                ], $status);
            }

            // ValidationException – thrown by $request->validate()
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                $details = [];
                foreach ($e->errors() as $field => $messages) {
                    foreach ($messages as $msg) {
                        $details[] = ['field' => $field, 'message' => $msg];
                    }
                }
                return response()->json([
                    'timestamp' => now()->toISOString(),
                    'path'      => $path,
                    'requestId' => $requestId,
                    'status'    => 422,
                    'code'      => 'E1001',
                    'message'   => 'Validation failed',
                    'details'   => $details,
                ], 422);
            }

            // ModelNotFoundException – 404 on route model binding
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return response()->json([
                    'timestamp' => now()->toISOString(),
                    'path'      => $path,
                    'requestId' => $requestId,
                    'status'    => 404,
                    'code'      => 'E4004',
                    'message'   => 'Resource not found',
                    'details'   => [],
                ], 404);
            }

            // AuthenticationException
            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                return response()->json([
                    'timestamp' => now()->toISOString(),
                    'path'      => $path,
                    'requestId' => $requestId,
                    'status'    => 401,
                    'code'      => 'E2004',
                    'message'   => 'Unauthenticated',
                    'details'   => [],
                ], 401);
            }

            // Generic server error (only in production hide details)
            return null; // let Laravel default handle (Ignition in dev)
        });
    })->create();
