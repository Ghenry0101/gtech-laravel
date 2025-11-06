<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role?->name === 'admin_barang';
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
            'description' => ['required', 'string'],
            'price' => ['required', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'weight' => ['required', 'integer', 'min:0'],
            'height' => ['required', 'integer', 'min:0'],
            'length' => ['required', 'integer', 'min:0'],
            'width' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:3072'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}

