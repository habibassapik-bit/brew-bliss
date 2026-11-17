<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_admin(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/masuk');
        $this->assertGuest();
    }

    public function test_customer_cannot_access_admin_dashboard(): void
    {
        $customer = User::factory()->create();

        $response = $this->actingAs($customer)->get('/admin');

        $response->assertForbidden();
    }

    public function test_customer_service_role_cannot_access_admin_dashboard(): void
    {
        $adminCs = User::factory()->create();
        $adminCs->assignRole(Role::create(['name' => 'admin_cs']));

        $response = $this->actingAs($adminCs)->get('/admin');

        $response->assertForbidden();
    }

    public function test_super_admin_can_access_admin_dashboard(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::create(['name' => 'super_admin']));

        $response = $this->actingAs($superAdmin)->get('/admin');

        $response->assertOk();
        $response->assertSee('Dashboard Super Admin');
        $response->assertSee($superAdmin->name);
    }

    public function test_super_admin_is_redirected_to_admin_dashboard_after_login(): void
    {
        $superAdmin = User::factory()->create(['password' => 'rahasia123']);
        $superAdmin->assignRole(Role::create(['name' => 'super_admin']));

        $response = $this->post('/masuk', [
            'email' => $superAdmin->email,
            'password' => 'rahasia123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($superAdmin);
    }

    public function test_customer_is_redirected_to_home_after_login(): void
    {
        $customer = User::factory()->create(['password' => 'rahasia123']);

        $response = $this->post('/masuk', [
            'email' => $customer->email,
            'password' => 'rahasia123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($customer);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $inactive = User::factory()->create(['password' => 'rahasia123', 'is_active' => false]);

        $response = $this->post('/masuk', [
            'email' => $inactive->email,
            'password' => 'rahasia123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
