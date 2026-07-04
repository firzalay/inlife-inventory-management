<?php

namespace Database\Factories;

use App\Models\Borrowing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Borrowing>
 */
class BorrowingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $borrowDate = fake()->dateTimeBetween('-1 month', 'now');
        $dueDate = fake()->dateTimeBetween($borrowDate, '+1 month');

        return [
            'borrower_name' => fake()->name(),
            'borrow_date' => $borrowDate,
            'due_date' => $dueDate,
            'return_date' => fake()->optional()->dateTimeBetween($borrowDate, '+1 month'),
            'status' => fake()->randomElement(['borrowed', 'returned', 'overdue']),
        ];
    }
}
