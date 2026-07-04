<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $stockBaik = fake()->numberBetween(0, 80);
        $stockRusak = fake()->numberBetween(0, 15);
        $stockPerluPerbaikan = fake()->numberBetween(0, 10);

        return [
            'code' => strtoupper(fake()->unique()->bothify('PRD-####')),
            'name' => fake()->words(3, true),
            'category_id' => Category::factory(),
            'stock_baik' => $stockBaik,
            'stock_rusak' => $stockRusak,
            'stock_perlu_perbaikan' => $stockPerluPerbaikan,
            'location' => fake()->bothify('Gudang-?? Rak-##'),
            'image' => null,
        ];
    }
}
