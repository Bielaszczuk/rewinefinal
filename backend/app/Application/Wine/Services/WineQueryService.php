<?php

namespace App\Application\Wine\Services;

use App\Domain\Wine\Contracts\WineRepositoryInterface;
use App\Models\Wine;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class WineQueryService
{
    public function __construct(
        private readonly WineRepositoryInterface $repository
    ) {}

    public function findById(string $id): ?Wine
    {
        return $this->repository->findById($id);
    }

    public function findByIdOrFail(string $id): Wine
    {
        return $this->repository->findByIdOrFail($id);
    }

    public function getDetails(string $id, ?string $userId = null): array
    {
        $wine = $this->repository->findByIdOrFail($id);

        $inCellar = false;
        if ($userId) {
            $inCellar = $wine->cellarWines()
                ->where('user_id', $userId)
                ->exists();
        }

        return [
            'wine' => $wine,
            'inCellar' => $inCellar,
        ];
    }

    public function getAllPaginated(int $page = 0, int $size = 20, bool $includeInactive = false): LengthAwarePaginator
    {
        return $this->repository->getAllPaginated($page, $size, $includeInactive);
    }

    public function search(array $filters, int $page = 0, int $size = 20): LengthAwarePaginator
    {
        return $this->repository->search($filters, $page, $size);
    }

    public function getFeatured(int $page = 0, int $size = 20): LengthAwarePaginator
    {
        return $this->repository->getFeaturedPaginated($page, $size);
    }

    public function getTopRated(int $page = 0, int $size = 20): LengthAwarePaginator
    {
        return $this->repository->getTopRatedPaginated($page, $size);
    }

    public function getRecent(int $page = 0, int $size = 20): LengthAwarePaginator
    {
        return $this->repository->getRecentPaginated($page, $size);
    }

    public function getRecommended(int $page = 0, int $size = 20): LengthAwarePaginator
    {
        return $this->repository->getTopRatedPaginated($page, $size);
    }

    public function getPopular(int $page = 0, int $size = 20): LengthAwarePaginator
    {
        return $this->repository->getTopRatedPaginated($page, $size);
    }

    public function getByWineryId(string $wineryId): Collection
    {
        return $this->repository->getByWineryId($wineryId);
    }

    public function exists(string $id): bool
    {
        return $this->repository->exists($id);
    }
}
