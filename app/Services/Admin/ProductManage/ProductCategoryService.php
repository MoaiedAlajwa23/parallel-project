<?php

namespace App\Services\Admin\ProductManage;

use App\Models\ProductCategory;

class ProductCategoryService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        
    }
    public function createCategory(array $data)
    {
        $category = ProductCategory::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
        return $category;
    }
    public function updateCategory(int $categoryId, array $data)
    {
        try {
            $category = ProductCategory::findOrFail($categoryId);
            $category->update([
                'name' => strtolower($data['name']),
                'description' => $data['description'] ?? null,
                ]);
                return $category;
        } catch (\Throwable $th) {
            return null;
        }
    }
    public function deleteCategory(int $categoryId)
    {
        try {
            $category = ProductCategory::findOrFail($categoryId);
            $category->delete();
            return true;
        } catch (\Throwable $th) {
            return null;
        }
    }
}
