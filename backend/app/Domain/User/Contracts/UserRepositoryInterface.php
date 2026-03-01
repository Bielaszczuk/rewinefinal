<?php

namespace App\Domain\User\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function findById(string $id): ?User;

    public function findByIdOrFail(string $id): User;

    public function findByEmail(string $email): ?User;

    public function getAllPaginated(int $page = 0, int $size = 20): LengthAwarePaginator;

    public function create(array $data): User;

    public function update(string $id, array $data): User;

    public function delete(string $id): bool;

    public function exists(string $id): bool;

    public function assignRole(string $userId, string $role): void;
}
