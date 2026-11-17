<?php

namespace Database\Seeders;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Produk awal sesuai PRD Bagian 2.
     *
     * @var array<string, array{name: string, category: string, price: int, cost: int}>
     */
    private const PRODUCTS = [
        ['name' => 'Risol Pelangi', 'category' => 'Makanan', 'price' => 5000, 'cost' => 3000],
        ['name' => 'Choco Lava', 'category' => 'Dessert', 'price' => 15000, 'cost' => 8000],
        ['name' => 'Donat', 'category' => 'Makanan', 'price' => 7000, 'cost' => 4000],
        ['name' => 'Dimsum', 'category' => 'Makanan', 'price' => 12000, 'cost' => 7000],
        ['name' => 'Corndog', 'category' => 'Makanan', 'price' => 10000, 'cost' => 6000],
        ['name' => 'Es Jeruk', 'category' => 'Minuman', 'price' => 8000, 'cost' => 3500],
        ['name' => 'Aneka Jus Buah', 'category' => 'Minuman', 'price' => 15000, 'cost' => 8000],
    ];

    public function run(): void
    {
        foreach (self::PRODUCTS as $product) {
            $category = Category::query()->where('name', $product['category'])->firstOrFail();

            Product::query()->firstOrCreate(
                ['slug' => str($product['name'])->slug()],
                [
                    'category_id' => $category->id,
                    'name' => $product['name'],
                    'description' => $product['name'].' pilihan segar dari Brew & Bliss.',
                    'price' => $product['price'],
                    'cost_price' => $product['cost'],
                    'stock' => 50,
                    'status' => ProductStatus::Available,
                ],
            );
        }
    }
}
