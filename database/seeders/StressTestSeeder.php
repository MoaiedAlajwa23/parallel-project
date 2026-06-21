<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Order;
use App\Models\Cart;
use App\Models\Balance;
use App\Models\Role; 
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
class StressTestSeeder extends Seeder
{
    use WithoutModelEvents;
    public function run(): void
    {
        $this->command->info('Starting Heavy Seeding with Roles... Please wait.');

        $roles = [
            Role::firstOrCreate(['slug' => 'customer'], ['description' => 'Customer Role']),
            Role::firstOrCreate(['slug' => 'admin'], ['description' => 'Admin Role']),
        ];
        
        $roleIds = collect($roles)->pluck('id')->toArray();


        $category = ProductCategory::firstOrCreate(
            ['name' => 'Stress Test Category'],
            ['description' => 'Category for testing']
        );


        $users = User::factory(200)->create([
            'password' => bcrypt('password123')
        ]);


        $products = Product::factory(50)->create([
            'category_id' => $category->id,
            'stock' => 10000,
            'price' => rand(10, 500),
            'version' => 1
        ]);

        DB::transaction(function () use ($users, $products, $roleIds) {
            foreach ($users as $user) {
                

                $randomRoleId = $roleIds[array_rand($roleIds)];
                
                DB::table('user_roles')->insert([
                    'user_id' => $user->id,
                    'role_id' => 2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);


                Balance::create([
                    'user_id' => $user->id,
                    'amount' => 1000000.00, 
                ]);


                $cart = Cart::create([
                    'user_id' => $user->id,
                    'status' => 'active'
                ]);
                
                // foreach ($products->random(3) as $cartProduct) {
                //     $cart->products()->attach($cartProduct->id, ['quantity' => 2]);
                // }


                // for ($i = 0; $i < 20; $i++) { 
                //     $order = Order::create([
                //         'user_id' => $user->id,
                //         'total_price' => rand(100, 1000),
                //         'status' => 'completed',
                //         'shipping_address' => 'Damascus',
                //         'notes' => 'Batch test',
                //         'discount' => 0,
                //         'created_at' => now()->subDays(rand(1, 30)), 
                //     ]);

                //     $randomProducts = $products->random(rand(1, 3));

                //     foreach ($randomProducts as $product) {
                //         $order->products()->attach($product->id, [
                //             'quantity' => rand(1, 3),
                //             'price_at_purchase' => $product->price,
                //         ]);
                //     }
                // }
            }
        });

        $this->command->info('Database seeded perfectly with Random Roles! 🚀');
    }
}