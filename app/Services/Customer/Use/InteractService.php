<?php

namespace App\Services\Customer\Use;

use App\Jobs\GenerateInvoiceJob;
use App\Jobs\PaymentSimulateJob;
use App\Jobs\SendOrderNotificationJob;
use App\Jobs\SyncProductViews;
use App\Models\Balance;
use App\Models\Cart;
use App\Models\CartProduct;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
class InteractService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
    }


    public function listProducts($request)
    {
        try {

            $perPage = (int) $request->query('per_page', 3);
            $perPage = $perPage > 0 ? min($perPage, 50) : 15;

            $page = (int) $request->query('page', 1);

            $cacheKey = "products_list_page_{$page}_per_{$perPage}";
            $ttl = 86400;

            $products = Cache::tags(['products_list'])->remember($cacheKey, $ttl, function () use ($perPage) {

                return Product::query()
                    ->latest()
                    ->paginate($perPage);
            });

            return $products;

        } catch (\Throwable $th) {
            return null;
        }
    }

    public function listCategories($request)
    {
        try {
            $categories = ProductCategory::query()
                ->select('name')
                ->distinct()
                ->pluck('name');
            return $categories;
        } catch (\Throwable $th) {
            return null;
        }
    }


        public function showProductById($id)
    {
        $views = Redis::incr("product_views:{$id}");
        if ($views % 50 === 0) {
            SyncProductViews::dispatch($id, $views);
        }

        $ttl = 86400;
        $cacheKey = "product_details:{$id}";

        $product = Cache::remember($cacheKey, $ttl, function () use ($id) {
            return Product::with('category')->findOrFail($id);
        });

        return $product;
    }


    public function addToCart($request)
    {
        $userId = auth()->id();

        return Cache::lock('cart-user-' . $userId, 5)
            ->block(2, function () use ($request, $userId) {

                return DB::transaction(function () use ($request, $userId) {

                    $productId = $request->product_id;
                    $quantity = $request->quantity ?? 1;

                    $product = Product::find($productId);

                    if (!$product || !$product->is_active) {
                        throw new \Exception('Product not found');
                    }
                    if ($request->quantity >= $product->getStock()) {
                        throw new \Exception("Quantity out of bount repository");
                    }

                    $cart = Cart::firstOrCreate([
                        'user_id' => $userId,
                        'status' => 'active'
                    ]);
                    $existing = DB::table('cart_products')
                        ->where('cart_id', $cart->id)
                        ->where('product_id', $productId)
                        ->first();

                    if ($existing) {
                        DB::table('cart_products')
                            ->where('cart_id', $cart->id)
                            ->where('product_id', $productId)
                            ->increment('quantity', $quantity);

                    } else {

                        $cart->products()->attach($productId, [
                            'quantity' => $quantity
                        ]);
                    }

                    return [true];
                });
            });
    }


    public function deposit(array $deposit)
    {
        $userId = auth()->id();

        return DB::transaction(function () use ($deposit, $userId) {

            $balance = Balance::where('user_id', $userId)
                ->lockForUpdate()
                ->first();
            if (!$balance) {
                $balance = Balance::create([
                    'user_id' => $userId,
                    'amount' => 0
                ]);
            }
            $balance->increment('amount', $deposit['amount']);
            return true;
        });
    }


    public function checkout($request)
    {
        $userId = auth()->id();

        try {
            return Cache::lock('checkout-user-' . $userId, 10)
                ->block(5, function () use ($request, $userId) {

                    return DB::transaction(function () use ($request, $userId) {

                        $user = User::where('id', $userId)
                            ->with([
                                'balance' => function ($query) {
                                    $query->lockForUpdate();
                                }
                            ])
                            ->first();

                        $cart = Cart::where('user_id', $userId)
                            ->where('status', 'active')
                            ->with('products')
                            ->first();

                        if (!$cart || $cart->products->isEmpty()) {
                            throw new \Exception('Cart is empty');
                        }

                        $totalPrice = 0;
                        $orderProductsData = [];

                        foreach ($cart->products as $product) {

                            $requestedQty = $product->pivot->quantity ?? 1;

                            $currentProduct = Product::find($product->id);

                            if ($currentProduct->stock < $requestedQty) {
                                throw new \Exception("The requested quantity for product {$currentProduct->name} is not available.");
                            }

                            $totalPrice += ($currentProduct->price * $requestedQty);

                            $updated = DB::table('products')
                                ->where('id', $currentProduct->id)
                                ->where('version', $currentProduct->version)
                                ->update([
                                    'stock' => $currentProduct->stock - $requestedQty,
                                    'version' => $currentProduct->version + 1
                                ]);

                            if ($updated === 0) {
                                throw new \Exception("Conflict: The product {$currentProduct->name} has been updated by another user. Please try again.");
                            }

                            $orderProductsData[$currentProduct->id] = [
                                'price_at_purchase' => $currentProduct->price,
                                'quantity' => $requestedQty
                            ];
                        }
                        $balance = $user->balance;

                        if ($balance->amount < $totalPrice) {
                            throw new \Exception('Insufficient balance');
                        }

                        $balance->decrement('amount', $totalPrice);

                        $order = Order::create([
                            'user_id' => $user->id,
                            'total_price' => $totalPrice,
                            'status' => 'pending',
                            'discount' => method_exists($balance, 'getDiscount') ? $balance->getDiscount() : 0,
                            'shipping_address' => $request->shipping_address,
                            'notes' => $request->notes,
                        ]);
                        $order->products()->attach($orderProductsData);

                        $cart->products()->detach();

                        $cart->update([
                            'status' => 'completed'
                        ]);

                        DB::afterCommit(function () use ($order) {
                            PaymentSimulateJob::dispatch($order);
                            GenerateInvoiceJob::dispatch($order);
                            SendOrderNotificationJob::dispatch($order);
                        });

                        return [true, $order->id];
                    });
                });

        } catch (\Throwable $th) {
            return [false, $th->getMessage()];
        }
    }


}
