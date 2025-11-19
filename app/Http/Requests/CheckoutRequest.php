<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'address_id' => ['required', 'exists:addresses,id'],
            'shipping_courier_code' => ['required', 'string', 'max:50'],
            'shipping_service_code' => ['required', 'string', 'max:50'],
            'payment_method' => ['required', 'string', 'in:bank_transfer,qris,ewallet'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
