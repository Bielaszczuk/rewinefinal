<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\WineRoute\Contracts\WineRouteRepositoryInterface;
use App\Models\WineRoute;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class WineRouteRepository implements WineRouteRepositoryInterface
{
    public function findById(string $id): ?WineRoute
    {
        return WineRoute::with(['stops', 'wineries', 'creator'])->find($id);
    }

    public function findByIdOrFail(string $id): WineRoute
    {
        return WineRoute::with(['stops', 'wineries', 'creator'])->findOrFail($id);
    }

    public function getAllPaginated(array $filters, int $page = 0, int $size = 20): LengthAwarePaginator
    {
        $query = WineRoute::withCount(['stops', 'wineries'])
            ->where('status', 'active');

        if (!empty($filters['country'])) {
            $query->where('country', $filters['country']);
        }
        if (!empty($filters['region'])) {
            $query->where('region', $filters['region']);
        }
        if (!empty($filters['subregion'])) {
            $query->where('subregion', $filters['subregion']);
        }
        if (!empty($filters['search'])) {
            $s = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($s) {
                $q->where('name', 'ilike', $s)->orWhere('description', 'ilike', $s);
            });
        }

        $sort = $filters['sort'] ?? 'name';
        $query->orderBy($sort, 'asc');

        $size = min($size, 100);

        return $query->paginate($size, ['*'], 'page', $page + 1);
    }

    public function getAllPaginatedIncludingInactive(array $filters, int $page = 0, int $size = 20): LengthAwarePaginator
    {
        $query = WineRoute::withCount(['stops', 'wineries']);

        if (!empty($filters['search'])) {
            $s = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($s) {
                $q->where('name', 'ilike', $s)->orWhere('description', 'ilike', $s);
            });
        }

        $query->orderBy('created_at', 'desc');

        $size = min($size, 100);

        return $query->paginate($size, ['*'], 'page', $page + 1);
    }

    public function getHierarchy(): array
    {
        $routes = WineRoute::where('status', 'active')
            ->select('country', 'region', 'subregion')
            ->whereNotNull('country')
            ->get();

        $tree = [];
        foreach ($routes as $route) {
            $country = $route->country;
            $region  = $route->region;
            $sub     = $route->subregion;

            if (!isset($tree[$country])) {
                $tree[$country] = [];
            }
            if ($region && !isset($tree[$country][$region])) {
                $tree[$country][$region] = [];
            }
            if ($region && $sub && !in_array($sub, $tree[$country][$region])) {
                $tree[$country][$region][] = $sub;
            }
        }

        $countries = [];
        foreach ($tree as $cName => $regions) {
            $regionList = [];
            foreach ($regions as $rName => $subs) {
                $regionList[] = ['name' => $rName, 'subregions' => array_values(array_unique($subs))];
            }
            $countries[] = ['name' => $cName, 'regions' => $regionList];
        }

        return ['countries' => $countries];
    }

    public function getCountries(): array
    {
        return WineRoute::where('status', 'active')
            ->whereNotNull('country')
            ->distinct()
            ->pluck('country')
            ->sort()
            ->values()
            ->toArray();
    }

    public function getRegions(string $country): array
    {
        return WineRoute::where('status', 'active')
            ->where('country', $country)
            ->whereNotNull('region')
            ->distinct()
            ->pluck('region')
            ->sort()
            ->values()
            ->toArray();
    }

    public function getSubregions(string $country, string $region): array
    {
        return WineRoute::where('status', 'active')
            ->where('country', $country)
            ->where('region', $region)
            ->whereNotNull('subregion')
            ->distinct()
            ->pluck('subregion')
            ->sort()
            ->values()
            ->toArray();
    }

    public function create(array $data): WineRoute
    {
        $route = WineRoute::create($data);

        if (!empty($data['stops'])) {
            foreach ($data['stops'] as $index => $stop) {
                $route->stops()->create(array_merge($stop, ['stop_order' => $index + 1]));
            }
        }

        if (!empty($data['winery_ids'])) {
            $route->wineries()->sync($data['winery_ids']);
        }

        return $route->load(['stops', 'wineries']);
    }

    public function update(string $id, array $data): WineRoute
    {
        $route = $this->findByIdOrFail($id);
        $route->update($data);

        if (isset($data['stops'])) {
            $route->stops()->delete();
            foreach ($data['stops'] as $index => $stop) {
                $route->stops()->create(array_merge($stop, ['stop_order' => $index + 1]));
            }
        }

        if (isset($data['winery_ids'])) {
            $route->wineries()->sync($data['winery_ids']);
        }

        return $route->fresh(['stops', 'wineries']);
    }

    public function delete(string $id): bool
    {
        $route = $this->findByIdOrFail($id);
        return $route->delete();
    }

    public function exists(string $id): bool
    {
        return WineRoute::where('id', $id)->exists();
    }
}

