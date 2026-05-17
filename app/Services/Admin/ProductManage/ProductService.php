<?php

namespace App\Services\Admin\ProductManage;

use App\Models\Product;

class ProductService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
    }

    public function createProduct(array $data)
    {
        $product = Product::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'stock' => $data['stock'],
            'category_id' => $data['category_id'],
            'is_active' => $data['is_active'] ?? true,
        ]);
        return $product;
    }

    public function updateProduct(array $data)
    {
        try {
            $product = Product::findOrFail($data['id']);
            $product->update($data);
            return $product;
        } catch (\Throwable $th) {
            return null;
        }
    }

    public function deleteProduct($request)
    {
        try {
            $product = Product::findOrFail($request->input('id'));
            $product->delete();
            return true;
        } catch (\Throwable $th) {
             return null;
        }
    }
}
