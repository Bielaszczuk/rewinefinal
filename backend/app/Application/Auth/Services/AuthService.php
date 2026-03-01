<?php

namespace App\Application\Auth\Services;

use App\Models\RefreshToken;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function register(array $data, string $ip, string $userAgent): array
    {
        if (User::where('username', $data['username'])->exists()) {
            abort(409, json_encode(['code' => 'E3001', 'message' => 'Username already taken']));
        }
        if (User::where('email', $data['email'])->exists()) {
            abort(409, json_encode(['code' => 'E3002', 'message' => 'Email already registered']));
        }

        $user = User::create([
            'username'      => $data['username'],
            'email'         => $data['email'],
            'password_hash' => Hash::make($data['password']),
            'name'          => $data['name'] ?? null,
        ]);

        $userRole = Role::where('name', 'ROLE_USER')->first();
        if ($userRole) {
            $user->roles()->attach($userRole->id);
        }

        return $this->issueTokens($user, $ip, $userAgent);
    }

    public function login(string $usernameOrEmail, string $password, string $ip, string $userAgent): array
    {
        $user = User::where('username', $usernameOrEmail)
            ->orWhere('email', $usernameOrEmail)
            ->first();

        if (!$user || !Hash::check($password, $user->password_hash)) {
            abort(401, json_encode(['code' => 'E2001', 'message' => 'Invalid credentials']));
        }

        if (!$user->isAccountActive()) {
            abort(403, json_encode(['code' => 'E2003', 'message' => 'Account is disabled or locked']));
        }

        $user->update(['last_login_at' => now()]);

        return $this->issueTokens($user, $ip, $userAgent);
    }

    public function refresh(string $rawRefreshToken, string $ip, string $userAgent): array
    {
        $hash = hash('sha256', $rawRefreshToken);
        $token = RefreshToken::where('token_hash', $hash)->first();

        if (!$token || !$token->isValid()) {
            abort(401, json_encode(['code' => 'E2002', 'message' => 'Invalid or expired refresh token']));
        }

        $user = $token->user;

        $token->update([
            'revoked_at' => now(),
            'revoked_reason' => 'rotated',
        ]);

        $tokens = $this->issueTokens($user, $ip, $userAgent);

        $newHash = hash('sha256', $tokens['refreshToken']);
        $token->update(['replaced_by_token_hash' => $newHash]);

        return $tokens;
    }

    public function logout(string $rawRefreshToken): void
    {
        $hash = hash('sha256', $rawRefreshToken);
        RefreshToken::where('token_hash', $hash)
            ->whereNull('revoked_at')
            ->update([
                'revoked_at' => now(),
                'revoked_reason' => 'logout',
            ]);
    }

    private function issueTokens(User $user, string $ip, string $userAgent): array
    {
        $accessToken = JWTAuth::fromUser($user);
        $rawRefresh  = Str::random(43);
        $refreshHash = hash('sha256', $rawRefresh);

        RefreshToken::create([
            'user_id'     => $user->id,
            'token_hash'  => $refreshHash,
            'device_info' => $userAgent,
            'ip_address'  => $ip,
            'expires_at'  => Carbon::now()->addMinutes(config('jwt.refresh_ttl', 10080)),
            'created_at'  => now(),
        ]);

        $ttlMs = config('jwt.ttl', 15) * 60 * 1000;

        return [
            'accessToken'  => $accessToken,
            'refreshToken' => $rawRefresh,
            'tokenType'    => 'Bearer',
            'expiresIn'    => $ttlMs,
            'user'         => $this->formatUser($user),
        ];
    }

    public function formatUser(User $user): array
    {
        return [
            'id'            => $user->id,
            'username'      => $user->username,
            'email'         => $user->email,
            'name'          => $user->name,
            'avatarUrl'     => $user->avatar_url,
            'roles'         => $user->getRoleNames(),
            'emailVerified' => (bool) $user->email_verified,
        ];
    }
}

