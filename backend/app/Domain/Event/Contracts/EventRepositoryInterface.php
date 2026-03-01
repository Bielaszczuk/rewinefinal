<?php

namespace App\Domain\Event\Contracts;

use App\Models\Event;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface EventRepositoryInterface
{
    public function findById(string $id): ?Event;

    public function findByIdOrFail(string $id): Event;

    public function getAllPaginated(int $page = 0, int $size = 20): LengthAwarePaginator;

    public function search(array $filters, int $page = 0, int $size = 20): LengthAwarePaginator;

    public function getNearby(float $latitude, float $longitude, float $radiusKm = 50, int $limit = 10): Collection;

    public function getByOrganizer(string $userId): Collection;

    public function getByOrganizerPaginated(string $userId, int $page = 0, int $size = 20): LengthAwarePaginator;

    public function getUpcoming(int $page = 0, int $size = 20): LengthAwarePaginator;

    public function listPublished(array $params, int $page = 0, int $size = 20): LengthAwarePaginator;

    public function create(array $data): Event;

    public function update(string $id, array $data): Event;

    public function delete(string $id): bool;

    public function exists(string $id): bool;

    public function registerUser(string $eventId, string $userId): void;

    public function unregisterUser(string $eventId, string $userId): void;

    public function isUserRegistered(string $eventId, string $userId): bool;
}
