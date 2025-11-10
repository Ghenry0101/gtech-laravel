<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role?->posisi === 'admin_barang';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['nullable', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'weight' => ['required', 'integer', 'min:0'],
            'height' => ['required', 'integer', 'min:0'],
            'length' => ['required', 'integer', 'min:0'],
            'width' => ['required', 'integer', 'min:0'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_price' => ['nullable', 'numeric', 'min:0'],
            'discount_start' => ['nullable', 'date'],
            'discount_end' => ['nullable', 'date', 'after_or_equal:discount_start'],
            'discount_active' => ['nullable', 'boolean'],
            'product_image' => ['nullable', 'image', 'max:3072'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
