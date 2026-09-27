<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
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
            'category_id' => Category::factory(),
            'user_id' => User::factory(),
            'name' => fake()->sentence($this->generateNumberBetween(2, 5)),
            'sku' => fake()->unique()->regexify('[A-Z]{3}-[0-9]{4}-[A-Z]{2}'),
            // Harga dalam Rupiah (kelipatan 1.000 atau 5.000 antara Rp 10.000 s/d Rp 5.000.000)
            'price' => (float) (fake()->numberBetween(10000, 5000000)),
            'stock' => fake()->numberBetween(0, 1000),
            'description' => fake()->paragraphs($this->generateNumberBetween(1, 3), true),
            'discount_percentage' => fake()->randomElement(range(0, 95, 5)),
            'rating' => fake()->randomFloat(2, 1, 5),
            'thumbnail' => "https://picsum.photos/640/480?random={$this->generateNumberBetween(1, 1000)}",
        ];
    }

    private function generateNumberBetween(int $min = 0, int $max = 1000): int
    {
        return fake()->numberBetween($min, $max);
    }
}
