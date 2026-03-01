<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Review\Contracts\ReviewRepositoryInterface;
use App\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReviewRepository implements ReviewRepositoryInterface
{
    public function findById(string $id): ?Review
    {
        return Review::with(['user', 'wine'])->find($id);
    }

    public function findByIdOrFail(string $id): Review
    {
        return Review::with(['user', 'wine'])->findOrFail($id);
    }

    public function getByWine(string $wineId, string $sortBy = 'recent', int $page = 0, int $size = 20): LengthAwarePaginator
    {
        $query = Review::with('user')->where('wine_id', $wineId);

        $query = match ($sortBy) {
            'likes', 'popular' => $query->orderBy('likes_count', 'desc'),
            'rating_high' => $query->orderBy('rating', 'desc'),
            'rating_low' => $query->orderBy('rating', 'asc'),
            default => $query->orderBy('created_at', 'desc'),
        };

        return $query->paginate($size, ['*'], 'page', $page + 1);
    }

    public function getByUser(string $userId): Collection
    {
        return Review::with('wine')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function create(array $data): Review
    {
        $review = Review::create($data);
        return $review->load('user');
    }

    public function update(string $id, array $data): Review
    {
        $review = $this->findByIdOrFail($id);
        $review->update($data);
        return $review->fresh(['user', 'wine']);
    }

    public function delete(string $id): bool
    {
        $review = $this->findByIdOrFail($id);
        return $review->delete();
    }

    public function exists(string $id): bool
    {
        return Review::where('id', $id)->exists();
    }

    public function likeReview(string $reviewId, string $userId): void
    {
        DB::table('review_likes')->insert([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'review_id' => $reviewId,
            'user_id' => $userId,
            'created_at' => now(),
        ]);
        $this->updateLikeCount($reviewId);
    }

    public function unlikeReview(string $reviewId, string $userId): void
    {
        DB::table('review_likes')
            ->where('review_id', $reviewId)
            ->where('user_id', $userId)
            ->delete();
        $this->updateLikeCount($reviewId);
    }

    public function isLikedByUser(string $reviewId, string $userId): bool
    {
        return DB::table('review_likes')
            ->where('review_id', $reviewId)
            ->where('user_id', $userId)
            ->exists();
    }

    public function updateLikeCount(string $reviewId): void
    {
        $count = DB::table('review_likes')
            ->where('review_id', $reviewId)
            ->count();

        Review::where('id', $reviewId)->update(['likes_count' => $count]);
    }
}
