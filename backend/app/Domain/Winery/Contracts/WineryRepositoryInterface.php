<?php

namespace App\Domain\Winery\Contracts;

use App\Models\Winery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface WineryRepositoryInterface
{
    public function findById(string $id): ?Winery;

    public function findByIdOrFail(string $id): Winery;

    public function getAllPaginated(int $page = 0, int $size = 20): LengthAwarePaginator;

    public function search(array $filters, int $page = 0, int $size = 20): LengthAwarePaginator;

    public function getByCountry(string $country): Collection;

    public function getByRegion(string $region): Collection;

    public function create(array $data): Winery;

    public function update(string $id, array $data): Winery;

    public function delete(string $id): bool;

    public function exists(string $id): bool;
}
