<?php

namespace App\Application\User\Services;

use App\Domain\User\Contracts\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserQueryService
{
    public function __construct(
        private readonly UserRepositoryInterface $repository
    ) {}

    public function findById(string $id): ?User
    {
        return $this->repository->findById($id);
    }

    public function findByIdOrFail(string $id): User
    {
        return $this->repository->findByIdOrFail($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->repository->findByEmail($email);
    }

    public function getAllPaginated(int $page = 0, int $size = 20): LengthAwarePaginator
    {
        return $this->repository->getAllPaginated($page, $size);
    }

    public function exists(string $id): bool
    {
        return $this->repository->exists($id);
    }
}
