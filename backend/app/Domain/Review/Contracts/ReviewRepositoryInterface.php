<?php

namespace App\Domain\Review\Contracts;

use App\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ReviewRepositoryInterface
{
    public function findById(string $id): ?Review;

    public function findByIdOrFail(string $id): Review;

    public function getByWine(string $wineId, string $sortBy = 'recent', int $page = 0, int $size = 20): LengthAwarePaginator;

    public function getByUser(string $userId): Collection;

    public function create(array $data): Review;

    public function update(string $id, array $data): Review;

    public function delete(string $id): bool;

    public function exists(string $id): bool;

    public function likeReview(string $reviewId, string $userId): void;

    public function unlikeReview(string $reviewId, string $userId): void;

    public function isLikedByUser(string $reviewId, string $userId): bool;

    public function updateLikeCount(string $reviewId): void;
}
