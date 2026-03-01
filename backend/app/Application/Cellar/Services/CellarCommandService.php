<?php

namespace App\Application\Cellar\Services;

use App\Domain\Cellar\Contracts\CellarRepositoryInterface;
use App\Models\CellarWine;
use Illuminate\Support\Facades\DB;

class CellarCommandService
{
    public function __construct(
        private readonly CellarRepositoryInterface $repository
    ) {}

    public function addWine(string $userId, array $data): CellarWine
    {
        return DB::transaction(function () use ($userId, $data) {
            return $this->repository->addOrUpdateWine($userId, $data);
        });
    }

    public function updateEntry(string $userId, string $entryId, array $data): CellarWine
    {
        return DB::transaction(function () use ($userId, $entryId, $data) {
            return $this->repository->update($userId, $entryId, $data);
        });
    }

    public function removeWine(string $userId, string $entryId): bool
    {
        return DB::transaction(function () use ($userId, $entryId) {
            return $this->repository->delete($userId, $entryId);
        });
    }
}

