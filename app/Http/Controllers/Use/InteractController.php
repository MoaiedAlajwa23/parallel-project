<?php

namespace App\Http\Controllers\Use;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\AddToCartRequest;
use App\Http\Requests\Customer\DepositRequest;
use App\Jobs\SyncProductViews;
use App\Models\Product;
use App\Services\Customer\Use\InteractService;
use App\Traits\ResourceTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Redis;
class InteractController extends BaseController
{
    use ResourceTrait;
    public function __construct(protected InteractService $interactService)
    {
        $this->middleware(['auth:sanctum', 'customer'])->except('listProducts', 'listCategories');
    }

    public function listProducts(Request $request)
    {
        $products = $this->interactService->listProducts($request);
        if (is_null($products)) {
            return $this->errorResponse('Could not retrieve products', 404);
        }
        return $this->successResponse($products, 'Products retrieved successfully');

    }


    public function showProductById($id)
    {
        $views = Redis::incr("product_views:{$id}");
        
        // ترحيل البيانات للقاعدة كل 50 زيارة لتخفيف عمليات I/O
        if ($views % 50 === 0) {
            SyncProductViews::dispatch($id, $views);
        }

        // 2. Cache-Aside Pattern (قراءة البيانات)
        $ttl = 86400; // Time-To-Live (TTL): 24 hours [cite: 123-125]
        $cacheKey = "product_details:{$id}";

        $product = Cache::remember($cacheKey, $ttl, function () use ($id) {
            // الاستعلام الثقيل يُنفذ فقط عند الـ Cache Miss
            return Product::with('category_id')->findOrFail($id);
        });

        return view('products.show', compact('product'));
    }





    public function listCategories(Request $request)
    {
        $categories = $this->interactService->listCategories($request);
        if (is_null($categories)) {
            return $this->errorResponse('Could not retrieve categories', 404);
        }
        return $this->successResponse($categories, 'Categories retrieved successfully');
    }

    public function addToCart(AddToCartRequest $request)
    {

        try {

            $result = $this->interactService->addToCart($request);
            if (!$result[0]) {
                return $this->errorResponse($result[1], 422);
            }
            return $this->successResponse(null, 'Product added to cart successfully');
        } catch (\Throwable $th) {
            return $this->throwed($th->getMessage(), 400);

        }
    }

    public function deposit(DepositRequest $request)
    {
        try {

            $finished = $this->interactService->deposit($request->validated());
            if (!$finished) {
                return $this->errorResponse("Error", 400);
            }
            return $this->successResponse(null, "Deposit process done successfully!", 200);
        } catch (\Throwable $th) {
            return $this->throwed($th->getMessage(), 400);
        }
    }

    public function checkout(Request $request)
    {
        try {

            $finished = $this->interactService->checkout($request);
            if (!$finished[0]) {
                return $this->errorResponse($finished, 400);
            }
            return $this->successResponse(null, "Checkout process done seccessfully!", 200);
        } catch (\Throwable $th) {
            return $this->throwed($th->getMessage(), 400);
        }
    }
}
