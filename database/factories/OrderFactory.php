<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => fake()->randomElement(OrderStatus::cases()),
            'total_amount' => 0,
            'shipping_fee' => fake()->randomElement(range(10000, 30000, 5000)),
            'tax_amount' => 0,
            'shipping_address' => fake()->address(),
        ];
    }
}
