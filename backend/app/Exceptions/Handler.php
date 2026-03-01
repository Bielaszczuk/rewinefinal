<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = ['current_password', 'password', 'password_confirmation'];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {});
    }

    public function render($request, Throwable $e)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->renderApiError($request, $e);
        }

        return parent::render($request, $e);
    }

    private function renderApiError($request, Throwable $e)
    {
        $requestId = app()->bound('request-id') ? app('request-id') : null;
        $path      = $request->path();
        $timestamp = now()->toISOString();

        if ($e instanceof HttpException) {
            $content = $e->getMessage();
            $decoded = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($decoded['code'])) {
                return response()->json([
                    'timestamp' => $timestamp,
                    'path'      => $path,
                    'requestId' => $requestId,
                    'status'    => $e->getStatusCode(),
                    'code'      => $decoded['code'],
                    'message'   => $decoded['message'],
                    'details'   => [],
                ], $e->getStatusCode());
            }

            return response()->json([
                'timestamp' => $timestamp,
                'path'      => $path,
                'requestId' => $requestId,
                'status'    => $e->getStatusCode(),
                'code'      => 'E1000',
                'message'   => $e->getMessage() ?: 'HTTP Error',
                'details'   => [],
            ], $e->getStatusCode());
        }

        if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
            return response()->json([
                'timestamp' => $timestamp,
                'path'      => $path,
                'requestId' => $requestId,
                'status'    => 404,
                'code'      => 'E1002',
                'message'   => 'Resource not found',
                'details'   => [],
            ], 404);
        }

        if ($e instanceof ValidationException) {
            $details = [];
            foreach ($e->errors() as $field => $messages) {
                foreach ($messages as $msg) {
                    $details[] = ['field' => $field, 'message' => $msg];
                }
            }
            return response()->json([
                'timestamp' => $timestamp,
                'path'      => $path,
                'requestId' => $requestId,
                'status'    => 422,
                'code'      => 'E1001',
                'message'   => 'Validation failed',
                'details'   => $details,
            ], 422);
        }

        if ($e instanceof AuthenticationException) {
            return response()->json([
                'timestamp' => $timestamp,
                'path'      => $path,
                'requestId' => $requestId,
                'status'    => 401,
                'code'      => 'E2004',
                'message'   => 'Authentication required',
                'details'   => [],
            ], 401);
        }

        $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
        return response()->json([
            'timestamp' => $timestamp,
            'path'      => $path,
            'requestId' => $requestId,
            'status'    => $status,
            'code'      => 'E1000',
            'message'   => config('app.debug') ? $e->getMessage() : 'An unexpected error occurred',
            'details'   => [],
        ], $status);
    }
}
