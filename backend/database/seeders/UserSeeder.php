<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'id'            => (string) Str::uuid(),
                'username'      => 'admin',
                'email'         => 'admin@rewine.local',
                'password_hash' => Hash::make('Rewine123!'),
                'name'          => 'Admin User',
                'enabled'       => true,
                'email_verified' => true,
                'role'          => 'ROLE_ADMIN',
            ],
            [
                'id'            => (string) Str::uuid(),
                'username'      => 'partner',
                'email'         => 'partner@rewine.local',
                'password_hash' => Hash::make('Rewine123!'),
                'name'          => 'Partner User',
                'enabled'       => true,
                'email_verified' => true,
                'role'          => 'ROLE_PARTNER',
            ],
            [
                'id'            => (string) Str::uuid(),
                'username'      => 'user',
                'email'         => 'user@rewine.local',
                'password_hash' => Hash::make('Rewine123!'),
                'name'          => 'Regular User',
                'enabled'       => true,
                'email_verified' => true,
                'role'          => 'ROLE_USER',
            ],
        ];

        foreach ($users as $userData) {
            $roleName = $userData['role'];
            unset($userData['role']);

            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            $role     = Role::where('name', $roleName)->first();
            $userRole = Role::where('name', 'ROLE_USER')->first();

            if ($role && !$user->roles()->where('role_id', $role->id)->exists()) {
                $user->roles()->attach($role->id);
            }
            // Also attach ROLE_USER if not already admin/user
            if ($userRole && $roleName !== 'ROLE_USER' && !$user->roles()->where('role_id', $userRole->id)->exists()) {
                $user->roles()->attach($userRole->id);
            }
        }
    }
}
