<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'ROLE_USER',      'description' => 'Default registered user'],
            ['name' => 'ROLE_ADMIN',     'description' => 'Full system administrator'],
            ['name' => 'ROLE_MODERATOR', 'description' => 'Content moderator'],
            ['name' => 'ROLE_PARTNER',   'description' => 'Partner – can create events'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
