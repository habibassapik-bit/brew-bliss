<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontVoucherTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_apply_voucher(): void
    {
        $response = $this->post('/checkout/voucher', ['voucher_code' => 'PROMO']);

        $response->assertRedirect('/masuk');
    }

    public function test_customer_can_apply_valid_voucher(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 50000, 'stock' => 10]);
        $this->addCartItem($user, $product, 1);
        Voucher::factory()->create(['code' => 'PROMO10K', 'value' => 10000]);

        $response = $this->actingAs($user)->post('/checkout/voucher', ['voucher_code' => 'promo10k']);

        $response->assertRedirect(route('checkout.index'));
        $response->assertSessionHas('status');
        $response->assertSessionHas('checkout_voucher_code', 'PROMO10K');
    }

    public function test_unknown_voucher_code_is_rejected(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 50000, 'stock' => 10]);
        $this->addCartItem($user, $product, 1);

        $response = $this->actingAs($user)->post('/checkout/voucher', ['voucher_code' => 'TIDAKADA']);

        $response->assertSessionHasErrors('voucher_code');
    }

    public function test_inactive_voucher_is_rejected(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 50000, 'stock' => 10]);
        $this->addCartItem($user, $product, 1);
        Voucher::factory()->inactive()->create(['code' => 'NONAKTIF']);

        $response = $this->actingAs($user)->post('/checkout/voucher', ['voucher_code' => 'NONAKTIF']);

        $response->assertSessionHasErrors('voucher_code');
        $this->assertNull(session('checkout_voucher_code'));
    }

    public function test_expired_voucher_is_rejected(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 50000, 'stock' => 10]);
        $this->addCartItem($user, $product, 1);
        Voucher::factory()->expired()->create(['code' => 'LAMABANGET']);

        $response = $this->actingAs($user)->post('/checkout/voucher', ['voucher_code' => 'LAMABANGET']);

        $response->assertSessionHasErrors('voucher_code');
    }

    public function test_future_voucher_is_rejected(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 50000, 'stock' => 10]);
        $this->addCartItem($user, $product, 1);
        Voucher::factory()->create(['code' => 'BELUMSAH', 'starts_at' => now()->addDay()]);

        $response = $this->actingAs($user)->post('/checkout/voucher', ['voucher_code' => 'BELUMSAH']);

        $response->assertSessionHasErrors('voucher_code');
    }

    public function test_exhausted_voucher_is_rejected(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 50000, 'stock' => 10]);
        $this->addCartItem($user, $product, 1);
        Voucher::factory()->withQuota(5, 5)->create(['code' => 'KUOTAHABIS']);

        $response = $this->actingAs($user)->post('/checkout/voucher', ['voucher_code' => 'KUOTAHABIS']);

        $response->assertSessionHasErrors('voucher_code');
    }

    public function test_voucher_below_minimum_purchase_is_rejected(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 10000, 'stock' => 10]);
        $this->addCartItem($user, $product, 1);
        Voucher::factory()->create(['code' => 'MINIMAL', 'value' => 10000, 'min_purchase' => 50000]);

        $response = $this->actingAs($user)->post('/checkout/voucher', ['voucher_code' => 'MINIMAL']);

        $response->assertSessionHasErrors('voucher_code');
    }

    public function test_voucher_meeting_minimum_purchase_is_accepted(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 25000, 'stock' => 10]);
        $this->addCartItem($user, $product, 2);
        Voucher::factory()->create(['code' => 'MINIMAL', 'value' => 10000, 'min_purchase' => 50000]);

        $response = $this->actingAs($user)->post('/checkout/voucher', ['voucher_code' => 'MINIMAL']);

        $response->assertRedirect(route('checkout.index'));
        $response->assertSessionHas('status');
    }

    public function test_fixed_voucher_discount_is_calculated_correctly(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 25000, 'stock' => 10]);
        $this->addCartItem($user, $product, 2);
        Voucher::factory()->create(['code' => 'POTONG5K', 'value' => 5000]);

        $response = $this->actingAs($user)->post('/checkout/voucher', ['voucher_code' => 'POTONG5K']);

        $response->assertSessionHas('status', fn (string $message) => str_contains($message, 'Rp 5.000'));
    }

    public function test_percent_voucher_discount_is_calculated_correctly(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 25000, 'stock' => 10]);
        $this->addCartItem($user, $product, 2);
        Voucher::factory()->percent(10)->create(['code' => 'DISKON10', 'value' => 10]);

        $response = $this->actingAs($user)->post('/checkout/voucher', ['voucher_code' => 'DISKON10']);

        $response->assertSessionHas('status', fn (string $message) => str_contains($message, 'Rp 5.000'));
    }

    public function test_percent_voucher_discount_is_capped_by_max_discount(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 100000, 'stock' => 10]);
        $this->addCartItem($user, $product, 2);
        Voucher::factory()->percent(50, 20000)->create(['code' => 'DISKON50', 'value' => 50]);

        $response = $this->actingAs($user)->post('/checkout/voucher', ['voucher_code' => 'DISKON50']);

        $response->assertSessionHas('status', fn (string $message) => str_contains($message, 'Rp 20.000'));
    }

    public function test_discount_is_not_stored_when_only_applying_voucher(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 50000, 'stock' => 10]);
        $this->addCartItem($user, $product, 1);
        $voucher = Voucher::factory()->create(['code' => 'PROMO10K', 'value' => 10000]);

        $this->actingAs($user)->post('/checkout/voucher', ['voucher_code' => 'PROMO10K']);

        $this->assertDatabaseHas('vouchers', ['id' => $voucher->id, 'used_count' => 0]);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_voucher_code_is_case_insensitive(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 50000, 'stock' => 10]);
        $this->addCartItem($user, $product, 1);
        Voucher::factory()->create(['code' => 'PROMO10K', 'value' => 10000]);

        $response = $this->actingAs($user)->post('/checkout/voucher', ['voucher_code' => 'promo10k']);

        $response->assertSessionHas('checkout_voucher_code', 'PROMO10K');
    }

    /**
     * Tambahkan item langsung ke keranjang milik user.
     */
    private function addCartItem(User $user, Product $product, int $quantity = 1): void
    {
        $cart = Cart::query()->firstOrCreate(['user_id' => $user->id]);
        $cart->items()->create(['product_id' => $product->id, 'quantity' => $quantity]);
    }
}
