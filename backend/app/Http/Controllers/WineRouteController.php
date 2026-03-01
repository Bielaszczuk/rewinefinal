<?php

namespace App\Http\Controllers;

use App\Application\WineRoute\Services\WineRouteQueryService;
use App\Http\Responses\PageResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WineRouteController extends Controller
{
    public function __construct(private readonly WineRouteQueryService $queryService) {}

    public function hierarchy(): JsonResponse
    {
        return response()->json($this->queryService->getHierarchy());
    }

    public function countries(): JsonResponse
    {
        return response()->json($this->queryService->getCountries());
    }

    public function regions(string $country): JsonResponse
    {
        return response()->json($this->queryService->getRegions($country));
    }

    public function subregions(string $country, string $region): JsonResponse
    {
        return response()->json($this->queryService->getSubregions($country, $region));
    }

    public function index(Request $request): JsonResponse
    {
        $params = $request->all();
        $page = (int)($params['page'] ?? 0);
        $size = min((int)($params['size'] ?? 20), 100);
        $paginator = $this->queryService->list($params, $page, $size);
        return response()->json(PageResponse::from($paginator, fn($r) => $this->summaryShape($r)));
    }

    public function show(string $id): JsonResponse
    {
        $route = $this->queryService->findByIdOrFail($id);
        return response()->json($this->detailShape($route));
    }

    private function summaryShape($route): array
    {
        return [
            'id'                => $route->id,
            'name'              => $route->name,
            'description'       => $route->description,
            'country'           => $route->country,
            'region'            => $route->region,
            'subregion'         => $route->subregion,
            'estimatedDuration' => $route->estimated_duration,
            'estimatedDays'     => $route->estimated_days,
            'totalDistance'     => $route->total_distance,
            'difficulty'        => $route->difficulty,
            'imageUrl'          => $route->image_url,
            'mapEmbedUrl'       => $route->map_embed_url,
            'status'            => $route->status,
            'wineryCount'       => $route->wineries_count ?? ($route->wineries?->count() ?? 0),
            'stopCount'         => $route->stops_count ?? ($route->stops?->count() ?? 0),
            'createdAt'         => $route->created_at?->toISOString(),
        ];
    }

    private function detailShape($route): array
    {
        return array_merge($this->summaryShape($route), [
            'recommendedWineTypes' => $route->recommended_wine_types ?? [],
            'stops' => ($route->stops ?? collect())->map(fn($s) => [
                'id'               => $s->id,
                'name'             => $s->name,
                'description'      => $s->description,
                'type'             => $s->type,
                'address'          => $s->address,
                'latitude'         => $s->latitude,
                'longitude'        => $s->longitude,
                'stopOrder'        => $s->stop_order,
                'estimatedDuration' => $s->estimated_duration,
            ])->values()->all(),
            'wineries' => ($route->wineries ?? collect())->map(fn($w) => [
                'id'          => $w->id,
                'name'        => $w->name,
                'country'     => $w->country,
                'region'      => $w->region,
                'subregion'   => $w->subregion,
                'description' => $w->description,
                'websiteUrl'  => $w->website_url,
                'logoUrl'     => $w->logo_url,
                'established' => $w->established,
            ])->values()->all(),
            'createdBy' => $route->creator ? [
                'id'       => $route->creator->id,
                'username' => $route->creator->username,
                'name'     => $route->creator->name,
            ] : null,
            'updatedAt' => $route->updated_at?->toISOString(),
        ]);
    }
}
