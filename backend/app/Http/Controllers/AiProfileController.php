<?php

namespace App\Http\Controllers;

use App\Application\Ai\Services\AiProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class AiProfileController extends Controller
{
    public function __construct(private readonly AiProfileService $aiService) {}

    public function show(Request $request, string $wineId): JsonResponse
    {
        $language = $request->query('language', 'es-AR');
        $result   = $this->aiService->getOrGenerate($wineId, $language);
        return response()->json($result);
    }

    public function generate(Request $request, string $wineId): JsonResponse
    {
        $language        = $request->input('language', 'es-AR');
        $forceRegenerate = filter_var($request->input('forceRegenerate', false), FILTER_VALIDATE_BOOLEAN);

        $result = $forceRegenerate
            ? $this->aiService->forceRegenerate($wineId, $language)
            : $this->aiService->getOrGenerate($wineId, $language);

        return response()->json($result);
    }

    public function status(Request $request, string $wineId): JsonResponse
    {
        $language = $request->query('language', 'es-AR');
        return response()->json($this->aiService->getStatus($wineId, $language));
    }
}
