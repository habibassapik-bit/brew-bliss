<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontProductDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_detail_shows_product_information(): void
    {
        $category = Category::factory()->create(['name' => 'Makanan', 'slug' => 'makanan']);

        $product = Product::factory()->create([
            'name' => 'Dimsum Ayam',
            'slug' => 'dimsum-ayam',
            'price' => 12000,
            'stock' => 25,
            'description' => 'Dimsum ayam homemade.',
            'category_id' => $category->id,
        ]);

        $response = $this->get('/produk/dimsum-ayam');

        $response->assertOk();
        $response->assertSee('Dimsum Ayam');
        $response->assertSee('Dimsum ayam homemade.');
        $response->assertSee('Rp 12.000');
        $response->assertSee('Makanan');
        $response->assertSee('Stok 25');
        $response->assertSee('Tambah ke Keranjang');
    }

    public function test_product_detail_blocks_ordering_when_sold_out(): void
    {
        Product::factory()->create([
            'name' => 'Choco Lava Habis',
            'slug' => 'choco-lava-habis',
            'status' => ProductStatus::SoldOut,
        ]);

        $response = $this->get('/produk/choco-lava-habis');

        $response->assertOk();
        $response->assertSee('Habis');
        $response->assertDontSee('name="product_id"', false);
    }

    public function test_product_detail_blocks_ordering_when_stock_is_zero(): void
    {
        Product::factory()->create([
            'name' => 'Donat Stok Nol',
            'slug' => 'donat-stok-nol',
            'stock' => 0,
        ]);

        $response = $this->get('/produk/donat-stok-nol');

        $response->assertOk();
        $response->assertSee('Habis');
        $response->assertDontSee('name="product_id"', false);
    }

    public function test_inactive_product_detail_returns_not_found(): void
    {
        Product::factory()->create([
            'name' => 'Produk Nonaktif',
            'slug' => 'produk-nonaktif',
            'status' => ProductStatus::Inactive,
        ]);

        $response = $this->get('/produk/produk-nonaktif');

        $response->assertNotFound();
    }

    public function test_guest_sees_login_prompt_to_order(): void
    {
        Product::factory()->create(['name' => 'Risol Pelangi', 'slug' => 'risol-pelangi', 'stock' => 10]);

        $response = $this->get('/produk/risol-pelangi');

        $response->assertOk();
        $response->assertSee('Tambah ke Keranjang');
        $response->assertSee(route('auth.login'));
    }
}
