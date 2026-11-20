<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Role::ROLE_PERMISSIONS as $name => $permissions) {
            Role::updateOrCreate(
                ['name' => $name],
                [
                    'display_name' => Role::DISPLAY_NAMES[$name],
                    'permissions' => $permissions,
                ]
            );
        }
    }
}
