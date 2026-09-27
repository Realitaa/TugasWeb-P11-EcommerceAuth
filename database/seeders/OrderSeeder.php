<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::all();

        if ($products->isEmpty()) {
            $this->call(ProductSeeder::class);
            $products = Product::all();
        }

        // Get customers (role: user)
        $customers = User::where('role', UserRole::User)->get();

        if ($customers->isEmpty()) {
            $customers = User::factory(5)->user()->create();
        }

        // Create 20-30 realistic orders across customers
        $orderCount = rand(20, 30);

        for ($i = 0; $i < $orderCount; $i++) {
            $customer = $customers->random();
            $shippingFee = fake()->randomElement([10000, 15000, 20000, 25000, 30000]);
            $taxAmount = 0;

            $order = Order::create([
                'user_id' => $customer->id,
                'status' => fake()->randomElement(OrderStatus::cases()),
                'total_amount' => 0,
                'shipping_fee' => $shippingFee,
                'tax_amount' => $taxAmount,
                'shipping_address' => fake()->address(),
            ]);

            // Pick 1 to 4 random products for this order
            $orderProducts = $products->random(fake()->numberBetween(1, 4));
            $itemsSubtotal = 0;

            foreach ($orderProducts as $product) {
                $quantity = fake()->numberBetween(1, 3);
                // Use product final_price or price as ordered item unit price
                $unitPrice = (int) $product->final_price;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $unitPrice,
                ]);

                $itemsSubtotal += ($unitPrice * $quantity);
            }

            // Calculate & update total amount = items subtotal + shipping fee + tax
            $order->update([
                'total_amount' => $itemsSubtotal + $shippingFee + $taxAmount,
            ]);
        }
    }
}
