<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminVoucherManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_and_customer_cannot_manage_vouchers(): void
    {
        $customer = User::factory()->create();

        $this->get('/admin/voucher')->assertRedirect('/masuk');
        $this->actingAs($customer)->get('/admin/voucher')->assertForbidden();
        $this->actingAs($customer)->post('/admin/voucher')->assertForbidden();
    }

    public function test_super_admin_can_create_voucher(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::create(['name' => 'super_admin']));

        $response = $this->actingAs($superAdmin)->post('/admin/voucher', [
            'code' => 'promol0k',
            'type' => 'fixed',
            'value' => '10000',
            'min_purchase' => '50000',
            'quota' => '10',
            'is_active' => '1',
        ]);

        $response->assertRedirect('/admin/voucher');
        $this->assertDatabaseHas('vouchers', [
            'code' => 'PROMOL0K',
            'type' => 'fixed',
            'value' => '10000',
            'is_active' => true,
        ]);
    }

    public function test_voucher_code_must_be_unique(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::create(['name' => 'super_admin']));
        Voucher::factory()->create(['code' => 'PROMO10K']);

        $response = $this->actingAs($superAdmin)->post('/admin/voucher', [
            'code' => 'PROMO10K',
            'type' => 'fixed',
            'value' => '5000',
        ]);

        $response->assertSessionHasErrors('code');
        $this->assertDatabaseCount('vouchers', 1);
    }

    public function test_percent_voucher_value_is_limited_to_100(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::create(['name' => 'super_admin']));

        $response = $this->actingAs($superAdmin)->post('/admin/voucher', [
            'code' => 'PERKENALAH',
            'type' => 'percent',
            'value' => '150',
        ]);

        $response->assertSessionHasErrors('value');
        $this->assertDatabaseCount('vouchers', 0);
    }

    public function test_voucher_period_must_be_valid(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::create(['name' => 'super_admin']));

        $response = $this->actingAs($superAdmin)->post('/admin/voucher', [
            'code' => 'KEBALIK',
            'type' => 'fixed',
            'value' => '5000',
            'starts_at' => '2026-12-31',
            'ends_at' => '2026-01-01',
        ]);

        $response->assertSessionHasErrors('ends_at');
        $this->assertDatabaseCount('vouchers', 0);
    }

    public function test_update_does_not_change_used_count(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::create(['name' => 'super_admin']));

        $voucher = Voucher::factory()->create([
            'code' => 'PROMO10K',
            'type' => 'fixed',
            'value' => 10000,
            'quota' => 10,
            'used_count' => 5,
        ]);

        $response = $this->actingAs($superAdmin)->put("/admin/voucher/{$voucher->id}", [
            'code' => 'PROMO10K',
            'type' => 'fixed',
            'value' => '20000',
            'quota' => '20',
            'is_active' => '1',
        ]);

        $response->assertRedirect('/admin/voucher');
        $voucher->refresh();

        $this->assertSame(20000.0, (float) $voucher->value);
        $this->assertSame(5, $voucher->used_count, 'Riwayat penggunaan tidak boleh berubah saat voucher diedit.');
    }

    public function test_deactivating_voucher_keeps_transaction_history(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::create(['name' => 'super_admin']));

        $voucher = Voucher::factory()->create(['code' => 'PROMO10K', 'used_count' => 3]);

        $response = $this->actingAs($superAdmin)->delete("/admin/voucher/{$voucher->id}");

        $response->assertRedirect('/admin/voucher');
        $voucher->refresh();

        $this->assertFalse($voucher->is_active);
        $this->assertDatabaseHas('vouchers', ['id' => $voucher->id, 'code' => 'PROMO10K', 'used_count' => 3]);
    }
}
