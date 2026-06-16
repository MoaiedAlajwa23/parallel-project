<?php

namespace App\Services\Customer\Use;

use App\Jobs\GenerateInvoiceJob;
use App\Jobs\PaymentSimulateJob;
use App\Jobs\SendOrderNotificationJob;
use App\Models\Balance;
use App\Models\Cart;
use App\Models\CartProduct;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
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
            $perPage = $perPage > 0
                ? min($perPage, 50)
                : 15;
            $products = Product::query()
                ->latest()
                ->paginate($perPage);
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

        return Cache::lock('checkout-user-'.$userId, 10)
            ->block(5, function () use ($request, $userId) {

                return DB::transaction(function () use ($request, $userId) {

                    $user = User::where('id', $userId)
                        ->lockForUpdate()
                        ->with('balance')
                        ->first();

                    $cart = Cart::where('user_id', $userId)
                        ->where('status', 'active')
                        ->with('products')
                        ->lockForUpdate()
                        ->first();

                    if (!$cart || $cart->products->isEmpty()) {
                        throw new \Exception('Cart is empty');
                    }

                    $totalPrice = 0;

                    foreach ($cart->products as $product) {

                        $lockedProduct = Product::where('id', $product->id)
                            ->lockForUpdate()
                            ->first();

                        if ($lockedProduct->stock < 1) {

                            throw new \Exception(
                                "Product {$lockedProduct->name} out of stock"
                            );
                        }

                        $totalPrice += $lockedProduct->price;

                        $lockedProduct->decrement('stock');
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
                        'discount' => $balance->getDiscount(),
                        'shipping_address' => $request->shipping_address,
                        'notes' => $request->notes,
                    ]);

                    foreach ($cart->products as $product) {

                        $order->products()->attach($product->id, [
                            'price_at_purchase' => $product->price,
                            'quantity' => 1
                        ]);
                    }
                    
                    $cart->products()->detach();

                        $cart->update([
                            'status'=>'completed'
                        ]);

                    DB::afterCommit(function () use ($order) {
                        PaymentSimulateJob::dispatch($order);
                        GenerateInvoiceJob::dispatch($order);
                        SendOrderNotificationJob::dispatch($order);
                    });

                    return [
                        true,
                        $order->id
                    ];
                });
            });

    } catch (\Throwable $th) {

        return [false, $th->getMessage()];
    }
}

// ==================  optimistic locking  ==================


public function checkout1($request)
{
        $var = "kkk";
    $userId = auth()->id();

    try {
        // حماية على مستوى التطبيق لمنع المستخدم نفسه من النقر المزدوج (ممتاز جداً)
        return Cache::lock('checkout-user-'.$userId, 10)
            ->block(5, function () use ($request, $userId) {

                return DB::transaction(function () use ($request, $userId) {

                    // 1. قراءة بيانات المستخدم بدون قفل تشاؤمي
                    $user = User::where('id', $userId)
                        ->with('balance')
                        ->first();

                    // 2. قراءة السلة بدون قفل تشاؤمي
                    $cart = Cart::where('user_id', $userId)
                        ->where('status', 'active')
                        ->with('products')
                        ->first();

                    if (!$cart || $cart->products->isEmpty()) {
                        throw new \Exception('Cart is empty');
                    }

                    $totalPrice = 0;

                    // 3. معالجة المنتجات (هنا يكمن سحر القفل المتفائل)
                    foreach ($cart->products as $product) {

                        // نقرأ حالة المنتج الحالية ورقم إصداره بدون أي أقفال
                        $currentProduct = Product::find($product->id);

                        if ($currentProduct->stock < 1) {
                            throw new \Exception("Product {$currentProduct->name} out of stock");
                        }

                        $totalPrice += $currentProduct->price;

                        // تحديث المخزون برمجياً باستخدام شرط رقم الإصدار
                        $updated = DB::table('products')
                            ->where('id', $currentProduct->id)
                            ->where('version', $currentProduct->version) // شرط القفل المتفائل
                            ->update([
                                'stock' => $currentProduct->stock - 1, // خصم الكمية
                                'version' => $currentProduct->version + 1 // زيادة رقم الإصدار
                            ]);

                        // التحقق من التضارب (Race Condition)
                        if ($updated === 0) {
                            throw new \Exception("تضارب: المنتج {$currentProduct->name} تم شراؤه من قبل مستخدم آخر في هذه اللحظة. يرجى المحاولة مرة أخرى.");
                        }
                    }

                    // 4. التحقق من الرصيد وخصمه
                    $balance = $user->balance;

                    if ($balance->amount < $totalPrice) {
                        throw new \Exception('Insufficient balance');
                    }

                    $balance->decrement('amount', $totalPrice);

                    // 5. إنشاء الطلب
                    $order = Order::create([
                        'user_id' => $user->id,
                        'total_price' => $totalPrice,
                        'status' => 'pending',
                        'discount' => $balance->getDiscount(),
                        'shipping_address' => $request->shipping_address,
                        'notes' => $request->notes,
                    ]);

                    // ربط المنتجات بالطلب
                    foreach ($cart->products as $product) {
                        $order->products()->attach($product->id, [
                            'price_at_purchase' => $product->price,
                            'quantity' => 1
                        ]);
                    }
                    
                    // إفراغ السلة
                    $cart->products()->detach();

                    $cart->update([
                        'status' => 'completed'
                    ]);

                    // 6. ترحيل المهام غير المتزامنة بعد نجاح المعاملة
                    DB::afterCommit(function () use ($order) {
                        PaymentSimulateJob::dispatch($order);
                        GenerateInvoiceJob::dispatch($order);
                        SendOrderNotificationJob::dispatch($order);
                    });

                    return [
                        true,
                        $order->id
                    ];
                });
            });

    } catch (\Throwable $th) {
        return [false, $th->getMessage()];
    }
}




}
