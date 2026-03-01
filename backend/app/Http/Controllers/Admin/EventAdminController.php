<?php

namespace App\Http\Controllers\Admin;

use App\Application\Event\Services\EventCommandService;
use App\Application\Event\Services\EventQueryService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Event\StoreEventRequest;
use App\Http\Requests\Event\UpdateEventRequest;
use App\Http\Resources\Event\EventCollection;
use App\Http\Resources\Event\EventResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventAdminController extends Controller
{
    public function __construct(
        private readonly EventQueryService $queryService,
        private readonly EventCommandService $commandService
    ) {}

    public function index(Request $request)
    {
        $events = $this->queryService->getAllPaginated(
            page: (int) $request->query('page', 0),
            size: min((int) $request->query('size', 20), 100)
        );

        return EventCollection::make($events);
    }

    public function store(StoreEventRequest $request)
    {
        $event = $this->commandService->create($request->toDto());

        return EventResource::make($event);
    }

    public function show(string $id)
    {
        $event = $this->queryService->findByIdOrFail($id);

        return EventResource::make($event);
    }

    public function update(UpdateEventRequest $request, string $id)
    {
        $event = $this->commandService->update($id, $request->toDto());

        return EventResource::make($event);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->commandService->delete($id);

        return response()->json([
            'message' => 'Event deleted successfully',
            'id' => $id,
        ], 200);
    }
}
