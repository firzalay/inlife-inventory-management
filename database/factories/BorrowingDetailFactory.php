<?php

namespace Database\Factories;

use App\Models\Borrowing;
use App\Models\BorrowingDetail;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BorrowingDetail>
 */
class BorrowingDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'borrowing_id' => Borrowing::factory(),
            'product_id' => Product::factory(),
            'quantity' => fake()->numberBetween(1, 10),
            'condition_on_return' => fake()->optional()->randomElement(['Baik', 'Rusak Ringan', 'Rusak Berat']),
        ];
    }
}
