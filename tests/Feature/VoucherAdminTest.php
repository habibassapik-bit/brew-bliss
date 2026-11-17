<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VoucherAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_and_customer_cannot_access_voucher_management(): void
    {
        $customer = User::factory()->create();
        $voucher = Voucher::factory()->create();

        $this->get(route('admin.vouchers.index'))->assertRedirect('/masuk');
        $this->actingAs($customer)->get(route('admin.vouchers.index'))->assertForbidden();
        $this->actingAs($customer)->get(route('admin.vouchers.create'))->assertForbidden();
        $this->actingAs($customer)->post(route('admin.vouchers.store'))->assertForbidden();
        $this->actingAs($customer)->delete(route('admin.vouchers.destroy', $voucher))->assertForbidden();
    }

    public function test_super_admin_can_create_fixed_voucher(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::create(['name' => 'super_admin']));

        $response = $this->actingAs($superAdmin)->post(route('admin.vouchers.store'), [
            'code' => 'promo10k',
            'type' => 'fixed',
            'value' => '10000',
            'min_purchase' => '50000',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.vouchers.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('vouchers', [
            'code' => 'PROMO10K',
            'type' => 'fixed',
            'value' => 10000,
            'min_purchase' => 50000,
            'is_active' => true,
        ]);
    }

    public function test_super_admin_can_create_percent_voucher(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::create(['name' => 'super_admin']));

        $response = $this->actingAs($superAdmin)->post(route('admin.vouchers.store'), [
            'code' => 'DISKON20',
            'type' => 'percent',
            'value' => '20',
            'max_discount' => '25000',
            'quota' => '100',
        ]);

        $response->assertRedirect(route('admin.vouchers.index'));
        $this->assertDatabaseHas('vouchers', [
            'code' => 'DISKON20',
            'type' => 'percent',
            'value' => 20,
            'max_discount' => 25000,
            'quota' => 100,
        ]);
    }

    public function test_percent_value_above_100_is_rejected(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::create(['name' => 'super_admin']));

        $response = $this->actingAs($superAdmin)->post(route('admin.vouchers.store'), [
            'code' => 'GILA150',
            'type' => 'percent',
            'value' => '150',
        ]);

        $response->assertSessionHasErrors('value');
        $this->assertDatabaseCount('vouchers', 0);
    }

    public function test_fixed_value_zero_is_rejected(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::create(['name' => 'super_admin']));

        $response = $this->actingAs($superAdmin)->post(route('admin.vouchers.store'), [
            'code' => 'NOL',
            'type' => 'fixed',
            'value' => '0',
        ]);

        $response->assertSessionHasErrors('value');
        $this->assertDatabaseCount('vouchers', 0);
    }

    public function test_voucher_code_must_be_unique(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::create(['name' => 'super_admin']));
        Voucher::factory()->create(['code' => 'SUDAHADA']);

        $response = $this->actingAs($superAdmin)->post(route('admin.vouchers.store'), [
            'code' => 'SUDAHADA',
            'type' => 'fixed',
            'value' => '5000',
        ]);

        $response->assertSessionHasErrors('code');
    }

    public function test_voucher_period_is_validated(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::create(['name' => 'super_admin']));

        $response = $this->actingAs($superAdmin)->post(route('admin.vouchers.store'), [
            'code' => 'MUNDIR',
            'type' => 'fixed',
            'value' => '5000',
            'starts_at' => '2026-12-31T23:59',
            'ends_at' => '2026-01-01T00:00',
        ]);

        $response->assertSessionHasErrors('ends_at');
        $this->assertDatabaseCount('vouchers', 0);
    }

    public function test_super_admin_can_update_voucher(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::create(['name' => 'super_admin']));
        $voucher = Voucher::factory()->create(['code' => 'EDITINI', 'value' => 5000, 'used_count' => 3]);

        $response = $this->actingAs($superAdmin)->put(route('admin.vouchers.update', $voucher), [
            'code' => 'EDITINI',
            'type' => 'fixed',
            'value' => '8000',
            'min_purchase' => '0',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.vouchers.index'));
        $voucher->refresh();
        $this->assertSame(8000.0, (float) $voucher->value);
        $this->assertSame(3, $voucher->used_count, 'used_count tidak boleh berubah saat update.');
    }

    public function test_super_admin_can_deactivate_voucher(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::create(['name' => 'super_admin']));
        $voucher = Voucher::factory()->create(['code' => 'NONAKTIF']);

        $response = $this->actingAs($superAdmin)->delete(route('admin.vouchers.destroy', $voucher));

        $response->assertRedirect(route('admin.vouchers.index'));
        $this->assertDatabaseHas('vouchers', ['id' => $voucher->id, 'is_active' => false]);
    }

    public function test_voucher_list_is_visible(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::create(['name' => 'super_admin']));
        Voucher::factory()->create(['code' => 'TAMPILYA']);

        $response = $this->actingAs($superAdmin)->get(route('admin.vouchers.index'));

        $response->assertOk();
        $response->assertSee('TAMPILYA');
    }
}
