<?php

namespace App\Application\Event\Services;

use App\Domain\Event\Contracts\EventRepositoryInterface;
use App\Domain\Event\DTOs\CreateEventDto;
use App\Domain\Event\DTOs\UpdateEventDto;
use App\Domain\Event\Exceptions\EventNotFoundException;
use App\Models\Event;
use Illuminate\Support\Facades\DB;

class EventCommandService
{
    public function __construct(
        private readonly EventRepositoryInterface $repository
    ) {}

    public function create(CreateEventDto $dto): Event
    {
        return DB::transaction(function () use ($dto) {
            return $this->repository->create($dto->toArray());
        });
    }

    public function createFromArray(array $data, string $organizerId): Event
    {
        return DB::transaction(function () use ($data, $organizerId) {
            return $this->repository->create(array_merge($data, [
                'organizer_id' => $organizerId,
                'status' => $data['status'] ?? 'DRAFT',
            ]));
        });
    }

    public function update(string $id, UpdateEventDto $dto): Event
    {
        if (!$this->repository->exists($id)) {
            throw new EventNotFoundException("Event with ID {$id} not found");
        }

        return DB::transaction(function () use ($id, $dto) {
            return $this->repository->update($id, $dto->toArray());
        });
    }

    public function updateWithPermission(string $id, array $data, string $userId, bool $isAdmin = false): Event
    {
        $event = $this->repository->findByIdOrFail($id);

        if (!$isAdmin && $event->organizer_id !== $userId) {
            abort(403, json_encode(['code' => 'E2005', 'message' => 'Forbidden']));
        }

        return DB::transaction(function () use ($id, $data) {
            return $this->repository->update($id, $data);
        });
    }

    public function delete(string $id): bool
    {
        if (!$this->repository->exists($id)) {
            throw new EventNotFoundException("Event with ID {$id} not found");
        }

        return DB::transaction(function () use ($id) {
            return $this->repository->delete($id);
        });
    }

    public function deleteWithPermission(string $id, string $userId, bool $isAdmin = false): bool
    {
        $event = $this->repository->findByIdOrFail($id);

        if (!$isAdmin && $event->organizer_id !== $userId) {
            abort(403, json_encode(['code' => 'E2005', 'message' => 'Forbidden']));
        }

        return DB::transaction(function () use ($id) {
            return $this->repository->delete($id);
        });
    }

    public function registerUser(string $eventId, string $userId): void
    {
        if (!$this->repository->exists($eventId)) {
            throw new EventNotFoundException("Event with ID {$eventId} not found");
        }

        $this->repository->registerUser($eventId, $userId);
    }

    public function unregisterUser(string $eventId, string $userId): void
    {
        if (!$this->repository->exists($eventId)) {
            throw new EventNotFoundException("Event with ID {$eventId} not found");
        }

        $this->repository->unregisterUser($eventId, $userId);
    }
}
