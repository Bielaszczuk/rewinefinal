<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wine\StoreWineRequest;
use App\Http\Requests\Wine\UpdateWineRequest;
use App\Http\Resources\Wine\WineResource;
use App\Http\Resources\Wine\WineCollection;
use App\Application\Wine\Services\WineCommandService;
use App\Application\Wine\Services\WineQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WineAdminController extends Controller
{
    public function __construct(
        private readonly WineQueryService $queryService,
        private readonly WineCommandService $commandService
    ) {}

    /**
     * List all wines for administration.
     */
    public function index(Request $request)
    {
        $wines = $this->queryService->getAllPaginated(
            page: (int) $request->query('page', 0),
            size: min((int) $request->query('size', 20), 100),
            includeInactive: true
        );

        return WineCollection::make($wines);
    }

    /**
     * Store a newly created wine.
     */
    public function store(StoreWineRequest $request)
    {
        $wine = $this->commandService->create($request->toDto());

        return WineResource::make($wine);
    }

    /**
     * Display the specified wine.
     */
    public function show(string $id)
    {
        $wine = $this->queryService->findById($id);

        return WineResource::make($wine);
    }

    /**
     * Update the specified wine.
     */
    public function update(UpdateWineRequest $request, string $id)
    {
        $wine = $this->commandService->update($id, $request->toDto());

        return WineResource::make($wine);
    }

    /**
     * Remove the specified wine.
     */
    public function destroy(string $id): JsonResponse
    {
        $this->middleware('role:admin');

        $this->commandService->delete($id);

        return response()->json([
            'message' => 'Wine deleted successfully',
            'id' => $id,
        ], 200);
    }

    /**
     * Toggle wine featured status.
     */
    public function toggleFeatured(string $id)
    {
        $wine = $this->commandService->toggleFeatured($id);

        return WineResource::make($wine);
    }

    /**
     * Toggle wine active status.
     */
    public function toggleActive(string $id)
    {
        $wine = $this->commandService->toggleActive($id);

        return WineResource::make($wine);
    }
}
