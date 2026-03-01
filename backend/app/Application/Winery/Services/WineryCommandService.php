<?php

namespace App\Application\Winery\Services;

use App\Domain\Winery\Contracts\WineryRepositoryInterface;
use App\Domain\Winery\DTOs\CreateWineryDto;
use App\Domain\Winery\DTOs\UpdateWineryDto;
use App\Domain\Winery\Exceptions\WineryNotFoundException;
use App\Models\Winery;
use Illuminate\Support\Facades\DB;

class WineryCommandService
{
    public function __construct(
        private readonly WineryRepositoryInterface $repository
    ) {}

    public function create(CreateWineryDto $dto): Winery
    {
        return DB::transaction(function () use ($dto) {
            return $this->repository->create($dto->toArray());
        });
    }

    public function update(string $id, UpdateWineryDto $dto): Winery
    {
        if (!$this->repository->exists($id)) {
            throw new WineryNotFoundException("Winery with ID {$id} not found");
        }

        return DB::transaction(function () use ($id, $dto) {
            return $this->repository->update($id, $dto->toArray());
        });
    }

    public function delete(string $id): bool
    {
        if (!$this->repository->exists($id)) {
            throw new WineryNotFoundException("Winery with ID {$id} not found");
        }

        return DB::transaction(function () use ($id) {
            return $this->repository->delete($id);
        });
    }
}
