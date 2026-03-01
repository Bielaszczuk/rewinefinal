<?php

namespace App\Http\Controllers\Admin;

use App\Application\User\Services\UserCommandService;
use App\Application\User\Services\UserQueryService;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\User\UserCollection;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserAdminController extends Controller
{
    public function __construct(
        private readonly UserQueryService $queryService,
        private readonly UserCommandService $commandService
    ) {}

    public function index(Request $request)
    {
        $users = $this->queryService->getAllPaginated(
            page: (int) $request->query('page', 0),
            size: min((int) $request->query('size', 20), 100)
        );

        return UserCollection::make($users);
    }

    public function show(string $id)
    {
        $user = $this->queryService->findByIdOrFail($id);

        return UserResource::make($user);
    }

    public function update(UpdateUserRequest $request, string $id)
    {
        $user = $this->commandService->update($id, $request->toDto());

        return UserResource::make($user);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->commandService->delete($id);

        return response()->json([
            'message' => 'User deleted successfully',
            'id' => $id,
        ], 200);
    }

    public function assignRole(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'role' => ['required', 'string', 'in:admin,moderator,partner,user'],
        ]);

        $this->commandService->assignRole($id, $request->input('role'));

        return response()->json([
            'message' => 'Role assigned successfully',
        ], 200);
    }
}
