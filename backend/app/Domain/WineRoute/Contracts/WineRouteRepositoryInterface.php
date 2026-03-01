<?php

namespace App\Domain\WineRoute\Contracts;

use App\Models\WineRoute;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface WineRouteRepositoryInterface
{
    public function findById(string $id): ?WineRoute;

    public function findByIdOrFail(string $id): WineRoute;

    public function getAllPaginated(array $filters, int $page = 0, int $size = 20): LengthAwarePaginator;

    public function getAllPaginatedIncludingInactive(array $filters, int $page = 0, int $size = 20): LengthAwarePaginator;

    public function getHierarchy(): array;

    public function getCountries(): array;

    public function getRegions(string $country): array;

    public function getSubregions(string $country, string $region): array;

    public function create(array $data): WineRoute;

    public function update(string $id, array $data): WineRoute;

    public function delete(string $id): bool;

    public function exists(string $id): bool;
}

