<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminCustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_and_customer_cannot_access_customer_management(): void
    {
        $customer = User::factory()->create();

        $this->get(route('admin.customers.index'))->assertRedirect('/masuk');
        $this->actingAs($customer)->get(route('admin.customers.index'))->assertForbidden();
        $this->actingAs($customer)->get(route('admin.customers.show', $customer))->assertForbidden();
    }

    public function test_super_admin_can_list_customers(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::create(['name' => 'super_admin']));

        $customer = User::factory()->create(['name' => 'Budi Santoso']);

        $response = $this->actingAs($superAdmin)->get(route('admin.customers.index'));

        $response->assertOk();
        $response->assertSee('Budi Santoso');
        $response->assertDontSee($superAdmin->email);
    }

    public function test_admin_roles_are_not_listed_as_customers(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::create(['name' => 'super_admin']));

        $adminCs = User::factory()->create(['name' => 'CS Operator']);
        $adminCs->assignRole(Role::create(['name' => 'admin_cs']));

        $customer = User::factory()->create(['name' => 'Pelanggan Biasa']);

        $response = $this->actingAs($superAdmin)->get(route('admin.customers.index'));

        $response->assertOk();
        $response->assertSee('Pelanggan Biasa');
        $response->assertDontSee('CS Operator');
        $response->assertDontSee($superAdmin->email);
    }

    public function test_customer_detail_shows_order_summary(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::create(['name' => 'super_admin']));

        $customer = User::factory()->create();
        Order::factory()->paid()->create(['user_id' => $customer->id, 'total' => 50000]);
        Order::factory()->cancelled()->create(['user_id' => $customer->id, 'total' => 20000]);

        $response = $this->actingAs($superAdmin)->get(route('admin.customers.show', $customer));

        $response->assertOk();
        $response->assertSee($customer->name);
        $response->assertSee('Rp 50.000');
    }

    public function test_super_admin_detail_is_blocked(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::create(['name' => 'super_admin']));
        $otherAdmin = User::factory()->create();
        $otherAdmin->assignRole('super_admin');

        $this->actingAs($superAdmin)->get(route('admin.customers.show', $otherAdmin))->assertNotFound();
    }

    public function test_customer_search_works(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::create(['name' => 'super_admin']));

        User::factory()->create(['name' => 'Budi Santoso', 'email' => 'budi@example.com']);
        User::factory()->create(['name' => 'Sari Dewi', 'email' => 'sari@example.com']);

        $response = $this->actingAs($superAdmin)->get(route('admin.customers.index', ['q' => 'budi']));

        $response->assertOk();
        $response->assertSee('Budi Santoso');
        $response->assertDontSee('Sari Dewi');
    }
}
