<?php

namespace App\Http\Requests\Admin\ProductManage;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProductCategoryRequest extends FormRequest
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
            'id' => 'sometimes|required|integer|exists:product_categories,id',
            'name' => 'required|string|max:50|unique:product_categories,name',
            'description' => 'nullable|max:500',
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
            'name.required' => 'Category name is required.',
            'name.string' => 'Category name must be a string.',
            'name.max' => 'Category name cannot exceed 50 characters.',
            'name.unique' => 'Category name must be unique.',
            'description.max' => 'Category description cannot exceed 500 characters.',
            'id.required' => 'Category ID is required.',
            'id.integer' => 'Category ID must be an integer.',
            'id.exists' => 'Category ID must exist in the categories table.',
        ];
    }
}
