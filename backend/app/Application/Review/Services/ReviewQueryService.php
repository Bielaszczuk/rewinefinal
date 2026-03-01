<?php

namespace App\Application\Review\Services;

use App\Domain\Review\Contracts\ReviewRepositoryInterface;
use App\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ReviewQueryService
{
    public function __construct(
        private readonly ReviewRepositoryInterface $repository
    ) {}

    public function findById(string $id): ?Review
    {
        return $this->repository->findById($id);
    }

    public function findByIdOrFail(string $id): Review
    {
        return $this->repository->findByIdOrFail($id);
    }

    public function getByWine(string $wineId, string $sortBy = 'recent', int $page = 0, int $size = 20): LengthAwarePaginator
    {
        return $this->repository->getByWine($wineId, $sortBy, $page, $size);
    }

    public function getByUser(string $userId): Collection
    {
        return $this->repository->getByUser($userId);
    }

    public function isLikedByUser(string $reviewId, string $userId): bool
    {
        return $this->repository->isLikedByUser($reviewId, $userId);
    }

    public function exists(string $id): bool
    {
        return $this->repository->exists($id);
    }
}
