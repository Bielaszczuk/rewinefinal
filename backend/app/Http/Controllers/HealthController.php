<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    public function health(): JsonResponse
    {
        return response()->json([
            'status'    => 'UP',
            'timestamp' => now()->toISOString(),
            'version'   => config('app.version', '1.0.0'),
        ]);
    }

    public function version(): JsonResponse
    {
        return response()->json([
            'version'   => config('app.version', '1.0.0'),
            'name'      => config('app.name', 'Rewine'),
            'timestamp' => now()->toISOString(),
        ]);
    }
}
