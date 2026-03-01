<?php

namespace App\Application\Cellar\Services;

use App\Domain\Cellar\Contracts\CellarRepositoryInterface;
use App\Models\CellarWine;
use Illuminate\Support\Collection;

class CellarQueryService
{
    public function __construct(
        private readonly CellarRepositoryInterface $repository
    ) {}

    public function getCellar(string $userId): Collection
    {
        return $this->repository->getByUser($userId);
    }

    public function getStats(string $userId): array
    {
        return $this->repository->getStatsByUser($userId);
    }

    public function findByUserAndId(string $userId, string $entryId): ?CellarWine
    {
        return $this->repository->findByUserAndId($userId, $entryId);
    }
}

