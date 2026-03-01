<?php

namespace App\Http\Controllers;

use App\Application\Wine\Services\WineQueryService;
use App\Http\Resources\Wine\WineCollection;
use App\Http\Resources\Wine\WineResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class WineController extends Controller
{
    public function __construct(
        private readonly WineQueryService $queryService
    ) {}

    public function index(Request $request)
    {
        $wines = $this->queryService->search(
            $request->all(),
            (int) $request->query('page', 0),
            min((int) $request->query('size', 20), 100)
        );

        return response()->json(WineCollection::make($wines)->toArray($request));
    }

    public function featured(Request $request)
    {
        $wines = $this->queryService->getFeatured(
            (int) $request->query('page', 0),
            min((int) $request->query('size', 20), 100)
        );

        return response()->json(WineCollection::make($wines)->toArray($request));
    }

    public function topRated(Request $request)
    {
        $wines = $this->queryService->getTopRated(
            (int) $request->query('page', 0),
            min((int) $request->query('size', 20), 100)
        );

        return response()->json(WineCollection::make($wines)->toArray($request));
    }

    public function recent(Request $request)
    {
        $wines = $this->queryService->getRecent(
            (int) $request->query('page', 0),
            min((int) $request->query('size', 20), 100)
        );

        return response()->json(WineCollection::make($wines)->toArray($request));
    }

    public function recommended(Request $request)
    {
        $wines = $this->queryService->getRecommended(
            (int) $request->query('page', 0),
            min((int) $request->query('size', 20), 100)
        );

        return response()->json(WineCollection::make($wines)->toArray($request));
    }

    public function popular(Request $request)
    {
        $wines = $this->queryService->getPopular(
            (int) $request->query('page', 0),
            min((int) $request->query('size', 20), 100)
        );

        return response()->json(WineCollection::make($wines)->toArray($request));
    }

    public function show(Request $request, string $id)
    {
        $userId = $this->getOptionalUserId($request);
        $data = $this->queryService->getDetails($id, $userId);

        $wineArray = WineResource::make($data['wine'])->toArray($request);
        $wineArray['inCellar'] = $data['inCellar'];

        return response()->json($wineArray);
    }

    private function getOptionalUserId(Request $request): ?string
    {
        try {
            if ($request->bearerToken()) {
                $user = JWTAuth::parseToken()->authenticate();
                return $user?->id;
            }
        } catch (JWTException $e) {}

        return null;
    }
}
