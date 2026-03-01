<?php

namespace App\Http\Controllers;

use App\Application\WineRoute\Services\WineRouteQueryService;
use App\Application\WineRoute\Services\WineRouteCommandService;
use App\Http\Responses\PageResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WineRouteAdminController extends Controller
{
    public function __construct(
        private readonly WineRouteQueryService $queryService,
        private readonly WineRouteCommandService $commandService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $params = $request->all();
        $page = (int)($params['page'] ?? 0);
        $size = min((int)($params['size'] ?? 20), 100);
        $paginator = $this->queryService->listAll($params, $page, $size);
        return response()->json(PageResponse::from($paginator, fn($r) => $this->toArray($r)));
    }

    public function show(string $id): JsonResponse
    {
        $route = $this->queryService->findByIdOrFail($id);
        return response()->json($this->toDetailArray($route));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'country' => 'required|string|max:100',
            'region' => 'nullable|string|max:100',
            'subregion' => 'nullable|string|max:100',
            'estimatedDuration' => 'nullable|max:50',
            'estimatedDays' => 'nullable|integer|min:1',
            'totalDistance' => 'nullable|numeric|min:0',
            'difficulty' => 'nullable|string|in:easy,moderate,challenging',
            'imageUrl' => 'nullable|string|max:500',
            'mapEmbedUrl' => 'nullable|string|max:1000',
            'status' => 'nullable|string|in:active,draft,archived',
            'recommendedWineTypes' => 'nullable|array',
            'stops' => 'nullable|array',
            'stops.*.name' => 'required_with:stops|string|max:255',
            'stops.*.description' => 'nullable|string',
            'stops.*.type' => 'nullable|string|max:50',
            'stops.*.address' => 'nullable|string|max:500',
            'stops.*.latitude' => 'nullable|numeric',
            'stops.*.longitude' => 'nullable|numeric',
            'stops.*.estimatedDuration' => 'nullable',
            'stops.*.stopOrder' => 'nullable|integer',
            'wineryIds' => 'nullable|array',
            'wineryIds.*' => 'uuid|exists:wineries,id',
        ]);

        $data = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'country' => $validated['country'],
            'region' => $validated['region'] ?? null,
            'subregion' => $validated['subregion'] ?? null,
            'estimated_duration' => $validated['estimatedDuration'] ?? null,
            'estimated_days' => $validated['estimatedDays'] ?? null,
            'total_distance' => $validated['totalDistance'] ?? null,
            'difficulty' => $validated['difficulty'] ?? 'moderate',
            'image_url' => $validated['imageUrl'] ?? null,
            'map_embed_url' => $validated['mapEmbedUrl'] ?? null,
            'status' => $validated['status'] ?? 'active',
            'recommended_wine_types' => $validated['recommendedWineTypes'] ?? [],
            'created_by' => auth('api')->id(),
            'stops' => $this->mapStops($validated['stops'] ?? []),
            'winery_ids' => $validated['wineryIds'] ?? [],
        ];

        $route = $this->commandService->create($data);

        return response()->json($this->toDetailArray($route), 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'country' => 'sometimes|string|max:100',
            'region' => 'nullable|string|max:100',
            'subregion' => 'nullable|string|max:100',
            'estimatedDuration' => 'nullable|max:50',
            'estimatedDays' => 'nullable|integer|min:1',
            'totalDistance' => 'nullable|numeric|min:0',
            'difficulty' => 'nullable|string|in:easy,moderate,challenging',
            'imageUrl' => 'nullable|string|max:500',
            'mapEmbedUrl' => 'nullable|string|max:1000',
            'status' => 'nullable|string|in:active,draft,archived',
            'recommendedWineTypes' => 'nullable|array',
            'stops' => 'nullable|array',
            'stops.*.name' => 'required_with:stops|string|max:255',
            'stops.*.description' => 'nullable|string',
            'stops.*.type' => 'nullable|string|max:50',
            'stops.*.address' => 'nullable|string|max:500',
            'stops.*.latitude' => 'nullable|numeric',
            'stops.*.longitude' => 'nullable|numeric',
            'stops.*.estimatedDuration' => 'nullable',
            'stops.*.stopOrder' => 'nullable|integer',
            'wineryIds' => 'nullable|array',
            'wineryIds.*' => 'uuid|exists:wineries,id',
        ]);

        $data = [];

        if (isset($validated['name'])) $data['name'] = $validated['name'];
        if (isset($validated['description'])) $data['description'] = $validated['description'];
        if (isset($validated['country'])) $data['country'] = $validated['country'];
        if (array_key_exists('region', $validated)) $data['region'] = $validated['region'];
        if (array_key_exists('subregion', $validated)) $data['subregion'] = $validated['subregion'];
        if (array_key_exists('estimatedDuration', $validated)) $data['estimated_duration'] = $validated['estimatedDuration'];
        if (array_key_exists('estimatedDays', $validated)) $data['estimated_days'] = $validated['estimatedDays'];
        if (array_key_exists('totalDistance', $validated)) $data['total_distance'] = $validated['totalDistance'];
        if (isset($validated['difficulty'])) $data['difficulty'] = $validated['difficulty'];
        if (array_key_exists('imageUrl', $validated)) $data['image_url'] = $validated['imageUrl'];
        if (array_key_exists('mapEmbedUrl', $validated)) $data['map_embed_url'] = $validated['mapEmbedUrl'];
        if (isset($validated['status'])) $data['status'] = $validated['status'];
        if (isset($validated['recommendedWineTypes'])) $data['recommended_wine_types'] = $validated['recommendedWineTypes'];

        if (isset($validated['stops'])) {
            $data['stops'] = $this->mapStops($validated['stops']);
        }

        if (isset($validated['wineryIds'])) {
            $data['winery_ids'] = $validated['wineryIds'];
        }

        $route = $this->commandService->update($id, $data);

        return response()->json($this->toDetailArray($route));
    }

    public function destroy(string $id): JsonResponse
    {
        $this->commandService->delete($id);
        return response()->json(['message' => 'Route deleted successfully']);
    }

    private function mapStops(array $stops): array
    {
        return array_map(fn($s, $index) => [
            'name' => $s['name'],
            'description' => $s['description'] ?? null,
            'type' => $s['type'] ?? null,
            'address' => $s['address'] ?? null,
            'latitude' => $s['latitude'] ?? null,
            'longitude' => $s['longitude'] ?? null,
            'estimated_duration' => $s['estimatedDuration'] ?? null,
            'stop_order' => $s['stopOrder'] ?? ($index + 1),
        ], $stops, array_keys($stops));
    }

    private function toArray($route): array
    {
        return [
            'id' => $route->id,
            'name' => $route->name,
            'description' => $route->description,
            'country' => $route->country,
            'region' => $route->region,
            'subregion' => $route->subregion,
            'estimatedDuration' => $route->estimated_duration,
            'estimatedDays' => $route->estimated_days,
            'totalDistance' => $route->total_distance,
            'difficulty' => $route->difficulty,
            'imageUrl' => $route->image_url,
            'mapEmbedUrl' => $route->map_embed_url,
            'status' => $route->status,
            'stopCount' => $route->stops_count ?? 0,
            'wineryCount' => $route->wineries_count ?? 0,
            'createdAt' => $route->created_at?->toISOString(),
        ];
    }

    private function toDetailArray($route): array
    {
        return array_merge($this->toArray($route), [
            'recommendedWineTypes' => $route->recommended_wine_types ?? [],
            'stops' => ($route->stops ?? collect())->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'description' => $s->description,
                'type' => $s->type,
                'address' => $s->address,
                'latitude' => $s->latitude,
                'longitude' => $s->longitude,
                'stopOrder' => $s->stop_order,
                'estimatedDuration' => $s->estimated_duration,
            ])->values()->all(),
            'wineries' => ($route->wineries ?? collect())->map(fn($w) => [
                'id' => $w->id,
                'name' => $w->name,
                'region' => $w->region,
                'latitude' => $w->latitude,
                'longitude' => $w->longitude,
            ])->values()->all(),
            'wineryIds' => ($route->wineries ?? collect())->pluck('id')->values()->all(),
            'updatedAt' => $route->updated_at?->toISOString(),
        ]);
    }
}

