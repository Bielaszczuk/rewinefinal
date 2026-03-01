<?php

namespace App\Application\WineRoute\Services;

use App\Domain\WineRoute\Contracts\WineRouteRepositoryInterface;
use App\Models\WineRoute;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class WineRouteQueryService
{
    public function __construct(
        private readonly WineRouteRepositoryInterface $repository
    ) {}

    public function findById(string $id): ?WineRoute
    {
        return $this->repository->findById($id);
    }

    public function findByIdOrFail(string $id): WineRoute
    {
        return $this->repository->findByIdOrFail($id);
    }

    public function list(array $filters, int $page = 0, int $size = 20): LengthAwarePaginator
    {
        return $this->repository->getAllPaginated($filters, $page, $size);
    }

    public function listAll(array $filters, int $page = 0, int $size = 20): LengthAwarePaginator
    {
        return $this->repository->getAllPaginatedIncludingInactive($filters, $page, $size);
    }

    public function getHierarchy(): array
    {
        return $this->repository->getHierarchy();
    }

    public function getCountries(): array
    {
        return $this->repository->getCountries();
    }

    public function getRegions(string $country): array
    {
        return $this->repository->getRegions($country);
    }

    public function getSubregions(string $country, string $region): array
    {
        return $this->repository->getSubregions($country, $region);
    }

    public function exists(string $id): bool
    {
        return $this->repository->exists($id);
    }
}

