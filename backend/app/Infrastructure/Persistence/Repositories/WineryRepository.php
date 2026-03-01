<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Winery\Contracts\WineryRepositoryInterface;
use App\Models\Winery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class WineryRepository implements WineryRepositoryInterface
{
    public function findById(string $id): ?Winery
    {
        return Winery::find($id);
    }

    public function findByIdOrFail(string $id): Winery
    {
        return Winery::findOrFail($id);
    }

    public function getAllPaginated(int $page = 0, int $size = 20): LengthAwarePaginator
    {
        return Winery::orderBy('name')
            ->paginate($size, ['*'], 'page', $page + 1);
    }

    public function search(array $filters, int $page = 0, int $size = 20): LengthAwarePaginator
    {
        $query = Winery::query();

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', $search)
                  ->orWhere('description', 'ilike', $search);
            });
        }

        if (!empty($filters['country'])) {
            $query->where('country', $filters['country']);
        }

        if (!empty($filters['region'])) {
            $query->where('region', $filters['region']);
        }

        return $query->orderBy('name')
            ->paginate($size, ['*'], 'page', $page + 1);
    }

    public function getByCountry(string $country): Collection
    {
        return Winery::where('country', $country)
            ->orderBy('name')
            ->get();
    }

    public function getByRegion(string $region): Collection
    {
        return Winery::where('region', $region)
            ->orderBy('name')
            ->get();
    }

    public function create(array $data): Winery
    {
        return Winery::create($data);
    }

    public function update(string $id, array $data): Winery
    {
        $winery = $this->findByIdOrFail($id);
        $winery->update($data);
        return $winery->fresh();
    }

    public function delete(string $id): bool
    {
        $winery = $this->findByIdOrFail($id);
        return $winery->delete();
    }

    public function exists(string $id): bool
    {
        return Winery::where('id', $id)->exists();
    }
}
