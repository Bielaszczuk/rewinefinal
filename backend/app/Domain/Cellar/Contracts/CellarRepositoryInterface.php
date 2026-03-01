<?php

namespace App\Domain\Cellar\Contracts;

use App\Models\CellarWine;
use Illuminate\Support\Collection;

interface CellarRepositoryInterface
{
    public function findById(string $id): ?CellarWine;

    public function findByIdOrFail(string $id): CellarWine;

    public function findByUserAndId(string $userId, string $entryId): ?CellarWine;

    public function findByUserAndIdOrFail(string $userId, string $entryId): CellarWine;

    public function getByUser(string $userId): Collection;

    public function addOrUpdateWine(string $userId, array $data): CellarWine;

    public function update(string $userId, string $entryId, array $data): CellarWine;

    public function delete(string $userId, string $entryId): bool;

    public function getStatsByUser(string $userId): array;
}

