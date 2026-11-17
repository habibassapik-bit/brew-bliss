<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_order_history(): void
    {
        $this->get('/pesanan')->assertRedirect('/masuk');
    }

    public function test_customer_sees_only_own_orders(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $ownOrder = Order::factory()->create(['user_id' => $user->id, 'order_number' => 'BB-OWN-0001']);
        Order::factory()->create(['user_id' => $other->id, 'order_number' => 'BB-OTHER-01']);

        $response = $this->actingAs($user)->get('/pesanan');

        $response->assertOk();
        $response->assertSee('BB-OWN-0001');
        $response->assertDontSee('BB-OTHER-01');
        $this->assertSame(1, $ownOrder->user_id === $user->id ? 1 : 0);
    }

    public function test_order_history_shows_empty_state(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/pesanan');

        $response->assertOk();
        $response->assertSee('Belum ada pesanan');
    }

    public function test_customer_cannot_view_other_users_order(): void
    {
        $user = User::factory()->create();
        $otherOrder = Order::factory()->create(['user_id' => User::factory()->create()->id]);

        $this->actingAs($user)->get(route('orders.show', $otherOrder))->assertNotFound();
    }

    public function test_order_detail_uses_transaction_snapshot_not_current_price(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'subtotal' => 20000,
            'total' => 20000,
        ]);
        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => Product::factory()->create(['price' => 99999])->id,
            'product_name' => 'Risol Pelangi',
            'product_price' => 10000,
            'product_cost' => 5000,
            'quantity' => 2,
            'subtotal' => 20000,
        ]);

        // Harga produk saat ini berubah setelah transaksi; snapshot tidak boleh ikut berubah.
        Product::query()->first()->update(['price' => 25000]);

        $response = $this->actingAs($user)->get(route('orders.show', $order));

        $response->assertOk();
        $response->assertSee('Risol Pelangi');
        $response->assertSee('Rp 20.000');
        $response->assertDontSee('Rp 25.000');
    }

    public function test_customer_can_cancel_own_pending_order_once_and_stock_restored_once(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 10000, 'stock' => 5]);
        $order = Order::factory()->create(['user_id' => $user->id]);
        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_price' => 10000,
            'product_cost' => 5000,
            'quantity' => 2,
            'subtotal' => 20000,
        ]);

        $response = $this->actingAs($user)->post(route('orders.cancel', $order), [
            'cancellation_reason' => 'Berubah pikiran',
        ]);

        $response->assertRedirect(route('orders.show', $order));
        $order->refresh();
        $this->assertSame(OrderStatus::Cancelled, $order->status);
        $this->assertSame('Berubah pikiran', $order->cancellation_reason);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 7]);
        $this->assertDatabaseHas('order_status_histories', ['order_id' => $order->id, 'status' => 'cancelled']);

        // Pembatalan berulang harus ditolak dan stok tidak bertambah dua kali.
        $response = $this->actingAs($user)->post(route('orders.cancel', $order), [
            'cancellation_reason' => 'Coba batalkan lagi',
        ]);

        $response->assertSessionHasErrors('cancellation');
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 7]);
    }

    public function test_completed_order_cannot_be_cancelled_by_customer(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->completed()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('orders.cancel', $order), [
            'cancellation_reason' => 'Berubah pikiran',
        ]);

        $response->assertSessionHasErrors('cancellation');
        $order->refresh();
        $this->assertSame(OrderStatus::Completed, $order->status);
    }

    public function test_customer_cannot_cancel_other_users_order(): void
    {
        $user = User::factory()->create();
        $otherOrder = Order::factory()->create(['user_id' => User::factory()->create()->id]);

        $response = $this->actingAs($user)->post(route('orders.cancel', $otherOrder), [
            'cancellation_reason' => 'Berubah pikiran',
        ]);

        $response->assertNotFound();
        $otherOrder->refresh();
        $this->assertNotSame(OrderStatus::Cancelled, $otherOrder->status);
    }

    public function test_order_detail_shows_status_history_for_tracking(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $order->statusHistories()->create(['status' => OrderStatus::PendingConfirmation, 'note' => 'Pesanan dibuat oleh pelanggan.']);
        $order->statusHistories()->create(['status' => OrderStatus::Processing, 'note' => 'Pesanan diproses.']);
        $order->update(['status' => OrderStatus::Processing]);

        $response = $this->actingAs($user)->get(route('orders.show', $order));

        $response->assertOk();
        $response->assertSee('Riwayat Status');
        $response->assertSee('Pesanan dibuat oleh pelanggan.');
        $response->assertSee('Pesanan diproses.');
        $response->assertSee('Status saat ini');
    }
}
