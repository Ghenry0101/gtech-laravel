<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Http\Requests\CheckoutShippingRequest;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Shipment;
use App\Services\Biteship\BiteshipService;
use App\Services\Midtrans\MidtransService;
use App\Services\Shipping\ShipmentDispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $cartItems = $this->getCartItems($user->id);

        if ($cartItems->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->withErrors(['cart' => __('Keranjang Anda masih kosong.')]);
        }

        $addresses = $user->addresses()->orderByDesc('is_default')->get();
        $defaultAddress = $addresses->firstWhere('is_default', true) ?? $addresses->first();

        $shippingOptions = [];

        if ($defaultAddress) {
            try {
                $shippingOptions = BiteshipService::make()->getRates($defaultAddress, $cartItems);
            } catch (\Throwable $throwable) {
                Log::warning('Unable to load Biteship rates for checkout page.', [
                    'user_id' => $user->id,
                    'message' => $throwable->getMessage(),
                ]);
            }
        }

        return view('checkout.index', [
            'cartItems' => $cartItems,
            'summary' => $this->calculateSummary($cartItems),
            'addresses' => $addresses,
            'defaultAddress' => $defaultAddress,
            'shippingOptions' => $shippingOptions,
            'paymentMethods' => config('midtrans.payment_methods', []),
            'midtransClientKey' => MidtransService::make()->getClientKey(),
            'snapScriptUrl' => MidtransService::make()->snapScriptUrl(),
        ]);
    }

    public function shippingRates(CheckoutShippingRequest $request): JsonResponse
    {
        $user = $request->user();
        $address = $user->addresses()->whereKey($request->validated('address_id'))->firstOrFail();
        $cartItems = $this->getCartItems($user->id);

        if ($cartItems->isEmpty()) {
            return response()->json([
                'message' => __('Keranjang kosong.'),
            ], 422);
        }

        try {
            $rates = BiteshipService::make()->getRates($address, $cartItems);
        } catch (\Throwable $throwable) {
            Log::error('Failed to retrieve Biteship rates.', [
                'address_id' => $address->id,
                'message' => $throwable->getMessage(),
            ]);

            return response()->json([
                'message' => __('Gagal mengambil ongkos kirim dari Biteship. Pastikan alamat valid.'),
            ], 422);
        }

        return response()->json([
            'data' => $rates,
        ]);
    }

    public function store(CheckoutRequest $request): JsonResponse
    {
        $user = $request->user();
        $cartItems = $this->getCartItems($user->id);

        if ($cartItems->isEmpty()) {
            return response()->json([
                'message' => __('Keranjang Anda kosong.'),
            ], 422);
        }

        $address = $user->addresses()->whereKey($request->validated('address_id'))->firstOrFail();

        $biteship = BiteshipService::make();
        $midtrans = MidtransService::make();

        $paymentMethodKey = $request->validated('payment_method');
        $paymentMethod = $midtrans->getPaymentMethod($paymentMethodKey);

        if (! $paymentMethod) {
            return response()->json([
                'message' => __('Metode pembayaran tidak valid.'),
            ], 422);
        }

        try {
            $rates = $biteship->getRates($address, $cartItems);
        } catch (\Throwable $throwable) {
            Log::error('Failed to refresh Biteship rates during checkout.', [
                'user_id' => $user->id,
                'message' => $throwable->getMessage(),
            ]);

            return response()->json([
                'message' => __('Gagal menghitung ongkos kirim. Silakan coba beberapa saat lagi.'),
            ], 422);
        }

        $selectedRate = collect($rates)->first(function (array $rate) use ($request) {
            return $rate['courier_code'] === $request->validated('shipping_courier_code')
                && $rate['courier_service_code'] === $request->validated('shipping_service_code');
        });

        if (! $selectedRate) {
            return response()->json([
                'message' => __('Pilihan ekspedisi tidak ditemukan.'),
            ], 422);
        }

        try {
            [$order, $payment, $shipment] = DB::transaction(function () use (
                $user,
                $address,
                $cartItems,
                $selectedRate,
                $request,
                $midtrans,
                $paymentMethodKey
            ) {
            $itemsTotal = $cartItems->sum('subtotal');
            $shippingCost = $selectedRate['cost'];
            $grandTotal = $itemsTotal + $shippingCost;

            $order = Order::create([
                'user_id' => $user->id,
                'address_id' => $address->id,
                'total_amount' => $itemsTotal,
                'shipping_cost' => $shippingCost,
                'grand_total' => $grandTotal,
                'order_status' => 'pending',
                'notes' => $request->validated('notes'),
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
                'rate_payload' => $selectedRate['raw'] ?? null,
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

            $midtrans->createSnapTransaction(
                order: $order->fresh('items'),
                payment: $payment,
                itemDetails: $itemDetails,
                customerDetails: $customerDetails,
                paymentMethodKey: $paymentMethodKey,
            );

                return [$order, $payment->fresh(), $shipment];
            });
        } catch (\Throwable $throwable) {
            Log::error('Checkout failed', [
                'user_id' => $user->id,
                'message' => $throwable->getMessage(),
            ]);

            return response()->json([
                'message' => __('Gagal membuat pesanan: :msg', ['msg' => $throwable->getMessage()]),
            ], 422);
        }

        return response()->json([
            'message' => __('Pesanan berhasil dibuat. Lanjutkan pembayaran.'),
            'order_number' => $order->order_number,
            'snap_token' => $payment->snap_token,
            'redirect_url' => $payment->snap_redirect_url,
            'shipment' => [
                'courier' => $shipment->courier_name,
                'service' => $shipment->courier_service,
            ],
        ]);
    }

    public function callback(Request $request): JsonResponse
    {
        $payload = $request->all();
        $midtrans = MidtransService::make();

        if (! $midtrans->verifySignature($payload)) {
            Log::warning('Invalid Midtrans signature received.', ['payload' => $payload]);

            return response()->json(['message' => 'Invalid signature.'], 403);
        }

        $order = Order::query()
            ->where('order_number', $payload['order_id'] ?? null)
            ->with(['payment', 'shipment', 'items.product', 'address'])
            ->first();

        if (! $order) {
            Log::warning('Midtrans webhook for unknown order.', ['payload' => $payload]);

            return response()->json(['message' => 'Order not found.'], 404);
        }

        DB::transaction(function () use ($order, $payload) {
            $payment = $order->payment;

            if ($payment) {
                $payment->forceFill([
                    'transaction_id' => $payload['transaction_id'] ?? $payment->transaction_id,
                    'payment_type' => $payload['payment_type'] ?? $payment->payment_type,
                    'bank' => data_get($payload, 'va_numbers.0.bank', $payment->bank),
                    'va_number' => data_get($payload, 'va_numbers.0.va_number', $payment->va_number),
                    'payment_status' => $payload['transaction_status'] ?? $payment->payment_status,
                    'fraud_status' => $payload['fraud_status'] ?? $payment->fraud_status,
                    'gross_amount' => $payload['gross_amount'] ?? $payment->gross_amount,
                    'paid_at' => in_array($payload['transaction_status'] ?? null, ['capture', 'settlement'], true)
                        ? now()
                        : $payment->paid_at,
                ])->save();
            }

            $this->updateOrderStatusFromNotification($order, $payload);
        });

        if ($this->shouldCreateShipmentFromNotification($payload)) {
            ShipmentDispatcher::make()->dispatch($order);
        }

        return response()->json(['message' => 'Webhook processed.']);
    }

    protected function getCartItems(int $userId): Collection
    {
        return Cart::query()
            ->with('product.category')
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->latest()
            ->get();
    }

    protected function calculateSummary(Collection $cartItems): array
    {
        return [
            'items' => $cartItems->sum('quantity'),
            'subtotal' => $cartItems->sum('subtotal'),
            'distinct' => $cartItems->count(),
        ];
    }

    protected function buildItemDetails(Order $order): array
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

    protected function buildCustomerDetails(string $name, string $email, $address): array
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

    protected function updateOrderStatusFromNotification(Order $order, array $payload): void
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

    protected function shouldCreateShipmentFromNotification(array $payload): bool
    {
        $status = $payload['transaction_status'] ?? null;
        $fraud = $payload['fraud_status'] ?? null;

        if ($status === 'capture') {
            return $fraud === null || $fraud === 'accept';
        }

        return $status === 'settlement';
    }
}
