<?php

namespace Database\Factories;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => str($name)->slug()->append('-'.fake()->unique()->numberBetween(1, 999999)),
            'description' => fake()->sentence(),
            'price' => fake()->numberBetween(10000, 50000),
            'cost_price' => fake()->numberBetween(5000, 20000),
            'stock' => fake()->numberBetween(10, 100),
            'status' => ProductStatus::Available,
        ];
    }
}
