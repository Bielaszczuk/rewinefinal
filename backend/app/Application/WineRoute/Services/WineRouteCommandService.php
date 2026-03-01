<?php

namespace App\Application\WineRoute\Services;

use App\Domain\WineRoute\Contracts\WineRouteRepositoryInterface;
use App\Domain\WineRoute\Exceptions\WineRouteNotFoundException;
use App\Models\WineRoute;
use Illuminate\Support\Facades\DB;

class WineRouteCommandService
{
    public function __construct(
        private readonly WineRouteRepositoryInterface $repository
    ) {}

    public function create(array $data): WineRoute
    {
        return DB::transaction(function () use ($data) {
            return $this->repository->create($data);
        });
    }

    public function update(string $id, array $data): WineRoute
    {
        if (!$this->repository->exists($id)) {
            throw new WineRouteNotFoundException("Wine route with ID {$id} not found");
        }

        return DB::transaction(function () use ($id, $data) {
            return $this->repository->update($id, $data);
        });
    }

    public function delete(string $id): bool
    {
        if (!$this->repository->exists($id)) {
            throw new WineRouteNotFoundException("Wine route with ID {$id} not found");
        }

        return DB::transaction(function () use ($id) {
            return $this->repository->delete($id);
        });
    }
}

