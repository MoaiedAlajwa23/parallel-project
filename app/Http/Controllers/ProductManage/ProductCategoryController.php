<?php

namespace App\Http\Controllers\ProductManage;

use App\DTOs\UpdateProductCategoryDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductManage\ProductCategoryRequest;
use App\Services\Admin\ProductManage\ProductCategoryService;
use App\Traits\ResourceTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
class ProductCategoryController extends BaseController
{
    use ResourceTrait;
    public function __construct(protected ProductCategoryService $categoryService)
    {
        $this->middleware(['auth:sanctum', 'admin']);
    }

    public function createCategory(ProductCategoryRequest $request)
    {
         $categoryData = $this->categoryService->createCategory($request->validated());

        return $this->successResponse($categoryData, 'Category created successfully', 201);
    }
    public function updateCategory(ProductCategoryRequest $request)
    {
        $categoryId = $request->input('id');
        $categoryData = $this->categoryService->updateCategory($categoryId, $request->validated());
        return $this->successResponse($categoryData, 'Category updated successfully');
    }
    public function deleteCategory(Request $request)
    {
        $fin = $this->categoryService->deleteCategory($request->input('id'));
        if ($fin == null) {
            return $this->errorResponse('Category not found or could not be deleted', 404);
        }
        return $this->successResponse(null, 'Category deleted successfully');
    }
}
