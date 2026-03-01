<?php

namespace App\Http\Controllers\Admin;

use App\Application\Winery\Services\WineryCommandService;
use App\Application\Winery\Services\WineryQueryService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Winery\StoreWineryRequest;
use App\Http\Requests\Winery\UpdateWineryRequest;
use App\Http\Resources\Winery\WineryCollection;
use App\Http\Resources\Winery\WineryResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WineryAdminController extends Controller
{
    public function __construct(
        private readonly WineryQueryService $queryService,
        private readonly WineryCommandService $commandService
    ) {}

    public function index(Request $request)
    {
        $wineries = $this->queryService->getAllPaginated(
            page: (int) $request->query('page', 0),
            size: min((int) $request->query('size', 20), 100)
        );

        return WineryCollection::make($wineries);
    }

    public function store(StoreWineryRequest $request)
    {
        $winery = $this->commandService->create($request->toDto());

        return WineryResource::make($winery);
    }

    public function show(string $id)
    {
        $winery = $this->queryService->findByIdOrFail($id);

        return WineryResource::make($winery);
    }

    public function update(UpdateWineryRequest $request, string $id)
    {
        $winery = $this->commandService->update($id, $request->toDto());

        return WineryResource::make($winery);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->middleware('role:admin');

        $this->commandService->delete($id);

        return response()->json([
            'message' => 'Winery deleted successfully',
            'id' => $id,
        ], 200);
    }
}
