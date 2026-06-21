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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
class InteractController extends BaseController
{
    use ResourceTrait;
    public function __construct(protected InteractService $interactService)
    {
        $this->middleware(['auth:sanctum', 'customer'])->except('listProducts', 'listCategories', 'bestSellers', 'showProductById', 'bestSellersSlow');
    }

    public function listProducts(Request $request)
    {
        $products = $this->interactService->listProducts($request);
        if (is_null($products)) {
            return $this->errorResponse('Could not retrieve products', 404);
        }
        return $this->successResponse($products, 'Products retrieved successfully');

    }

    public function showProductById(Request $request)
    {
        $id = $request->query('id');
        $product = $this->interactService->showProductById($id);
        if (is_null($product)) {
            return $this->errorResponse('Product not found', 404);
        }
        return $this->successResponse($product, 'Product retrieved successfully');
    }


    public function bestSellers()
    {
        try {
            $bestSellers = Cache::tags(['products_list'])->get('store:bestsellers', []);
            return $this->successResponse($bestSellers, 'The best-selling products have been successfully brought in');

        } catch (\Throwable $th) {
            return $this->errorResponse('An internal error occurred', 500);
        }
    }



    public function bestSellersSlow()
    {
        try {
            $bestSellers = $this->interactService->bestSellersSlow();
            return $this->successResponse($bestSellers, 'The best-selling products have been successfully brought in');

        } catch (\Throwable $th) {
            return $this->errorResponse('An internal error occurred', 500);
        }
    }


    public function listCategories(Request $request)
    {
        $categories = $this->interactService->listCategories($request);
        if (is_null($categories)) {
            return $this->errorResponse('Could not retrieve categories', 404);
        }
        return $this->successResponse($categories, 'Categories retrieved successfully');
    }

    public function addToCart(Request $request)
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
