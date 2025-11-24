<?php

namespace App\Services;

use App\Exceptions\CheckoutException;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\User;
use App\Services\Biteship\BiteshipService;
use App\Services\Midtrans\MidtransService;
use App\Services\Shipping\ShipmentDispatcher;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CheckoutService
{
    public function getCartItems(string $userId): Collection
    {
        return Cart::query()
            ->with('product.category')
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->latest()
            ->get();
    }

    public function prepareCheckoutViewData(User $user, ?Collection $cartItems = null): array
    {
        $cartItems ??= $this->getCartItems($user->id);

        $addresses = $user->addresses()->orderByDesc('is_default')->get();
        $defaultAddress = $addresses->firstWhere('is_default', true) ?? $addresses->first();

        $shippingOptions = [];
        if ($defaultAddress && $cartItems->isNotEmpty()) {
            try {
                $shippingOptions = BiteshipService::make()->getRates($defaultAddress, $cartItems);
            } catch (\Throwable $throwable) {
                Log::warning('Unable to load Biteship rates for checkout page.', [
                    'user_id' => $user->id,
                    'message' => $throwable->getMessage(),
                ]);
            }
        }

        return [
            'cartItems' => $cartItems,
            'summary' => $this->calculateSummary($cartItems),
            'addresses' => $addresses,
            'defaultAddress' => $defaultAddress,
            'shippingOptions' => $shippingOptions,
            'paymentMethods' => config('midtrans.payment_methods', []),
        ];
    }

    public function fetchShippingRates(User $user, int $addressId): array
    {
        $address = $user->addresses()->whereKey($addressId)->firstOrFail();
        $cartItems = $this->getCartItems($user->id);

        if ($cartItems->isEmpty()) {
            throw new CheckoutException(__('Keranjang kosong.'), 422);
        }

        try {
            return BiteshipService::make()->getRates($address, $cartItems);
        } catch (\Throwable $throwable) {
            Log::error('Failed to retrieve Biteship rates.', [
                'address_id' => $address->id,
                'message' => $throwable->getMessage(),
            ]);

            throw new CheckoutException(__('Gagal mengambil ongkos kirim dari Biteship. Pastikan alamat valid.'), 422);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{order: Order, payment: Payment, shipment: Shipment}
     *
     * @throws CheckoutException
     */
    public function processCheckout(User $user, array $data): array
    {
        $cartItems = $this->getCartItems($user->id);

        if ($cartItems->isEmpty()) {
            throw new CheckoutException(__('Keranjang Anda kosong.'), 422);
        }

        $address = $user->addresses()->whereKey($data['address_id'])->firstOrFail();

        $midtrans = MidtransService::make();
        $paymentMethodKey = $data['payment_method'];
        $paymentMethod = $midtrans->getPaymentMethod($paymentMethodKey);
        $paymentBank = $data['payment_bank'] ?? null;

        if (! $paymentMethod) {
            throw new CheckoutException(__('Metode pembayaran tidak valid.'), 422);
        }

        $rates = $this->refreshShippingRates($user, $address, $cartItems);

        $selectedRate = collect($rates)->first(function (array $rate) use ($data) {
            return $rate['courier_code'] === $data['shipping_courier_code']
                && $rate['courier_service_code'] === $data['shipping_service_code'];
        });

        if (! $selectedRate) {
            throw new CheckoutException(__('Pilihan ekspedisi tidak ditemukan.'), 422);
        }

        try {
            [$order, $payment, $shipment] = DB::transaction(function () use (
                $user,
                $address,
                $cartItems,
                $selectedRate,
                $data,
                $midtrans,
                $paymentMethodKey,
                $paymentBank
            ) {
                $itemsTotal = $cartItems->sum('subtotal');
                $shippingCost = $selectedRate['cost'];
                $grandTotal = $itemsTotal + $shippingCost;

                $order = Order::create([
                    'user_id' => $user->id,
                    'recipient_name' => $address->recipient_name,
                    'phone' => $address->phone,
                    'full_address' => $this->formatFullAddress($address),
                    'subtotal_amount' => $itemsTotal,
                    'shipping_cost' => $shippingCost,
                    'total_amount' => $grandTotal,
                    'order_status' => 'pending',
                    'notes' => $data['notes'] ?? null,
                    'payment_method' => $paymentMethodKey,
                    'order_time' => now(),
                ]);

                foreach ($cartItems as $cartItem) {
                    if ($cartItem->product && $cartItem->product->stock < $cartItem->quantity) {
                        throw new \RuntimeException(__('Stok :name tidak mencukupi.', ['name' => $cartItem->product->name]));
                    }

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $cartItem->product?->id,
                        'product_name' => $cartItem->product?->name ?? __('Produk'),
                        'quantity' => $cartItem->quantity,
                        'price' => $cartItem->price,
                    ]);

                    if ($cartItem->product) {
                        $updatedRows = $cartItem->product
                            ->newQuery()
                            ->whereKey($cartItem->product->getKey())
                            ->where('stock', '>=', $cartItem->quantity)
                            ->decrement('stock', $cartItem->quantity);

                        if (! $updatedRows) {
                            throw new \RuntimeException(__('Stok :name tidak mencukupi.', ['name' => $cartItem->product->name]));
                        }
                    }
                }

                $shipment = Shipment::create([
                    'order_id' => $order->id,
                    'courier_code' => $selectedRate['courier_code'],
                    'courier_service_code' => $selectedRate['courier_service_code'],
                    'courier_name' => $selectedRate['courier_company'],
                    'courier_service' => $selectedRate['courier_service_name'],
                    'shipping_cost' => $shippingCost,
                    'estimation_days' => $selectedRate['duration_max'] ?? $selectedRate['duration_min'] ?? null,
                    'status' => 'processing',
                    'rate_payload' => $this->buildShipmentMetadata($selectedRate, $address),
                ]);

                $payment = Payment::create([
                    'order_id' => $order->id,
                    'payment_type' => $paymentMethodKey,
                    'gross_amount' => $grandTotal,
                    'payment_status' => 'pending',
                ]);

                Cart::query()
                    ->whereIn('id', $cartItems->pluck('id'))
                    ->update(['status' => 'checked_out']);

                $itemDetails = $this->buildItemDetails($order);
                $customerDetails = $this->buildCustomerDetails($user->name, $user->email, $address);

                $midtrans->createCoreCharge(
                    order: $order->fresh('items'),
                    payment: $payment,
                    itemDetails: $itemDetails,
                    customerDetails: $customerDetails,
                    paymentMethodKey: $paymentMethodKey,
                    bank: $paymentBank,
                );

                return [$order, $payment->fresh(), $shipment];
            });
        } catch (\Throwable $throwable) {
            $context = [
                'user_id' => $user->id,
                'message' => $throwable->getMessage(),
            ];
            $userMessage = __('Gagal membuat pesanan: :msg', ['msg' => $throwable->getMessage()]);

            if ($throwable instanceof RequestException && $throwable->response) {
                $body = $throwable->response->json();
                $context['midtrans_status'] = $throwable->response->status();
                $context['midtrans_response'] = $body;

                $validationMessages = array_filter((array) ($body['validation_messages'] ?? []));
                $statusMessage = $body['status_message'] ?? null;

                if (! empty($validationMessages)) {
                    $userMessage = __('Gagal membuat pesanan: :msg', ['msg' => implode('; ', $validationMessages)]);
                } elseif ($statusMessage) {
                    $userMessage = __('Gagal membuat pesanan: :msg', ['msg' => $statusMessage]);
                }
            }

            Log::error('Checkout failed', $context);

            throw new CheckoutException($userMessage, 422);
        }

        return [
            'order' => $order,
            'payment' => $payment,
            'shipment' => $shipment,
        ];
    }

    public function handleMidtransCallback(array $payload): void
    {
        $midtrans = MidtransService::make();

        if (! $midtrans->verifySignature($payload)) {
            Log::warning('Invalid Midtrans signature received.', ['payload' => $payload]);
            throw new CheckoutException('Invalid signature.', 403);
        }

        $order = Order::query()
            ->where('order_number', $payload['order_id'] ?? null)
            ->with(['payment', 'shipment', 'items.product'])
            ->first();

        if (! $order) {
            Log::warning('Midtrans webhook for unknown order.', ['payload' => $payload]);
            throw new CheckoutException('Order not found.', 404);
        }

        DB::transaction(function () use ($order, $payload, $midtrans) {
            if ($order->payment) {
                $midtrans->updatePaymentFromResponse($order->payment, $payload, $order);
            }

            $this->updateOrderStatusFromNotification($order, $payload);
        });

        if ($this->shouldCreateShipmentFromNotification($payload)) {
            ShipmentDispatcher::make()->dispatch($order);
        }
    }

    private function calculateSummary(Collection $cartItems): array
    {
        return [
            'items' => $cartItems->sum('quantity'),
            'subtotal' => $cartItems->sum('subtotal'),
            'distinct' => $cartItems->count(),
        ];
    }

    private function refreshShippingRates(User $user, Address $address, Collection $cartItems): array
    {
        try {
            return BiteshipService::make()->getRates($address, $cartItems);
        } catch (\Throwable $throwable) {
            Log::error('Failed to refresh Biteship rates during checkout.', [
                'user_id' => $user->id,
                'message' => $throwable->getMessage(),
            ]);

            throw new CheckoutException(__('Gagal menghitung ongkos kirim. Silakan coba beberapa saat lagi.'), 422);
        }
    }

    private function formatFullAddress(Address $address): string
    {
        return collect([
            $address->detail,
            $address->district,
            $address->city,
            $address->province,
            $address->postal_code,
        ])->filter()->implode(', ');
    }

    private function buildAddressSnapshot(Address $address): array
    {
        return [
            'address_id' => $address->id,
            'recipient_name' => $address->recipient_name,
            'phone' => $address->phone,
            'detail' => $address->detail,
            'district' => $address->district,
            'city' => $address->city,
            'province' => $address->province,
            'postal_code' => $address->postal_code,
            'biteship_area_id' => $address->biteship_area_id,
        ];
    }

    private function buildShipmentMetadata(array $selectedRate, Address $address): array
    {
        return [
            'selected_rate' => $selectedRate,
            'destination' => $this->buildAddressSnapshot($address),
            'full_address' => $this->formatFullAddress($address),
        ];
    }

    private function buildItemDetails(Order $order): array
    {
        $items = $order->items->map(function (OrderItem $item) {
            return [
                'id' => (string) $item->id,
                'name' => Str::limit($item->product_name, 50),
                'price' => (float) $item->price,
                'quantity' => $item->quantity,
            ];
        })->all();

        $items[] = [
            'id' => 'shipping-'.$order->shipment?->courier_code,
            'name' => 'Biaya Pengiriman - '.($order->shipment?->courier_service ?? 'Kurir'),
            'price' => (float) $order->shipping_cost,
            'quantity' => 1,
        ];

        return MidtransService::make()->normalizeItemDetails($items);
    }

    private function buildCustomerDetails(string $name, string $email, Address $address): array
    {
        return [
            'first_name' => $name,
            'email' => $email,
            'phone' => $address->phone,
            'shipping_address' => [
                'first_name' => $address->recipient_name,
                'phone' => $address->phone,
                'address' => $address->detail,
                'city' => $address->city,
                'postal_code' => $address->postal_code,
                'country_code' => 'IDN',
            ],
            'billing_address' => [
                'first_name' => $name,
                'phone' => $address->phone,
                'address' => $address->detail,
                'city' => $address->city,
                'postal_code' => $address->postal_code,
                'country_code' => 'IDN',
            ],
        ];
    }

    private function updateOrderStatusFromNotification(Order $order, array $payload): void
    {
        $status = $payload['transaction_status'] ?? null;
        $fraud = $payload['fraud_status'] ?? null;

        if (in_array($status, ['capture', 'settlement'], true) && ($fraud === null || $fraud === 'accept')) {
            $order->forceFill([
                'order_status' => 'processing',
                'paid_at' => $order->paid_at ?? now(),
            ])->save();
        }

        if (in_array($status, ['cancel', 'deny', 'expire'], true)) {
            $order->forceFill([
                'order_status' => 'canceled',
            ])->save();
        }
    }

    private function shouldCreateShipmentFromNotification(array $payload): bool
    {
        $status = $payload['transaction_status'] ?? null;
        $fraud = $payload['fraud_status'] ?? null;

        if ($status === 'capture') {
            return $fraud === null || $fraud === 'accept';
        }

        return $status === 'settlement';
    }
}
