<?php

namespace Database\Seeders;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\Cart;

class StressTestSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::factory(100)->create();

        $products = Product::factory(50)->create([
            'stock' => 1000,
            'price' => rand(10, 500),
        ]);

        foreach ($users as $user) {

            for ($i = 0; $i < 50; $i++) {

                $order = Order::create([
                    'user_id' => $user->id,
                    'total_price' => rand(100, 1000),
                    'status' => 'completed',
                    'shipping_address' => 'Damascus',
                    'notes' => 'Batch test',
                    'discount' => 0,
                    'created_at' => now(),
                ]);

                $randomProducts = $products->random(rand(1, 5));

                foreach ($randomProducts as $product) {

                    $order->products()->attach($product->id, [
                        'quantity' => rand(1, 3),
                        'price_at_purchase' => $product->price,
                    ]);
                }
            }
        }
    }
}
