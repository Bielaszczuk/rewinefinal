<?php

namespace App\Application\Winery\Services;

use App\Domain\Winery\Contracts\WineryRepositoryInterface;
use App\Models\Winery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class WineryQueryService
{
    public function __construct(
        private readonly WineryRepositoryInterface $repository
    ) {}

    public function findById(string $id): ?Winery
    {
        return $this->repository->findById($id);
    }

    public function findByIdOrFail(string $id): Winery
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

    public function getByCountry(string $country): Collection
    {
        return $this->repository->getByCountry($country);
    }

    public function getByRegion(string $region): Collection
    {
        return $this->repository->getByRegion($region);
    }

    public function exists(string $id): bool
    {
        return $this->repository->exists($id);
    }
}
