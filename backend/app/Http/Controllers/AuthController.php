<?php

namespace App\Http\Controllers;

use App\Application\Auth\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|min:3|max:50|regex:/^[a-zA-Z0-9_]+$/',
            'email'    => 'required|email|max:255',
            'password' => 'required|string|min:8|max:100',
            'name'     => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return $this->validationError($request, $validator->errors()->toArray());
        }

        $result = $this->authService->register(
            $request->only(['username', 'email', 'password', 'name']),
            $request->ip(),
            $request->userAgent() ?? ''
        );

        return response()->json($result, 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'usernameOrEmail' => 'required|string',
            'password'        => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationError($request, $validator->errors()->toArray());
        }

        $result = $this->authService->login(
            $request->input('usernameOrEmail'),
            $request->input('password'),
            $request->ip(),
            $request->userAgent() ?? ''
        );

        return response()->json($result);
    }

    public function refresh(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'refreshToken' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationError($request, $validator->errors()->toArray());
        }

        $result = $this->authService->refresh(
            $request->input('refreshToken'),
            $request->ip(),
            $request->userAgent() ?? ''
        );

        return response()->json($result);
    }

    public function logout(Request $request): JsonResponse
    {
        $refreshToken = $request->input('refreshToken');
        if ($refreshToken) {
            $this->authService->logout($refreshToken);
        }

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();
        $formatted = $this->authService->formatUser($user);
        $formatted['createdAt'] = $user->created_at?->toISOString();
        $formatted['updatedAt'] = $user->updated_at?->toISOString();

        return response()->json($formatted);
    }

    private function validationError(Request $request, array $errors): JsonResponse
    {
        $details = [];
        foreach ($errors as $field => $messages) {
            foreach ((array) $messages as $msg) {
                $details[] = ['field' => $field, 'message' => $msg];
            }
        }

        return response()->json([
            'timestamp' => now()->toISOString(),
            'path'      => $request->path(),
            'requestId' => app()->bound('request-id') ? app('request-id') : null,
            'status'    => 400,
            'code'      => 'E1001',
            'message'   => 'Validation failed',
            'details'   => $details,
        ], 400);
    }
}
