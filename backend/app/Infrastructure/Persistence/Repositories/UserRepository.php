<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\User\Contracts\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class UserRepository implements UserRepositoryInterface
{
    public function findById(string $id): ?User
    {
        return User::with('role')->find($id);
    }

    public function findByIdOrFail(string $id): User
    {
        return User::with('role')->findOrFail($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::with('role')->where('email', $email)->first();
    }

    public function getAllPaginated(int $page = 0, int $size = 20): LengthAwarePaginator
    {
        return User::with('role')
            ->orderBy('created_at', 'desc')
            ->paginate($size, ['*'], 'page', $page + 1);
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(string $id, array $data): User
    {
        $user = $this->findByIdOrFail($id);
        $user->update($data);
        return $user->fresh(['role']);
    }

    public function delete(string $id): bool
    {
        $user = $this->findByIdOrFail($id);
        return $user->delete();
    }

    public function exists(string $id): bool
    {
        return User::where('id', $id)->exists();
    }

    public function assignRole(string $userId, string $role): void
    {
        $roleId = DB::table('roles')->where('name', $role)->value('id');

        if ($roleId) {
            User::where('id', $userId)->update(['role_id' => $roleId]);
        }
    }
}
