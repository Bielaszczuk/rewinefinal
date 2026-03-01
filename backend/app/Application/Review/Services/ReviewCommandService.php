<?php

namespace App\Application\Review\Services;

use App\Domain\Review\Contracts\ReviewRepositoryInterface;
use App\Domain\Review\DTOs\CreateReviewDto;
use App\Domain\Review\DTOs\UpdateReviewDto;
use App\Domain\Review\Exceptions\ReviewNotFoundException;
use App\Models\Review;
use Illuminate\Support\Facades\DB;

class ReviewCommandService
{
    public function __construct(
        private readonly ReviewRepositoryInterface $repository
    ) {}

    public function create(CreateReviewDto $dto): Review
    {
        return DB::transaction(function () use ($dto) {
            return $this->repository->create($dto->toArray());
        });
    }

    public function update(string $id, UpdateReviewDto $dto): Review
    {
        if (!$this->repository->exists($id)) {
            throw new ReviewNotFoundException("Review with ID {$id} not found");
        }

        return DB::transaction(function () use ($id, $dto) {
            return $this->repository->update($id, $dto->toArray());
        });
    }

    public function delete(string $id): bool
    {
        if (!$this->repository->exists($id)) {
            throw new ReviewNotFoundException("Review with ID {$id} not found");
        }

        return DB::transaction(function () use ($id) {
            return $this->repository->delete($id);
        });
    }

    public function likeReview(string $reviewId, string $userId): void
    {
        if (!$this->repository->exists($reviewId)) {
            throw new ReviewNotFoundException("Review with ID {$reviewId} not found");
        }

        $this->repository->likeReview($reviewId, $userId);
    }

    public function unlikeReview(string $reviewId, string $userId): void
    {
        if (!$this->repository->exists($reviewId)) {
            throw new ReviewNotFoundException("Review with ID {$reviewId} not found");
        }

        $this->repository->unlikeReview($reviewId, $userId);
    }
}
