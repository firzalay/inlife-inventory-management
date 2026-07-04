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
        return [
            'code' => strtoupper(fake()->unique()->bothify('PRD-####')),
            'name' => fake()->words(3, true),
            'category_id' => Category::factory(),
            'stock' => fake()->numberBetween(0, 100),
            'location' => fake()->bothify('Gudang-?? Rak-##'),
            'condition' => fake()->randomElement(['good', 'damaged', 'lost']),
            'image' => null,
        ];
    }
}
