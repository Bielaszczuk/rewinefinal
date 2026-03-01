<?php

namespace App\Domain\Wine\Contracts;

use App\Models\Wine;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface WineRepositoryInterface
{
    /**
     * Find wine by ID.
     */
    public function findById(string $id): ?Wine;

    /**
     * Find wine by ID or fail.
     */
    public function findByIdOrFail(string $id): Wine;

    /**
     * Get all wines paginated.
     */
    public function getAllPaginated(int $page = 0, int $size = 20, bool $includeInactive = false): LengthAwarePaginator;

    /**
     * Get featured wines paginated.
     */
    public function getFeaturedPaginated(int $page = 0, int $size = 20): LengthAwarePaginator;

    /**
     * Get top rated wines paginated.
     */
    public function getTopRatedPaginated(int $page = 0, int $size = 20): LengthAwarePaginator;

    /**
     * Get recent wines paginated.
     */
    public function getRecentPaginated(int $page = 0, int $size = 20): LengthAwarePaginator;

    /**
     * Search wines with filters.
     */
    public function search(array $filters, int $page = 0, int $size = 20): LengthAwarePaginator;

    /**
     * Get wines by winery ID.
     */
    public function getByWineryId(string $wineryId): Collection;

    /**
     * Create a new wine.
     */
    public function create(array $data): Wine;

    /**
     * Update a wine.
     */
    public function update(string $id, array $data): Wine;

    /**
     * Delete a wine.
     */
    public function delete(string $id): bool;

    /**
     * Check if wine exists.
     */
    public function exists(string $id): bool;

    /**
     * Update rating average.
     */
    public function updateRatingAverage(string $id, float $average, int $count): void;
}
