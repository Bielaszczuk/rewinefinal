<?php

namespace App\Application\Event\Services;

use App\Domain\Event\Contracts\EventRepositoryInterface;
use App\Models\Event;
use App\Utils\GeoUtils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EventQueryService
{
    public function __construct(
        private readonly EventRepositoryInterface $repository
    ) {}

    public function findById(string $id): ?Event
    {
        return $this->repository->findById($id);
    }

    public function findByIdOrFail(string $id): Event
    {
        return $this->repository->findByIdOrFail($id);
    }

    public function getAllPaginated(int $page = 0, int $size = 20): LengthAwarePaginator
    {
        return $this->repository->getAllPaginated($page, $size);
    }

    public function search(array $filters, int $page = 0, int $size = 20): LengthAwarePaginator
    {
        return $this->repository->search($filters, $page, $size);
    }

    public function listPublished(array $params, int $page = 0, int $size = 20): LengthAwarePaginator
    {
        return $this->repository->listPublished($params, $page, $size);
    }

    public function getNearby(float $latitude, float $longitude, float $radiusKm = 50, int $limit = 10): Collection
    {
        return $this->repository->getNearby($latitude, $longitude, $radiusKm, $limit);
    }

    public function getNearbyWithDistance(float $latitude, float $longitude, float $radiusKm, int $page = 0, int $size = 20): array
    {
        $box = GeoUtils::boundingBox($latitude, $longitude, $radiusKm);

        $events = Event::with('organizer')
            ->where('status', 'PUBLISHED')
            ->whereBetween('latitude', [$box['minLat'], $box['maxLat']])
            ->whereBetween('longitude', [$box['minLon'], $box['maxLon']])
            ->where('start_date', '>', now())
            ->get();

        $withDistance = $events->map(function (Event $e) use ($latitude, $longitude) {
            if ($e->latitude === null || $e->longitude === null) {
                return null;
            }
            $dist = GeoUtils::haversineDistance($latitude, $longitude, (float)$e->latitude, (float)$e->longitude);
            $e->distanceKm = round($dist, 2);
            return $e;
        })->filter()->sortBy('distanceKm')->values();

        $total = $withDistance->count();
        $offset = $page * $size;
        $items = $withDistance->slice($offset, $size)->values();

        return [
            'items' => $items,
            'total' => $total,
            'page'  => $page,
            'size'  => $size,
        ];
    }

    public function getByOrganizer(string $userId): Collection
    {
        return $this->repository->getByOrganizer($userId);
    }

    public function getByOrganizerPaginated(string $userId, int $page = 0, int $size = 20): LengthAwarePaginator
    {
        return $this->repository->getByOrganizerPaginated($userId, $page, $size);
    }

    public function getUpcoming(int $page = 0, int $size = 20): LengthAwarePaginator
    {
        return $this->repository->getUpcoming($page, $size);
    }

    public function isUserRegistered(string $eventId, string $userId): bool
    {
        return $this->repository->isUserRegistered($eventId, $userId);
    }

    public function exists(string $id): bool
    {
        return $this->repository->exists($id);
    }
}
