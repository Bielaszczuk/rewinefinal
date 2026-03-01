<?php

namespace App\Application\Wine\Services;

use App\Domain\Wine\Contracts\WineRepositoryInterface;
use App\Domain\Wine\DTOs\CreateWineDto;
use App\Domain\Wine\DTOs\UpdateWineDto;
use App\Domain\Wine\Exceptions\WineNotFoundException;
use App\Models\Wine;
use Illuminate\Support\Facades\DB;

class WineCommandService
{
    public function __construct(
        private readonly WineRepositoryInterface $repository
    ) {}

    public function create(CreateWineDto $dto): Wine
    {
        return DB::transaction(function () use ($dto) {
            return $this->repository->create($dto->toArray());
        });
    }

    public function update(string $id, UpdateWineDto $dto): Wine
    {
        if (!$this->repository->exists($id)) {
            throw new WineNotFoundException("Wine with ID {$id} not found");
        }

        return DB::transaction(function () use ($id, $dto) {
            return $this->repository->update($id, $dto->toArray());
        });
    }

    public function delete(string $id): bool
    {
        if (!$this->repository->exists($id)) {
            throw new WineNotFoundException("Wine with ID {$id} not found");
        }

        return DB::transaction(function () use ($id) {
            return $this->repository->delete($id);
        });
    }

    public function toggleFeatured(string $id): Wine
    {
        $wine = $this->repository->findByIdOrFail($id);

        return DB::transaction(function () use ($wine) {
            $wine->update(['is_featured' => !$wine->is_featured]);
            return $wine->fresh(['winery']);
        });
    }

    public function toggleActive(string $id): Wine
    {
        $wine = $this->repository->findByIdOrFail($id);

        return DB::transaction(function () use ($wine) {
            $wine->update(['is_active' => !$wine->is_active]);
            return $wine->fresh(['winery']);
        });
    }

    public function updateRating(string $id, float $average, int $count): void
    {
        if (!$this->repository->exists($id)) {
            throw new WineNotFoundException("Wine with ID {$id} not found");
        }

        $this->repository->updateRatingAverage($id, $average, $count);
    }
}
