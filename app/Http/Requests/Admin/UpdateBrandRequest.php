<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role?->posisi === 'admin_barang';
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('brands', 'name')->ignore(optional($this->route('brand'))->getKey(), 'id'),
            ],
            'logo' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
