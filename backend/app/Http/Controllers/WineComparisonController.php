<?php

namespace App\Http\Controllers;

use App\Http\Requests\Comparison\CompareWinesRequest;
use App\Http\Resources\Comparison\WineComparisonResource;
use App\Application\Wine\Services\WineComparisonService;
use Illuminate\Http\JsonResponse;

class WineComparisonController extends Controller
{
    public function __construct(
        private readonly WineComparisonService $comparisonService
    ) {}

    /**
     * Compare two wines.
     */
    public function compare(CompareWinesRequest $request)
    {
        $comparison = $this->comparisonService->compare(
            $request->getWineAId(),
            $request->getWineBId(),
            $request->getLanguage()
        );

        return WineComparisonResource::make($comparison);
    }
}
