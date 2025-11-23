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
                Rule::in(['processing', 'courier_allocated', 'picking_up', 'picked', 'on_the_way', 'delivering', 'delivered', 'shipped']),
            ],
        ];
    }
}
