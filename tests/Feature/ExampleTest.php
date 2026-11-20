<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_dashboard_renders_for_authenticated_users(): void
    {
        $this->seed(RoleSeeder::class);

        $user = User::create([
            'role_id' => Role::where('name', 'super-admin')->value('id'),
            'first_name' => 'Ruth',
            'last_name' => 'Kayira',
            'email' => 'admin@myperfectstitch.co.zm',
            'email_verified_at' => now(),
            'password' => bcrypt('ChangeMe@2026!'),
        ]);

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee('My Perfect Stitch');
    }

    public function test_production_role_only_sees_its_modules_and_stats(): void
    {
        $this->seed(RoleSeeder::class);
        $user = $this->userWithRole('production');

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee('Order & Production')
            ->assertSee('Low Stock Items')
            ->assertDontSee('Smart Invoicing')
            ->assertDontSee('Revenue (ZMW)');

        $this->get(route('production.index'))->assertOk();
        $this->get(route('invoicing.index'))->assertForbidden();
        $this->get(route('hr.index'))->assertForbidden();
    }

    public function test_sales_role_only_sees_its_modules_and_stats(): void
    {
        $this->seed(RoleSeeder::class);
        $user = $this->userWithRole('sales');

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee('CRM')
            ->assertSee('Pending Invoices')
            ->assertDontSee('HR & Payroll')
            ->assertDontSee('Accounting');

        $this->get(route('crm.index'))->assertOk();
        $this->get(route('accounting.index'))->assertForbidden();
    }

    public function test_super_admin_sees_admin_module(): void
    {
        $this->seed(RoleSeeder::class);
        $user = $this->userWithRole('super-admin');

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee('Super Admin');

        $this->get(route('admin.index'))->assertOk();
    }

    private function userWithRole(string $role): User
    {
        return User::create([
            'role_id' => Role::where('name', $role)->value('id'),
            'first_name' => ucfirst($role),
            'last_name' => 'Tester',
            'email' => $role.'@example.test',
            'email_verified_at' => now(),
            'password' => bcrypt('ChangeMe@2026!'),
        ]);
    }
}
