<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Seed the categories table with initial inventory categories.
     */
    public function run(): void
    {
        $categories = [
            'Elektronik',
            'Furniture',
            'ATK',
            'Peralatan Kantor',
            'Peralatan Dapur',
            'Kendaraan',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate(['name' => $name]);
        }
    }
}
