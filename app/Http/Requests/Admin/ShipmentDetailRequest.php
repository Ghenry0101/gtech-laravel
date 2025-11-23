<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ShipmentDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role?->posisi === 'admin_pengiriman';
    }

    public function rules(): array
    {
        return [
            'estimation_days' => ['nullable', 'integer', 'min:1', 'max:60'],
        ];
    }

}
