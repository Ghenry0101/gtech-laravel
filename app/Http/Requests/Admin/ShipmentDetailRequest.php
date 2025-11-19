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
            'courier_name' => ['nullable', 'string', 'max:120'],
            'courier_service' => ['nullable', 'string', 'max:120'],
            'tracking_id' => ['nullable', 'string', 'max:160'],
            'waybill_id' => ['nullable', 'string', 'max:160'],
            'estimation_days' => ['nullable', 'integer', 'min:1', 'max:60'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
        ];
    }

}
