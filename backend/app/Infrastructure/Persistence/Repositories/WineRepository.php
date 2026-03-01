<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Wine\Contracts\WineRepositoryInterface;
use App\Models\Wine;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class WineRepository implements WineRepositoryInterface
{
    public function findById(string $id): ?Wine
    {
        return Wine::with('winery')->find($id);
    }

    public function findByIdOrFail(string $id): Wine
    {
        return Wine::with('winery')->findOrFail($id);
    }

    public function getAllPaginated(int $page = 0, int $size = 20, bool $includeInactive = false): LengthAwarePaginator
    {
        $query = Wine::with('winery');

        if (!$includeInactive) {
            $query->where('is_active', true);
        }

        return $query->orderBy('name')
            ->paginate($size, ['*'], 'page', $page + 1);
    }

    public function getFeaturedPaginated(int $page = 0, int $size = 20): LengthAwarePaginator
    {
        return Wine::with('winery')
            ->where('is_featured', true)
            ->where('is_active', true)
            ->orderByDesc('rating_average')
            ->paginate($size, ['*'], 'page', $page + 1);
    }

    public function getTopRatedPaginated(int $page = 0, int $size = 20): LengthAwarePaginator
    {
        return Wine::with('winery')
            ->where('is_active', true)
            ->where('rating_count', '>', 0)
            ->orderByDesc('rating_average')
            ->orderByDesc('rating_count')
            ->paginate($size, ['*'], 'page', $page + 1);
    }

    public function getRecentPaginated(int $page = 0, int $size = 20): LengthAwarePaginator
    {
        return Wine::with('winery')
            ->where('is_active', true)
            ->orderByDesc('created_at')
            ->paginate($size, ['*'], 'page', $page + 1);
    }

    public function search(array $filters, int $page = 0, int $size = 20): LengthAwarePaginator
    {
        $query = Wine::with('winery')->where('is_active', true);

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($search) {
                $q->where('wines.name', 'ilike', $search)
                  ->orWhereHas('winery', fn($w) => $w->where('name', 'ilike', $search))
                  ->orWhere('description_en', 'ilike', $search);
            });
        }

        if (!empty($filters['wineType'])) {
            $query->where('wine_type', strtoupper($filters['wineType']));
        }

        if (!empty($filters['wineryId'])) {
            $query->where('winery_id', $filters['wineryId']);
        }

        if (!empty($filters['country'])) {
            $query->whereHas('winery', fn($w) => $w->where('country', $filters['country']));
        }

        if (!empty($filters['region'])) {
            $query->whereHas('winery', fn($w) => $w->where('region', $filters['region']));
        }

        if (!empty($filters['grapeVariety'])) {
            $grape = $filters['grapeVariety'];
            $query->where(function ($q) use ($grape) {
                $q->whereJsonContains('grapes', $grape)
                  ->orWhereRaw("grapes::text ilike ?", ['%' . $grape . '%']);
            });
        }

        if (!empty($filters['vintage'])) {
            $query->where('vintage', $filters['vintage']);
        }

        if (isset($filters['minPrice'])) {
            $query->where('price_min', '>=', $filters['minPrice']);
        }

        if (isset($filters['maxPrice'])) {
            $query->where('price_max', '<=', $filters['maxPrice']);
        }

        if (isset($filters['minRating'])) {
            $query->where('rating_average', '>=', $filters['minRating']);
        }

        if (isset($filters['featured'])) {
            $query->where('is_featured', filter_var($filters['featured'], FILTER_VALIDATE_BOOLEAN));
        }

        $sortColumnMap = [
            'NAME' => 'wines.name',
            'VINTAGE' => 'vintage',
            'PRICE' => 'price_min',
            'RATING' => 'rating_average',
            'CREATED_AT' => 'wines.created_at',
        ];

        $sortBy = $sortColumnMap[strtoupper($filters['sortBy'] ?? 'NAME')] ?? 'wines.name';
        $direction = strtoupper($filters['sortDirection'] ?? 'ASC') === 'DESC' ? 'desc' : 'asc';

        $query->orderBy($sortBy, $direction);

        return $query->paginate($size, ['*'], 'page', $page + 1);
    }

    public function getByWineryId(string $wineryId): Collection
    {
        return Wine::where('winery_id', $wineryId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function create(array $data): Wine
    {
        return Wine::create($data);
    }

    public function update(string $id, array $data): Wine
    {
        $wine = $this->findByIdOrFail($id);
        $wine->update($data);
        $wine->refresh();
        $wine->load('winery');

        return $wine;
    }

    public function delete(string $id): bool
    {
        $wine = $this->findByIdOrFail($id);
        return $wine->delete();
    }

    public function exists(string $id): bool
    {
        return Wine::where('id', $id)->exists();
    }

    public function updateRatingAverage(string $id, float $average, int $count): void
    {
        Wine::where('id', $id)->update([
            'rating_average' => $average,
            'rating_count' => $count,
        ]);
    }
}
