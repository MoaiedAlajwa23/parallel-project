<?php

namespace App\Http\Controllers\Use;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\AddToCartRequest;
use App\Http\Requests\Customer\DepositRequest;
use App\Models\Product;
use App\Services\Customer\Use\InteractService;
use App\Traits\ResourceTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
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
