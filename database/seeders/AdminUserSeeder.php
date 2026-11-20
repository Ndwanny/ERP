<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminRole = Role::where('name', 'super-admin')->first();

        $user = User::firstOrNew(['email' => 'admin@myperfectstitch.co.zm']);
        $user->fill([
            'role_id' => $superAdminRole->id,
            'first_name' => 'Ruth',
            'last_name' => 'Kayira',
            'email' => 'admin@myperfectstitch.co.zm',
            'department' => 'Management',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        if (! $user->exists || config('app.seed_reset_passwords')) {
            $user->password = Hash::make(config('app.seeded_admin_password'));
        }

        $user->save();
    }
}
