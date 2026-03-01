<?php

namespace App\Http\Controllers;

use App\Application\Cellar\Services\CellarQueryService;
use App\Application\Cellar\Services\CellarCommandService;
use App\Models\CellarWine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class CellarController extends Controller
{
    public function __construct(
        private readonly CellarQueryService $queryService,
        private readonly CellarCommandService $commandService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $userId = JWTAuth::parseToken()->authenticate()->id;
        $entries = $this->queryService->getCellar($userId);
        return response()->json($entries->map(fn($e) => $this->format($e))->values()->all());
    }

    public function store(Request $request): JsonResponse
    {
        $userId = JWTAuth::parseToken()->authenticate()->id;

        $validated = $request->validate([
            'wineId'        => 'required|uuid|exists:wines,id',
            'quantity'      => 'nullable|integer|min:1|max:9999',
            'purchaseDate'  => 'nullable|date',
            'purchasePrice' => 'nullable|numeric|min:0',
            'location'      => 'nullable|string|max:255',
            'notes'         => 'nullable|string|max:2000',
        ]);

        $entry = $this->commandService->addWine($userId, $validated);
        return response()->json($this->format($entry), 201);
    }

    public function update(Request $request, string $entryId): JsonResponse
    {
        $userId = JWTAuth::parseToken()->authenticate()->id;

        $validated = $request->validate([
            'quantity'      => 'nullable|integer|min:1|max:9999',
            'purchaseDate'  => 'nullable|date',
            'purchasePrice' => 'nullable|numeric|min:0',
            'location'      => 'nullable|string|max:255',
            'notes'         => 'nullable|string|max:2000',
        ]);

        $entry = $this->commandService->updateEntry($userId, $entryId, $validated);
        return response()->json($this->format($entry));
    }

    public function destroy(string $entryId, Request $request): JsonResponse
    {
        $userId = JWTAuth::parseToken()->authenticate()->id;
        $this->commandService->removeWine($userId, $entryId);
        return response()->json(null, 204);
    }

    public function stats(Request $request): JsonResponse
    {
        $userId = JWTAuth::parseToken()->authenticate()->id;
        return response()->json($this->queryService->getStats($userId));
    }

    private function format(CellarWine $entry): array
    {
        $wine = $entry->wine;
        return [
            'id'            => $entry->id,
            'wineId'        => $entry->wine_id,
            'quantity'      => $entry->quantity,
            'purchaseDate'  => $entry->purchase_date?->toDateString(),
            'purchasePrice' => $entry->purchase_price,
            'location'      => $entry->location,
            'notes'         => $entry->notes,
            'addedAt'       => $entry->created_at?->toISOString(),
            'updatedAt'     => $entry->updated_at?->toISOString(),
            'wine'          => $wine ? [
                'id'            => $wine->id,
                'name'          => $wine->name,
                'vintage'       => $wine->vintage,
                'wineType'      => $wine->wine_type,
                'style'         => $wine->style,
                'wineryName'    => $wine->winery?->name,
                'region'        => $wine->winery?->region,
                'country'       => $wine->winery?->country,
                'priceMin'      => $wine->price_min,
                'priceMax'      => $wine->price_max,
                'imageUrl'      => $wine->image_url,
                'ratingAverage' => $wine->rating_average,
            ] : null,
        ];
    }
}
