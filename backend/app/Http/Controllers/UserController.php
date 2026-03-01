<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Http\Responses\PageResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = $request->query('search');
        $query = User::with('roles')->orderBy('created_at', 'desc');

        if ($q) {
            $query->where(function ($qb) use ($q) {
                $qb->where('username', 'ilike', "%$q%")
                   ->orWhere('email', 'ilike', "%$q%")
                   ->orWhere('name', 'ilike', "%$q%");
            });
        }

        $size = min((int) $request->query('size', 20), 100);
        $page = (int) $request->query('page', 0) + 1;

        $paginator = $query->paginate($size, ['*'], 'page', $page);
        return response()->json(PageResponse::from($paginator, fn($u) => $this->formatUser($u)));
    }

    public function show(string $id): JsonResponse
    {
        $user = User::with('roles')->findOrFail($id);
        return response()->json($this->formatUser($user));
    }

    public function updateMe(Request $request): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();

        $validated = $request->validate([
            'name'      => 'nullable|string|max:100',
            'avatarUrl' => 'nullable|url|max:500',
        ]);

        $user->update(array_filter([
            'name'       => $validated['name'] ?? null,
            'avatar_url' => $validated['avatarUrl'] ?? null,
        ], fn($v) => $v !== null));

        return response()->json($this->formatUser($user->fresh('roles')));
    }

    public function changePassword(Request $request): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();

        $request->validate([
            'currentPassword' => 'required|string',
            'newPassword'     => 'required|string|min:8|max:100',
        ]);

        if (!Hash::check($request->input('currentPassword'), $user->password_hash)) {
            abort(400, json_encode(['code' => 'E2001', 'message' => 'Current password is incorrect']));
        }

        $user->update(['password_hash' => Hash::make($request->input('newPassword'))]);
        return response()->json(['message' => 'Password updated successfully']);
    }

    public function getStats(Request $request): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();

        $reviewsCount = \App\Models\Review::where('user_id', $user->id)->count();
        $cellarCount = \App\Models\CellarEntry::where('user_id', $user->id)->sum('quantity');

        $eventsAttended = 0;

        return response()->json([
            'reviewsCount' => $reviewsCount,
            'cellarCount' => $cellarCount,
            'eventsAttended' => $eventsAttended,
        ]);
    }

    public function updateRole(Request $request, string $id): JsonResponse
    {
        $request->validate(['role' => 'required|string|exists:roles,name']);

        $user = User::with('roles')->findOrFail($id);
        $role = Role::where('name', $request->input('role'))->firstOrFail();

        $keepIds = $user->roles->where('name', 'ROLE_USER')->pluck('id');
        $syncIds = $keepIds->push($role->id)->unique()->values()->all();
        $user->roles()->sync($syncIds);

        return response()->json($this->formatUser($user->fresh('roles')));
    }

    public function ban(Request $request, string $id): JsonResponse
    {
        $request->validate(['reason' => 'nullable|string|max:255']);
        $user = User::findOrFail($id);
        $user->update(['locked' => true, 'lock_reason' => $request->input('reason', 'Banned by admin')]);
        return response()->json(['message' => 'User banned']);
    }

    public function unban(string $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->update(['locked' => false, 'lock_reason' => null]);
        return response()->json(['message' => 'User unbanned']);
    }

    public function destroy(string $id): JsonResponse
    {
        User::findOrFail($id)->delete();
        return response()->json(null, 204);
    }

    private function formatUser(User $user): array
    {
        return [
            'id'            => $user->id,
            'username'      => $user->username,
            'email'         => $user->email,
            'name'          => $user->name,
            'avatarUrl'     => $user->avatar_url,
            'roles'         => $user->roles->pluck('name')->values()->all(),
            'emailVerified' => (bool) $user->email_verified,
            'enabled'       => (bool) $user->enabled,
            'locked'        => (bool) $user->locked,
            'lockReason'    => $user->lock_reason,
            'lastLoginAt'   => $user->last_login_at?->toISOString(),
            'createdAt'     => $user->created_at?->toISOString(),
            'updatedAt'     => $user->updated_at?->toISOString(),
        ];
    }
}
