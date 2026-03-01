<?php

namespace App\Application\User\Services;

use App\Domain\User\Contracts\UserRepositoryInterface;
use App\Domain\User\DTOs\CreateUserDto;
use App\Domain\User\DTOs\UpdateUserDto;
use App\Domain\User\Exceptions\UserNotFoundException;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserCommandService
{
    public function __construct(
        private readonly UserRepositoryInterface $repository
    ) {}

    public function create(CreateUserDto $dto): User
    {
        return DB::transaction(function () use ($dto) {
            return $this->repository->create($dto->toArray());
        });
    }

    public function update(string $id, UpdateUserDto $dto): User
    {
        if (!$this->repository->exists($id)) {
            throw new UserNotFoundException("User with ID {$id} not found");
        }

        return DB::transaction(function () use ($id, $dto) {
            return $this->repository->update($id, $dto->toArray());
        });
    }

    public function delete(string $id): bool
    {
        if (!$this->repository->exists($id)) {
            throw new UserNotFoundException("User with ID {$id} not found");
        }

        return DB::transaction(function () use ($id) {
            return $this->repository->delete($id);
        });
    }

    public function assignRole(string $userId, string $role): void
    {
        if (!$this->repository->exists($userId)) {
            throw new UserNotFoundException("User with ID {$userId} not found");
        }

        $this->repository->assignRole($userId, $role);
    }
}
