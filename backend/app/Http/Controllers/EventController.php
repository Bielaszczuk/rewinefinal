<?php

namespace App\Http\Controllers;

use App\Application\Event\Services\EventCommandService;
use App\Application\Event\Services\EventQueryService;
use App\Http\Responses\PageResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class EventController extends Controller
{
    public function __construct(
        private readonly EventQueryService $queryService,
        private readonly EventCommandService $commandService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $page = (int)($request->query('page', 0));
        $size = min((int)($request->query('size', 20)), 100);
        $paginator = $this->queryService->listPublished($request->all(), $page, $size);
        return response()->json(PageResponse::from($paginator, fn($e) => $this->summaryShape($e)));
    }

    public function nearby(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radiusKm'  => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->validationError($request, $validator->errors()->toArray());
        }

        $result = $this->queryService->getNearbyWithDistance(
            (float) $request->query('latitude'),
            (float) $request->query('longitude'),
            (float) $request->query('radiusKm', 50.0),
            (int) $request->query('page', 0),
            min((int) $request->query('size', 20), 100)
        );

        $mapped = array_map(fn($e) => $this->summaryShape($e), $result['items']->all());

        return response()->json(PageResponse::fromArray(
            $mapped,
            $result['total'],
            $result['page'],
            $result['size']
        ));
    }

    public function show(string $id): JsonResponse
    {
        $event = $this->queryService->findByIdOrFail($id);
        return response()->json($this->detailShape($event));
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:255',
            'type'        => 'required|string|in:TASTING,FESTIVAL,TOUR,CLASS,DINNER,PAIRING,OTHER',
            'startDate'   => 'required|date',
            'endDate'     => 'required|date|after_or_equal:startDate',
            'locationCity' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->validationError($request, $validator->errors()->toArray());
        }

        $user = JWTAuth::parseToken()->authenticate();
        $data = $this->mapRequestToDb($request->all());
        $event = $this->commandService->createFromArray($data, $user->id);

        return response()->json($this->detailShape($event), 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $user    = JWTAuth::parseToken()->authenticate();
        $isAdmin = $user->hasRole('ROLE_ADMIN');
        $data    = $this->mapRequestToDb($request->all());
        $event   = $this->commandService->updateWithPermission($id, $data, $user->id, $isAdmin);

        return response()->json($this->detailShape($event));
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $user    = JWTAuth::parseToken()->authenticate();
        $isAdmin = $user->hasRole('ROLE_ADMIN');
        $this->commandService->deleteWithPermission($id, $user->id, $isAdmin);

        return response()->json(['message' => 'Event deleted']);
    }

    public function myEvents(Request $request): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();

        $paginator = $this->queryService->getByOrganizerPaginated(
            $user->id,
            (int) $request->query('page', 0),
            min((int) $request->query('size', 20), 100)
        );

        return response()->json(PageResponse::from($paginator, fn($e) => $this->detailShape($e)));
    }

    private function summaryShape($event): array
    {
        return [
            'id'               => $event->id,
            'title'            => $event->title,
            'type'             => $event->type,
            'status'           => $event->status,
            'startDate'        => $event->start_date?->toISOString(),
            'endDate'          => $event->end_date?->toISOString(),
            'locationName'     => $event->location_name,
            'locationCity'     => $event->location_city,
            'locationRegion'   => $event->location_region,
            'latitude'         => $event->latitude,
            'longitude'        => $event->longitude,
            'price'            => $event->price,
            'maxAttendees'     => $event->max_attendees,
            'currentAttendees' => $event->current_attendees,
            'availableSpots'   => $event->getAvailableSpots(),
            'imageUrl'         => $event->image_url,
            'organizerName'    => $event->organizer?->name ?? $event->organizer?->username,
            'organizerId'      => $event->organizer_id,
            'distanceKm'       => $event->distanceKm ?? null,
        ];
    }

    private function detailShape($event): array
    {
        return array_merge($this->summaryShape($event), [
            'description'     => $event->description,
            'locationAddress' => $event->location_address,
            'isAvailable'     => $event->isAvailable(),
            'organizerType'   => $event->organizer_type,
            'contactEmail'    => $event->contact_email,
            'contactPhone'    => $event->contact_phone,
            'websiteUrl'      => $event->website_url,
            'mapEmbedUrl'     => $event->map_embed_url,
            'updatedAt'       => $event->updated_at?->toISOString(),
        ]);
    }

    private function mapRequestToDb(array $data): array
    {
        $map = [
            'startDate'      => 'start_date',
            'endDate'        => 'end_date',
            'locationName'   => 'location_name',
            'locationAddress' => 'location_address',
            'locationCity'   => 'location_city',
            'locationRegion' => 'location_region',
            'maxAttendees'   => 'max_attendees',
            'imageUrl'       => 'image_url',
            'contactEmail'   => 'contact_email',
            'contactPhone'   => 'contact_phone',
            'websiteUrl'     => 'website_url',
            'mapEmbedUrl'    => 'map_embed_url',
            'organizerType'  => 'organizer_type',
        ];

        $result = [];
        foreach ($data as $key => $val) {
            $dbKey = $map[$key] ?? $key;
            $result[$dbKey] = $val;
        }
        return $result;
    }

    private function validationError(Request $request, array $errors): JsonResponse
    {
        $details = [];
        foreach ($errors as $field => $messages) {
            foreach ((array) $messages as $msg) {
                $details[] = ['field' => $field, 'message' => $msg];
            }
        }
        return response()->json([
            'timestamp' => now()->toISOString(),
            'path'      => $request->path(),
            'requestId' => app()->bound('request-id') ? app('request-id') : null,
            'status'    => 400,
            'code'      => 'E1001',
            'message'   => 'Validation failed',
            'details'   => $details,
        ], 400);
    }
}
