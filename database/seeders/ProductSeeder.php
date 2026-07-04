<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Seed the products table with sample inventory items per category.
     */
    public function run(): void
    {
        $categories = Category::all();

        foreach ($categories as $category) {
            Product::factory()->count(5)->create([
                'category_id' => $category->id,
            ]);
        }
    }
}
