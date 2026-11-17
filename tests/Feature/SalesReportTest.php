<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\OperationalCost;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SalesReportTest extends TestCase
{
    use RefreshDatabase;

    private function createSuperAdmin(): User
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::firstOrCreate(['name' => 'super_admin']));

        return $superAdmin;
    }

    /**
     * Buat pesanan valid (paid, tidak batal) dengan angka yang pasti.
     */
    private function createValidOrder(array $attributes = [], array $item = []): Order
    {
        $order = Order::factory()->paid()->create($attributes + [
            'status' => OrderStatus::Completed,
            'subtotal' => 10000,
            'discount_amount' => 1000,
            'delivery_fee' => 2000,
            'total' => 11000,
        ]);

        OrderItem::query()->create($item + [
            'order_id' => $order->id,
            'product_id' => Product::factory()->create(['cost_price' => 1500])->id,
            'product_name' => 'Produk Uji',
            'product_price' => 5000,
            'product_cost' => 1500,
            'quantity' => 2,
            'subtotal' => 10000,
        ]);

        return $order;
    }

    public function test_guest_and_customer_cannot_access_reports(): void
    {
        $customer = User::factory()->create();

        $this->get(route('admin.reports.index'))->assertRedirect('/masuk');
        $this->actingAs($customer)->get(route('admin.reports.index'))->assertForbidden();
        $this->actingAs($customer)->get(route('admin.reports.export'))->assertForbidden();
    }

    public function test_super_admin_can_access_reports(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $this->actingAs($superAdmin)->get(route('admin.reports.index'))->assertOk();
    }

    public function test_revenue_counts_only_paid_and_not_cancelled_orders(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $this->createValidOrder(['order_number' => 'BB-VALID-0001']);
        Order::factory()->cancelled()->create(['total' => 50000, 'order_number' => 'BB-CANCELLED-0001']);
        Order::factory()->create([
            'status' => OrderStatus::PendingConfirmation,
            'payment_status' => PaymentStatus::Unpaid,
            'total' => 60000,
            'order_number' => 'BB-UNPAID-0001',
        ]);

        $response = $this->actingAs($superAdmin)->get(route('admin.reports.index'));

        $response->assertOk();
        $response->assertSee('Rp 11.000');
        $response->assertSee('BB-VALID-0001');
        $response->assertDontSee('BB-CANCELLED-0001');
        $response->assertDontSee('BB-UNPAID-0001');
    }

    public function test_period_filter_limits_report_data(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $this->createValidOrder(['order_number' => 'BB-TODAY-0001']);
        $this->createValidOrder(['order_number' => 'BB-YESTERDAY-0001', 'created_at' => Carbon::yesterday()]);

        $today = $this->actingAs($superAdmin)->get(route('admin.reports.index', ['period' => 'today']));
        $today->assertOk();
        $today->assertSee('BB-TODAY-0001');
        $today->assertDontSee('BB-YESTERDAY-0001');

        $last7 = $this->actingAs($superAdmin)->get(route('admin.reports.index', ['period' => 'last7']));
        $last7->assertOk();
        $last7->assertSee('BB-TODAY-0001');
        $last7->assertSee('BB-YESTERDAY-0001');
    }

    public function test_custom_range_is_applied_when_valid(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $this->createValidOrder(['order_number' => 'BB-RANGE-0001', 'created_at' => Carbon::yesterday()]);

        $response = $this->actingAs($superAdmin)->get(route('admin.reports.index', [
            'period' => 'custom',
            'from' => Carbon::yesterday()->toDateString(),
            'to' => Carbon::today()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertSee('BB-RANGE-0001');
        $response->assertSee('Rp 11.000');
    }

    public function test_invalid_custom_range_falls_back_safely(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $this->createValidOrder(['order_number' => 'BB-OLD-0001', 'created_at' => Carbon::yesterday()]);

        $inverted = $this->actingAs($superAdmin)->get(route('admin.reports.index', [
            'period' => 'custom',
            'from' => Carbon::today()->toDateString(),
            'to' => Carbon::yesterday()->toDateString(),
        ]));

        $inverted->assertOk();
        $inverted->assertDontSee('BB-OLD-0001');

        $malformed = $this->actingAs($superAdmin)->get(route('admin.reports.index', [
            'period' => 'custom',
            'from' => 'bukan-tanggal',
            'to' => 'juga-bukan',
        ]));

        $malformed->assertOk();
        $malformed->assertDontSee('BB-OLD-0001');

        $unknown = $this->actingAs($superAdmin)->get(route('admin.reports.index', ['period' => 'nilai-aneh']));

        $unknown->assertOk();
        $unknown->assertDontSee('BB-OLD-0001');
    }

    public function test_profit_uses_snapshot_hpp_not_current_product_cost(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $product = Product::factory()->create(['price' => 5000, 'cost_price' => 1500]);
        $this->createValidOrder([], [
            'product_id' => $product->id,
            'product_cost' => 1500,
        ]);

        $product->update(['cost_price' => 9000]);

        $response = $this->actingAs($superAdmin)->get(route('admin.reports.index'));

        $response->assertOk();
        $response->assertSee('Rp 11.000');
        $response->assertSee('Rp 3.000');
        $response->assertSee('Rp 8.000');
    }

    public function test_profit_calculation_includes_operational_costs(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $this->createValidOrder();

        OperationalCost::query()->create([
            'recorded_by' => $superAdmin->id,
            'name' => 'Gas dapur',
            'category' => 'operational',
            'amount' => 500,
            'incurred_at' => Carbon::today(),
        ]);

        $response = $this->actingAs($superAdmin)->get(route('admin.reports.index'));

        $response->assertOk();
        $response->assertSee('Rp 11.000');
        $response->assertSee('Rp 3.000');
        $response->assertSee('Rp 8.000');
        $response->assertSee('Rp 500');
        $response->assertSee('Rp 7.500');
    }

    public function test_report_details_are_paginated(): void
    {
        $superAdmin = $this->createSuperAdmin();

        for ($i = 1; $i <= 20; $i++) {
            $this->createValidOrder(['order_number' => sprintf('BB-PAGE-%04d', $i)]);
        }

        $response = $this->actingAs($superAdmin)->get(route('admin.reports.index'));

        $response->assertOk();
        $response->assertSee('BB-PAGE-0001');
        $response->assertDontSee('BB-PAGE-0020');
    }
}
