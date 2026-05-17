<?php

namespace App\Http\Controllers\ProductManage;
use App\Services\Admin\ProductManage\ProductService;
use Illuminate\Routing\Controller as BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductManage\ProductRequest;
use Illuminate\Http\Request;
use App\Traits\ResourceTrait;
class ProductController extends BaseController
{
    use ResourceTrait;
    public function __construct(protected ProductService $productService)
    {
        $this->middleware(['auth:sanctum', 'admin']);
    }
    public function createProduct(ProductRequest $request)
    {
        $productData = $this->productService->createProduct($request->validated());
        return $this->successResponse($productData, 'Product created successfully', 201);
    }

    public function updateProduct(ProductRequest $request)
    {
        $productData = $this->productService->updateProduct($request->validated());
            if ($productData == null) {
                return $this->errorResponse('Product not found or could not be updated', 404);
            }
        return $this->successResponse($productData, 'Product updated successfully');
    }

    public function deleteProduct(Request $request)
    {
        $fin = $this->productService->deleteProduct($request);
        if ($fin == null) {
            return $this->errorResponse('Product not found or could not be deleted', 404);
        }
        return $this->successResponse(null, 'Product deleted successfully');
    }
}
