<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_shows_store_identity_and_products(): void
    {
        $product = Product::factory()->create(['name' => 'Risol Pelangi', 'slug' => 'risol-pelangi']);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Brew & Bliss');
        $response->assertSee('Risol Pelangi');
        $response->assertSee('Lihat semua');
        $response->assertSee(route('products.show', $product));
    }

    public function test_catalog_lists_available_products(): void
    {
        Product::factory()->create(['name' => 'Donat Gula', 'slug' => 'donat-gula']);

        $response = $this->get('/produk');

        $response->assertOk();
        $response->assertSee('Donat Gula');
    }

    public function test_catalog_hides_inactive_products(): void
    {
        Product::factory()->create([
            'name' => 'Produk Nonaktif',
            'slug' => 'produk-nonaktif',
            'status' => ProductStatus::Inactive,
        ]);

        $response = $this->get('/produk');

        $response->assertOk();
        $response->assertDontSee('Produk Nonaktif');
    }

    public function test_catalog_shows_sold_out_products_without_order_option(): void
    {
        Product::factory()->create([
            'name' => 'Choco Lava Habis',
            'slug' => 'choco-lava-habis',
            'status' => ProductStatus::SoldOut,
        ]);

        $response = $this->get('/produk');

        $response->assertOk();
        $response->assertSee('Choco Lava Habis');
        $response->assertSee('Habis');
    }

    public function test_catalog_search_filters_by_name(): void
    {
        Product::factory()->create(['name' => 'Kopi Susu Gula Aren', 'slug' => 'kopi-susu-gula-aren']);
        Product::factory()->create(['name' => 'Dimsum Ayam', 'slug' => 'dimsum-ayam']);

        $response = $this->get('/produk?q=Kopi');

        $response->assertOk();
        $response->assertSee('Kopi Susu Gula Aren');
        $response->assertDontSee('Dimsum Ayam');
    }

    public function test_catalog_filters_by_category(): void
    {
        $makanan = Category::factory()->create(['name' => 'Makanan', 'slug' => 'makanan']);
        $minuman = Category::factory()->create(['name' => 'Minuman', 'slug' => 'minuman']);

        Product::factory()->create(['name' => 'Corndog Saus', 'slug' => 'corndog-saus', 'category_id' => $makanan->id]);
        Product::factory()->create(['name' => 'Es Jeruk Peras', 'slug' => 'es-jeruk-peras', 'category_id' => $minuman->id]);

        $response = $this->get('/produk?category=makanan');

        $response->assertOk();
        $response->assertSee('Corndog Saus');
        $response->assertDontSee('Es Jeruk Peras');
    }

    public function test_catalog_filters_by_availability(): void
    {
        Product::factory()->create(['name' => 'Risol tersedia', 'slug' => 'risol-tersedia', 'stock' => 20]);
        Product::factory()->create(['name' => 'Donat habis', 'slug' => 'donat-habis', 'stock' => 0]);

        $available = $this->get('/produk?availability=tersedia');
        $soldOut = $this->get('/produk?availability=habis');

        $available->assertOk();
        $available->assertSee('Risol tersedia');
        $available->assertDontSee('Donat habis');

        $soldOut->assertOk();
        $soldOut->assertSee('Donat habis');
        $soldOut->assertDontSee('Risol tersedia');
    }
}
