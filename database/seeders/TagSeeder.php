<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            'Best Seller',
            'New Arrival',
            'Hot Deal',
            'Limited Edition',
            'Eco Friendly',
            'Featured',
            'Trending',
            'Discounted',
            'Premium Quality',
            'Clearance',
        ];

        foreach ($tags as $name) {
            Tag::firstOrCreate(['name' => $name]);
        }
    }
}
