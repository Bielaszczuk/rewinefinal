<?php

namespace App\Http\Controllers;

use App\Application\Winery\Services\WineryQueryService;
use App\Http\Resources\Winery\WineryCollection;
use App\Http\Resources\Winery\WineryResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WineryController extends Controller
{
    public function __construct(
        private readonly WineryQueryService $queryService
    ) {}

    public function index(Request $request)
    {
        $wineries = $this->queryService->getAllPaginated(
            page: (int) $request->query('page', 0),
            size: min((int) $request->query('size', 20), 100)
        );

        return WineryCollection::make($wineries);
    }

    public function show(string $id)
    {
        $winery = $this->queryService->findByIdOrFail($id);

        return WineryResource::make($winery);
    }
}
