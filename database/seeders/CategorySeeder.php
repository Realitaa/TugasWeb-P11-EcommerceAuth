<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Electronics',
            'Fashion & Apparel',
            'Home & Living',
            'Beauty & Personal Care',
            'Sports & Outdoors',
            'Books & Stationery',
            'Toys & Hobbies',
            'Automotive',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate(['name' => $name]);
        }
    }
}
