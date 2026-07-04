<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Seed the products table with sample inventory items per category.
     * Uses a variety of stock combinations across all 3 condition columns.
     */
    public function run(): void
    {
        $categories = Category::all();

        foreach ($categories as $category) {
            // All-good condition products (ready to borrow)
            Product::factory()->count(2)->create([
                'category_id' => $category->id,
                'stock_baik' => fake()->numberBetween(10, 30),
                'stock_rusak' => 0,
                'stock_perlu_perbaikan' => 0,
            ]);

            // Mixed condition products
            Product::factory()->count(2)->create([
                'category_id' => $category->id,
                'stock_baik' => fake()->numberBetween(3, 15),
                'stock_rusak' => fake()->numberBetween(1, 5),
                'stock_perlu_perbaikan' => fake()->numberBetween(0, 3),
            ]);

            // Low/near-threshold stock products (to trigger notifications)
            Product::factory()->count(1)->create([
                'category_id' => $category->id,
                'stock_baik' => fake()->numberBetween(0, 5),
                'stock_rusak' => fake()->numberBetween(0, 3),
                'stock_perlu_perbaikan' => fake()->numberBetween(0, 2),
            ]);
        }
    }
}
