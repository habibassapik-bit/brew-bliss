<?php

namespace Tests\Feature;

use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_checkout_page(): void
    {
        $response = $this->get('/checkout');

        $response->assertRedirect('/masuk');
    }

    public function test_guest_cannot_create_order(): void
    {
        $response = $this->post('/checkout', [
            'customer_name' => 'Budi',
            'customer_phone' => '081234567890',
            'fulfillment_type' => 'pickup',
        ]);

        $response->assertRedirect('/masuk');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_redirects_to_cart_when_empty(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/checkout');

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('error');
    }

    public function test_checkout_page_shows_summary_for_filled_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['name' => 'Risol Pelangi', 'slug' => 'risol-pelangi', 'price' => 10000, 'stock' => 10]);
        $this->addCartItem($user, $product, 2);

        $response = $this->actingAs($user)->get('/checkout');

        $response->assertOk();
        $response->assertSee('Risol Pelangi');
        $response->assertSee('Rp 20.000');
        $response->assertSee('Buat Pesanan');
    }

    public function test_pickup_checkout_creates_order_without_address(): void
    {
        $user = User::factory()->create(['name' => 'Budi Santoso', 'phone' => '081234567890']);
        $product = Product::factory()->create(['name' => 'Risol Pelangi', 'slug' => 'risol-pelangi', 'price' => 10000, 'stock' => 10]);
        $this->addCartItem($user, $product, 3);

        $response = $this->actingAs($user)->post('/checkout', [
            'customer_name' => 'Budi Santoso',
            'customer_phone' => '081234567890',
            'fulfillment_type' => 'pickup',
            'note' => 'Jangan pedas',
        ]);

        $response->assertRedirect();
        $order = $user->orders()->first();

        $this->assertNotNull($order);
        $this->assertSame(OrderStatus::PendingConfirmation, $order->status);
        $this->assertSame(FulfillmentType::Pickup, $order->fulfillment_type);
        $this->assertNull($order->delivery_address);
        $this->assertSame(30000.0, (float) $order->subtotal);
        $this->assertSame(0.0, (float) $order->delivery_fee);
        $this->assertSame(30000.0, (float) $order->total);
        $this->assertSame('Jangan pedas', $order->note);
        $this->assertSame('Risol Pelangi', $order->items()->first()?->product_name);
        $this->assertDatabaseCount('order_items', 1);
    }

    public function test_delivery_checkout_requires_address(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 10000, 'stock' => 10]);
        $this->addCartItem($user, $product, 1);

        $response = $this->actingAs($user)->post('/checkout', [
            'customer_name' => 'Budi',
            'customer_phone' => '081234567890',
            'fulfillment_type' => 'delivery',
        ]);

        $response->assertSessionHasErrors('address_choice');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_delivery_checkout_accepts_saved_address_and_applies_delivery_fee(): void
    {
        Setting::query()->create(['key' => 'delivery_fee', 'value' => '10000']);

        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create(['price' => 20000, 'stock' => 10]);
        $this->addCartItem($user, $product, 2);

        $response = $this->actingAs($user)->post('/checkout', [
            'customer_name' => 'Budi',
            'customer_phone' => '081234567890',
            'fulfillment_type' => 'delivery',
            'address_choice' => (string) $address->id,
        ]);

        $response->assertRedirect();
        $order = $user->orders()->first();

        $this->assertNotNull($order);
        $this->assertSame(FulfillmentType::Delivery, $order->fulfillment_type);
        $this->assertSame(40000.0, (float) $order->subtotal);
        $this->assertSame(10000.0, (float) $order->delivery_fee);
        $this->assertSame(50000.0, (float) $order->total);
        $this->assertStringContainsString($address->address, (string) $order->delivery_address);
    }

    public function test_delivery_checkout_can_create_new_address(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 10000, 'stock' => 10]);
        $this->addCartItem($user, $product, 1);

        $response = $this->actingAs($user)->post('/checkout', [
            'customer_name' => 'Budi',
            'customer_phone' => '081234567890',
            'fulfillment_type' => 'delivery',
            'address_choice' => 'new',
            'recipient_name' => 'Sari Dewi',
            'address_phone' => '089876543210',
            'address' => 'Jl. Melati No. 5, Kota',
        ]);

        $response->assertRedirect();
        $order = $user->orders()->first();

        $this->assertDatabaseHas('addresses', ['user_id' => $user->id, 'recipient_name' => 'Sari Dewi']);
        $this->assertNotNull($order);
        $this->assertStringContainsString('Jl. Melati No. 5', (string) $order->delivery_address);
    }

    public function test_delivery_checkout_with_new_address_requires_address_fields(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 10000, 'stock' => 10]);
        $this->addCartItem($user, $product, 1);

        $response = $this->actingAs($user)->post('/checkout', [
            'customer_name' => 'Budi',
            'customer_phone' => '081234567890',
            'fulfillment_type' => 'delivery',
            'address_choice' => 'new',
        ]);

        $response->assertSessionHasErrors(['recipient_name', 'address_phone', 'address']);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_delivery_checkout_accepts_empty_new_address_fields_from_browser(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create(['price' => 10000, 'stock' => 10]);
        $this->addCartItem($user, $product, 1);

        $response = $this->actingAs($user)->post('/checkout', [
            'customer_name' => 'Budi',
            'customer_phone' => '081234567890',
            'fulfillment_type' => 'delivery',
            'address_choice' => (string) $address->id,
            'recipient_name' => '',
            'address_phone' => '',
            'address' => '',
        ]);

        $response->assertRedirect();
        $order = $user->orders()->first();

        $this->assertNotNull($order);
        $this->assertStringContainsString($address->address, (string) $order->delivery_address);
    }

    public function test_pickup_checkout_accepts_hidden_delivery_fields_from_browser(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 10000, 'stock' => 10]);
        $this->addCartItem($user, $product, 1);

        $response = $this->actingAs($user)->post('/checkout', [
            'customer_name' => 'Budi',
            'customer_phone' => '081234567890',
            'fulfillment_type' => 'pickup',
            'recipient_name' => '',
            'address_phone' => '',
            'address' => '',
        ]);

        $response->assertRedirect();
        $order = $user->orders()->first();

        $this->assertNotNull($order);
        $this->assertSame(0.0, (float) $order->delivery_fee);
    }

    public function test_delivery_checkout_uses_default_delivery_fee_of_3000(): void
    {
        $this->assertDatabaseCount('settings', 0);

        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 10000, 'stock' => 10]);
        $this->addCartItem($user, $product, 2);

        $response = $this->actingAs($user)->post('/checkout', [
            'customer_name' => 'Budi',
            'customer_phone' => '081234567890',
            'fulfillment_type' => 'delivery',
            'address_choice' => 'new',
            'recipient_name' => 'Sari Dewi',
            'address_phone' => '089876543210',
            'address' => 'Jl. Melati No. 5, Kota',
        ]);

        $response->assertRedirect();
        $order = $user->orders()->first();

        $this->assertNotNull($order);
        $this->assertSame(3000.0, (float) $order->delivery_fee);
        $this->assertSame(23000.0, (float) $order->total);
    }

    public function test_checkout_page_displays_default_delivery_fee_3000(): void
    {
        $this->assertDatabaseCount('settings', 0);

        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 10000, 'stock' => 10]);
        $this->addCartItem($user, $product, 1);

        $response = $this->actingAs($user)->get('/checkout');

        $response->assertOk();
        $response->assertSee('Rp 3.000');
    }

    public function test_customer_cannot_use_other_customers_address(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $otherAddress = Address::factory()->create(['user_id' => $other->id]);
        $product = Product::factory()->create(['price' => 10000, 'stock' => 10]);
        $this->addCartItem($user, $product, 1);

        $response = $this->actingAs($user)->post('/checkout', [
            'customer_name' => 'Budi',
            'customer_phone' => '081234567890',
            'fulfillment_type' => 'delivery',
            'address_choice' => (string) $otherAddress->id,
        ]);

        $response->assertSessionHasErrors('address_choice');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_recalculates_totals_from_server_data(): void
    {
        Setting::query()->create(['key' => 'delivery_fee', 'value' => '10000']);

        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 10000, 'stock' => 10]);
        $this->addCartItem($user, $product, 2);

        $response = $this->actingAs($user)->post('/checkout', [
            'customer_name' => 'Budi',
            'customer_phone' => '081234567890',
            'fulfillment_type' => 'pickup',
            'subtotal' => '1',
            'discount' => '100000',
            'delivery_fee' => '999',
            'total' => '1',
        ]);

        $response->assertRedirect();
        $order = $user->orders()->first();

        $this->assertNotNull($order);
        $this->assertSame(20000.0, (float) $order->subtotal);
        $this->assertSame(0.0, (float) $order->discount_amount);
        $this->assertSame(0.0, (float) $order->delivery_fee);
        $this->assertSame(20000.0, (float) $order->total);
    }

    public function test_checkout_uses_fresh_product_price(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 10000, 'stock' => 10]);
        $this->addCartItem($user, $product, 2);

        $product->update(['price' => 15000]);

        $response = $this->actingAs($user)->post('/checkout', [
            'customer_name' => 'Budi',
            'customer_phone' => '081234567890',
            'fulfillment_type' => 'pickup',
        ]);

        $response->assertRedirect();
        $order = $user->orders()->first();

        $this->assertNotNull($order);
        $this->assertSame(30000.0, (float) $order->subtotal);
        $this->assertSame(15000.0, (float) $order->items()->first()?->product_price);
    }

    public function test_checkout_revalidates_stock_and_fails_when_depleted(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 10000, 'stock' => 5]);
        $this->addCartItem($user, $product, 3);

        $product->update(['stock' => 1]);

        $response = $this->actingAs($user)->post('/checkout', [
            'customer_name' => 'Budi',
            'customer_phone' => '081234567890',
            'fulfillment_type' => 'pickup',
        ]);

        $response->assertSessionHasErrors('cart');
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 1]);
    }

    public function test_checkout_rejects_inactive_product(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 10000, 'stock' => 10]);
        $this->addCartItem($user, $product, 1);

        $product->update(['status' => ProductStatus::Inactive]);

        $response = $this->actingAs($user)->post('/checkout', [
            'customer_name' => 'Budi',
            'customer_phone' => '081234567890',
            'fulfillment_type' => 'pickup',
        ]);

        $response->assertSessionHasErrors('cart');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_rejects_soft_deleted_product(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 10000, 'stock' => 10]);
        $this->addCartItem($user, $product, 1);

        $product->delete();

        $response = $this->actingAs($user)->post('/checkout', [
            'customer_name' => 'Budi',
            'customer_phone' => '081234567890',
            'fulfillment_type' => 'pickup',
        ]);

        $response->assertSessionHasErrors('cart');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_decrements_stock_and_clears_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 10000, 'stock' => 10]);
        $this->addCartItem($user, $product, 4);

        $response = $this->actingAs($user)->post('/checkout', [
            'customer_name' => 'Budi',
            'customer_phone' => '081234567890',
            'fulfillment_type' => 'pickup',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 6]);
        $this->assertDatabaseCount('cart_items', 0);
        $this->assertDatabaseHas('order_status_histories', ['status' => 'pending_confirmation']);
    }

    public function test_checkout_blocked_when_store_is_closed(): void
    {
        Setting::query()->create(['key' => 'store_is_open', 'value' => '0']);

        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 10000, 'stock' => 10]);
        $this->addCartItem($user, $product, 1);

        $response = $this->actingAs($user)->post('/checkout', [
            'customer_name' => 'Budi',
            'customer_phone' => '081234567890',
            'fulfillment_type' => 'pickup',
        ]);

        $response->assertSessionHasErrors('store');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_delivery_rejected_when_disabled(): void
    {
        Setting::query()->create(['key' => 'enable_delivery', 'value' => '0']);

        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 10000, 'stock' => 10]);
        $this->addCartItem($user, $product, 1);

        $response = $this->actingAs($user)->post('/checkout', [
            'customer_name' => 'Budi',
            'customer_phone' => '081234567890',
            'fulfillment_type' => 'delivery',
            'address_choice' => 'new',
            'recipient_name' => 'Sari',
            'address_phone' => '089876543210',
            'address' => 'Jl. Melati No. 5',
        ]);

        $response->assertSessionHasErrors('fulfillment_type');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_pickup_rejected_when_disabled(): void
    {
        Setting::query()->create(['key' => 'enable_pickup', 'value' => '0']);

        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 10000, 'stock' => 10]);
        $this->addCartItem($user, $product, 1);

        $response = $this->actingAs($user)->post('/checkout', [
            'customer_name' => 'Budi',
            'customer_phone' => '081234567890',
            'fulfillment_type' => 'pickup',
        ]);

        $response->assertSessionHasErrors('fulfillment_type');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_revalidates_voucher_and_creates_order_with_discount(): void
    {
        $user = User::factory()->create();
        $voucher = Voucher::factory()->create(['code' => 'PROMO10K', 'value' => 10000]);
        $product = Product::factory()->create(['price' => 50000, 'stock' => 10]);
        $this->addCartItem($user, $product, 1);

        $response = $this->actingAs($user)->post('/checkout', [
            'customer_name' => 'Budi',
            'customer_phone' => '081234567890',
            'fulfillment_type' => 'pickup',
            'voucher_code' => 'PROMO10K',
        ]);

        $response->assertRedirect();
        $order = $user->orders()->first();

        $this->assertNotNull($order);
        $this->assertSame(50000.0, (float) $order->subtotal);
        $this->assertSame(10000.0, (float) $order->discount_amount);
        $this->assertSame(40000.0, (float) $order->total);
        $this->assertSame($voucher->id, $order->voucher_id);
        $this->assertDatabaseHas('vouchers', ['id' => $voucher->id, 'used_count' => 1]);
    }

    public function test_checkout_rejects_exhausted_voucher(): void
    {
        $user = User::factory()->create();
        Voucher::factory()->create(['code' => 'HABIS', 'value' => 10000, 'quota' => 1, 'used_count' => 1]);
        $product = Product::factory()->create(['price' => 50000, 'stock' => 10]);
        $this->addCartItem($user, $product, 1);

        $response = $this->actingAs($user)->post('/checkout', [
            'customer_name' => 'Budi',
            'customer_phone' => '081234567890',
            'fulfillment_type' => 'pickup',
            'voucher_code' => 'HABIS',
        ]);

        $response->assertSessionHasErrors('voucher_code');
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseHas('vouchers', ['code' => 'HABIS', 'used_count' => 1]);
    }

    public function test_order_success_page_only_accessible_by_owner(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $product = Product::factory()->create(['price' => 10000, 'stock' => 10]);
        $this->addCartItem($owner, $product, 1);

        $this->actingAs($owner)->post('/checkout', [
            'customer_name' => 'Budi',
            'customer_phone' => '081234567890',
            'fulfillment_type' => 'pickup',
        ]);

        $order = $owner->orders()->first();

        $this->actingAs($owner)->get("/checkout/sukses/{$order->id}")->assertOk()->assertSee($order->order_number);
        $this->actingAs($other)->get("/checkout/sukses/{$order->id}")->assertNotFound();
    }

    public function test_cart_page_renders_safely_with_soft_deleted_product(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['name' => 'Produk Dihapus', 'slug' => 'produk-dihapus', 'price' => 10000, 'stock' => 10]);
        $this->addCartItem($user, $product, 1);

        $product->delete();

        $response = $this->actingAs($user)->get('/keranjang');

        $response->assertOk();
        $response->assertSee('Produk tidak tersedia');
        $response->assertSee('Hapus');
        $response->assertDontSee('href="'.route('checkout.index'));
    }

    public function test_cart_page_disables_checkout_when_product_inactive(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['name' => 'Produk Nonaktif', 'slug' => 'produk-nonaktif', 'price' => 10000, 'stock' => 10]);
        $this->addCartItem($user, $product, 1);

        $product->update(['status' => ProductStatus::Inactive]);

        $response = $this->actingAs($user)->get('/keranjang');

        $response->assertOk();
        $response->assertSee('Produk Nonaktif');
        $response->assertSee('tidak dapat dipesan');
        $response->assertDontSee('href="'.route('checkout.index'));
    }

    /**
     * Tambahkan item langsung ke keranjang milik user.
     */
    private function addCartItem(User $user, Product $product, int $quantity = 1, ?string $note = null): CartItem
    {
        $cart = Cart::query()->firstOrCreate(['user_id' => $user->id]);

        return $cart->items()->create([
            'product_id' => $product->id,
            'quantity' => $quantity,
            'note' => $note,
        ]);
    }
}
