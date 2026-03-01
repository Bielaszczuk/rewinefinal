<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Event\Contracts\EventRepositoryInterface;
use App\Models\Event;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EventRepository implements EventRepositoryInterface
{
    public function findById(string $id): ?Event
    {
        return Event::with('organizer')->find($id);
    }

    public function findByIdOrFail(string $id): Event
    {
        return Event::with('organizer')->findOrFail($id);
    }

    public function getAllPaginated(int $page = 0, int $size = 20): LengthAwarePaginator
    {
        return Event::with('organizer')
            ->orderBy('start_date', 'desc')
            ->paginate($size, ['*'], 'page', $page + 1);
    }

    public function search(array $filters, int $page = 0, int $size = 20): LengthAwarePaginator
    {
        $query = Event::with('organizer');

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ilike', $search)
                  ->orWhere('description', 'ilike', $search)
                  ->orWhere('location_name', 'ilike', $search);
            });
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['startDate'])) {
            $query->where('start_date', '>=', $filters['startDate']);
        }

        if (!empty($filters['endDate'])) {
            $query->where('end_date', '<=', $filters['endDate']);
        }

        return $query->orderBy('start_date', 'desc')
            ->paginate($size, ['*'], 'page', $page + 1);
    }

    public function getNearby(float $latitude, float $longitude, float $radiusKm = 50, int $limit = 10): Collection
    {
        return Event::select('*')
            ->selectRaw('(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance', [$latitude, $longitude, $latitude])
            ->having('distance', '<', $radiusKm)
            ->orderBy('distance')
            ->limit($limit)
            ->get();
    }

    public function getByOrganizer(string $userId): Collection
    {
        return Event::where('organizer_id', $userId)
            ->orderBy('start_date', 'desc')
            ->get();
    }

    public function getByOrganizerPaginated(string $userId, int $page = 0, int $size = 20): LengthAwarePaginator
    {
        return Event::with('organizer')
            ->where('organizer_id', $userId)
            ->orderBy('start_date', 'desc')
            ->paginate($size, ['*'], 'page', $page + 1);
    }

    public function getUpcoming(int $page = 0, int $size = 20): LengthAwarePaginator
    {
        return Event::with('organizer')
            ->where('start_date', '>=', now())
            ->orderBy('start_date', 'asc')
            ->paginate($size, ['*'], 'page', $page + 1);
    }

    public function listPublished(array $params, int $page = 0, int $size = 20): LengthAwarePaginator
    {
        $query = Event::with('organizer')
            ->where('status', 'PUBLISHED');

        if (!empty($params['type'])) {
            $query->where('type', strtoupper($params['type']));
        }
        if (!empty($params['city'])) {
            $query->where('location_city', 'ilike', '%' . $params['city'] . '%');
        }
        if (!empty($params['region'])) {
            $query->where('location_region', 'ilike', '%' . $params['region'] . '%');
        }
        if (!empty($params['search'])) {
            $s = '%' . $params['search'] . '%';
            $query->where(function ($q) use ($s) {
                $q->where('title', 'ilike', $s)->orWhere('description', 'ilike', $s);
            });
        }

        return $query->orderBy('start_date', 'asc')
            ->paginate($size, ['*'], 'page', $page + 1);
    }

    public function create(array $data): Event
    {
        return Event::create($data);
    }

    public function update(string $id, array $data): Event
    {
        $event = $this->findByIdOrFail($id);
        $event->update($data);
        return $event->fresh(['organizer']);
    }

    public function delete(string $id): bool
    {
        $event = $this->findByIdOrFail($id);
        return $event->delete();
    }

    public function exists(string $id): bool
    {
        return Event::where('id', $id)->exists();
    }

    public function registerUser(string $eventId, string $userId): void
    {
        DB::table('event_registrations')->insert([
            'event_id' => $eventId,
            'user_id' => $userId,
            'created_at' => now(),
        ]);
    }

    public function unregisterUser(string $eventId, string $userId): void
    {
        DB::table('event_registrations')
            ->where('event_id', $eventId)
            ->where('user_id', $userId)
            ->delete();
    }

    public function isUserRegistered(string $eventId, string $userId): bool
    {
        return DB::table('event_registrations')
            ->where('event_id', $eventId)
            ->where('user_id', $userId)
            ->exists();
    }
}
