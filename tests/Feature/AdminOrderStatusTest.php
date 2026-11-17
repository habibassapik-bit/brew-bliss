<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminOrderStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_and_customer_cannot_manage_orders(): void
    {
        $customer = User::factory()->create();
        $order = Order::factory()->create();

        $this->get(route('admin.orders.index'))->assertRedirect('/masuk');
        $this->actingAs($customer)->get(route('admin.orders.index'))->assertForbidden();
        $this->actingAs($customer)->get(route('admin.orders.show', $order))->assertForbidden();
        $this->actingAs($customer)->patch(route('admin.orders.status', $order))->assertForbidden();
    }

    public function test_super_admin_can_list_and_view_orders(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::create(['name' => 'super_admin']));

        $order = Order::factory()->create(['order_number' => 'BB-ADMN-0001']);
        OrderItem::factory()->create(['order_id' => $order->id, 'product_name' => 'Choco Lava']);

        $response = $this->actingAs($superAdmin)->get(route('admin.orders.index'));

        $response->assertOk();
        $response->assertSee('BB-ADMN-0001');

        $response = $this->actingAs($superAdmin)->get(route('admin.orders.show', $order));

        $response->assertOk();
        $response->assertSee('Choco Lava');
        $response->assertSee('Riwayat Status');
    }

    public function test_valid_status_transition_is_recorded(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::create(['name' => 'super_admin']));

        $order = Order::factory()->create();
        $order->statusHistories()->create(['status' => OrderStatus::PendingConfirmation, 'note' => 'Pesanan dibuat.']);

        $response = $this->actingAs($superAdmin)->patch(route('admin.orders.status', $order), [
            'status' => OrderStatus::Processing->value,
            'note' => 'Konfirmasi manual admin',
        ]);

        $response->assertRedirect(route('admin.orders.show', $order));
        $order->refresh();

        $this->assertSame(OrderStatus::Processing, $order->status);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'status' => 'processing',
            'changed_by' => $superAdmin->id,
        ]);
    }

    public function test_invalid_status_transition_is_rejected(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::create(['name' => 'super_admin']));

        $order = Order::factory()->completed()->create();
        $historyCount = $order->statusHistories()->count();

        $response = $this->actingAs($superAdmin)->patch(route('admin.orders.status', $order), [
            'status' => OrderStatus::PendingConfirmation->value,
        ]);

        $response->assertSessionHasErrors('status');
        $order->refresh();
        $this->assertSame(OrderStatus::Completed, $order->status);
        $this->assertSame($historyCount, $order->statusHistories()->count());
    }

    public function test_admin_cancel_records_history_and_restores_stock_once(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::create(['name' => 'super_admin']));

        $product = Product::factory()->create(['stock' => 5]);
        $order = Order::factory()->create();
        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_price' => 10000,
            'product_cost' => 5000,
            'quantity' => 3,
            'subtotal' => 30000,
        ]);

        $response = $this->actingAs($superAdmin)->post(route('admin.orders.cancel', $order), [
            'cancellation_reason' => 'Tidak bisa memenuhi pesanan',
        ]);

        $response->assertRedirect(route('admin.orders.show', $order));
        $order->refresh();

        $this->assertSame(OrderStatus::Cancelled, $order->status);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 8]);
        $this->assertDatabaseHas('order_status_histories', ['order_id' => $order->id, 'status' => 'cancelled']);

        // Pembatalan berulang tidak boleh menambah stok dua kali.
        $response = $this->actingAs($superAdmin)->post(route('admin.orders.cancel', $order), [
            'cancellation_reason' => 'Coba batalkan lagi',
        ]);

        $response->assertSessionHasErrors('status');
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 8]);
    }

    public function test_completed_order_cannot_be_cancelled_by_admin(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::create(['name' => 'super_admin']));

        $order = Order::factory()->completed()->create();

        $response = $this->actingAs($superAdmin)->post(route('admin.orders.cancel', $order), [
            'cancellation_reason' => 'Coba batalkan selesai',
        ]);

        $response->assertSessionHasErrors('status');
        $order->refresh();
        $this->assertSame(OrderStatus::Completed, $order->status);
    }

    public function test_cod_order_can_be_processed_without_payment_verification(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::create(['name' => 'super_admin']));

        $order = Order::factory()->create();
        $order->payment()->create([
            'method' => PaymentMethod::Cod,
            'status' => PaymentStatus::Unpaid,
            'amount' => $order->total,
        ]);

        $response = $this->actingAs($superAdmin)->patch(route('admin.orders.status', $order), [
            'status' => OrderStatus::Processing->value,
        ]);

        $response->assertRedirect();
        $order->refresh();
        $this->assertSame(OrderStatus::Processing, $order->status);
        $this->assertSame(PaymentStatus::Unpaid, $order->payment_status);
    }
}
