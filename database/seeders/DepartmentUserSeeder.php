<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DepartmentUserSeeder extends Seeder
{
    public function run(): void
    {
        // ── Departments ──────────────────────────────────────────────
        // name => [display, head_email (set after users created)]
        $departmentDefs = [
            'Management',
            'Operations',
            'Production',
            'Design',
            'Sales',
            'Finance',
            'Human Resources',
            'Marketing & Ecommerce',
            'Projects & Interiors',
        ];

        $departments = [];
        foreach ($departmentDefs as $name) {
            $departments[$name] = Department::firstOrCreate(['name' => $name]);
        }

        // ── Role lookup ──────────────────────────────────────────────
        $roles = Role::pluck('id', 'name');

        // ── Users ────────────────────────────────────────────────────
        // Each entry: department, role, first_name, last_name, email, password
        $users = [
            [
                'department' => 'Management',
                'role' => 'super-admin',
                'first_name' => 'Ruth',
                'last_name' => 'Kayira',
                'email' => 'ruth@myperfectstitch.co.zm',
                'password' => 'Admin@MPS2026!',
            ],
            [
                'department' => 'Operations',
                'role' => 'manager',
                'first_name' => 'Chisomo',
                'last_name' => 'Tembo',
                'email' => 'operations@myperfectstitch.co.zm',
                'password' => 'Ops@MPS2026!',
            ],
            [
                'department' => 'Production',
                'role' => 'production',
                'first_name' => 'John',
                'last_name' => 'Banda',
                'email' => 'production@myperfectstitch.co.zm',
                'password' => 'Prod@MPS2026!',
            ],
            [
                'department' => 'Design',
                'role' => 'design',
                'first_name' => 'Chanda',
                'last_name' => 'Mwale',
                'email' => 'design@myperfectstitch.co.zm',
                'password' => 'Design@MPS2026!',
            ],
            [
                'department' => 'Sales',
                'role' => 'sales',
                'first_name' => 'Mutale',
                'last_name' => 'Phiri',
                'email' => 'sales@myperfectstitch.co.zm',
                'password' => 'Sales@MPS2026!',
            ],
            [
                'department' => 'Finance',
                'role' => 'accounts',
                'first_name' => 'Nalishebo',
                'last_name' => 'Mwamba',
                'email' => 'finance@myperfectstitch.co.zm',
                'password' => 'Finance@MPS2026!',
            ],
            [
                'department' => 'Human Resources',
                'role' => 'hr-manager',
                'first_name' => 'Mwansa',
                'last_name' => 'Kabwe',
                'email' => 'hr@myperfectstitch.co.zm',
                'password' => 'HR@MPS2026!',
            ],
            [
                'department' => 'Marketing & Ecommerce',
                'role' => 'marketing',
                'first_name' => 'Thandiwe',
                'last_name' => 'Lungu',
                'email' => 'marketing@myperfectstitch.co.zm',
                'password' => 'Mktg@MPS2026!',
            ],
            [
                'department' => 'Projects & Interiors',
                'role' => 'projects',
                'first_name' => 'Mwila',
                'last_name' => 'Chileshe',
                'email' => 'projects@myperfectstitch.co.zm',
                'password' => 'Projects@MPS2026!',
            ],
        ];

        foreach ($users as $data) {
            $dept = $departments[$data['department']];
            $roleId = $roles[$data['role']] ?? $roles['super-admin'];

            $user = User::firstOrNew(['email' => $data['email']]);
            $user->fill([
                'role_id' => $roleId,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'department' => $data['department'],
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            if (! $user->exists || config('app.seed_reset_passwords')) {
                $user->password = Hash::make($data['password']);
            }

            $user->save();
        }

        // ── Assign department heads ──────────────────────────────────
        $headMap = [
            'Management' => 'ruth@myperfectstitch.co.zm',
            'Operations' => 'operations@myperfectstitch.co.zm',
            'Production' => 'production@myperfectstitch.co.zm',
            'Design' => 'design@myperfectstitch.co.zm',
            'Sales' => 'sales@myperfectstitch.co.zm',
            'Finance' => 'finance@myperfectstitch.co.zm',
            'Human Resources' => 'hr@myperfectstitch.co.zm',
            'Marketing & Ecommerce' => 'marketing@myperfectstitch.co.zm',
            'Projects & Interiors' => 'projects@myperfectstitch.co.zm',
        ];

        foreach ($headMap as $deptName => $email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $departments[$deptName]->update(['head_of_department' => $user->id]);
            }
        }
    }
}
