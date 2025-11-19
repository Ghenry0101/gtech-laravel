<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShipmentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role?->posisi === 'admin_pengiriman';
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::in(['processing', 'shipped', 'delivered']),
            ],
            'tracking_id' => ['nullable', 'string', 'max:160'],
            'waybill_id' => ['nullable', 'string', 'max:160'],
        ];
    }
}
