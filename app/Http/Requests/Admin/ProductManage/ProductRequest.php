<?php

namespace App\Http\Requests\Admin\ProductManage;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => 'sometimes|required|integer|exists:products,id',
            'name' => 'required|string|max:50',
            'description' => 'nullable|max:500',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|integer|exists:product_categories,id',
            'is_active' => 'boolean',
        ];
    }
    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $response = response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'errors' => $validator->errors()
        ], 422);
        
        throw new \Illuminate\Validation\ValidationException($validator, $response);
        }
        public function messages(): array
        {
            return [
                'name.required' => 'Product name is required.',
                'name.string' => 'Product name must be a string.',
                'name.max' => 'Product name cannot exceed 50 characters.',
                'description.max' => 'Product description cannot exceed 500 characters.',
                'price.required' => 'Product price is required.',
                'price.numeric' => 'Product price must be a number.',
                'price.min' => 'Product price must be at least 0.',
                'stock.required' => 'Product stock is required.',
                'stock.integer' => 'Product stock must be an integer.',
                'stock.min' => 'Product stock must be at least 0.',
                'category_id.required' => 'Category ID is required.',
                'category_id.integer' => 'Category ID must be an integer.',
                'category_id.exists' => 'Category ID must exist in the categories table.',
                'is_active.boolean' => 'Is active must be a boolean value.',
                'id.required' => 'Product ID is required.',
                'id.integer' => 'Product ID must be an integer.',
                'id.exists' => 'Product ID must exist in the products table.',
            ];
        }

}
