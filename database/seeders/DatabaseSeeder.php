<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            AdminUserSeeder::class,
            DepartmentUserSeeder::class,
            ChartOfAccountsSeeder::class,
            SettingsSeeder::class,
        ]);

        if (app()->environment('local') || config('app.seed_demo_data')) {
            $this->call(DemoDataSeeder::class);
        }
    }
}
