<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * @var array<int, string>
     */
    private const CATEGORIES = ['Makanan', 'Dessert', 'Minuman'];

    public function run(): void
    {
        foreach (self::CATEGORIES as $index => $name) {
            Category::query()->firstOrCreate(
                ['slug' => str($name)->slug()],
                ['name' => $name, 'sort_order' => $index],
            );
        }
    }
}
