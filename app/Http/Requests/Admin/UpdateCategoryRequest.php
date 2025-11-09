<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $admin = $this->user('admin');

        return $admin?->position === 'product_admin';
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $categoryId = (string) $this->route('category');

        return [
            'name' => ['required', 'string', 'max:150', 'unique:categories,name,' . $categoryId],
            'description' => ['nullable', 'string'],
        ];
    }
}
