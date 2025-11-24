<?php

namespace App\Http\Controllers;

use App\Exceptions\CheckoutException;
use App\Http\Requests\CheckoutRequest;
use App\Http\Requests\CheckoutShippingRequest;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CheckoutService $checkout
    ) {
    }

    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $cartItems = $this->checkout->getCartItems($user->id);

        if ($cartItems->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->withErrors(['cart' => __('Keranjang Anda masih kosong.')]);
        }

        return view('checkout.index', $this->checkout->prepareCheckoutViewData($user, $cartItems));
    }

    public function shippingRates(CheckoutShippingRequest $request): JsonResponse
    {
        try {
            $rates = $this->checkout->fetchShippingRates($request->user(), $request->validated('address_id'));
        } catch (CheckoutException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], $exception->status());
        }

        return response()->json([
            'data' => $rates,
        ]);
    }

    public function store(CheckoutRequest $request): JsonResponse
    {
        try {
            $result = $this->checkout->processCheckout($request->user(), $request->validated());
        } catch (CheckoutException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], $exception->status());
        }

        ['order' => $order, 'payment' => $payment] = $result;

        return response()->json([
            'message' => __('Pesanan berhasil dibuat. Lanjutkan pembayaran.'),
            'order_number' => $order->order_number,
            'payment' => [
                'type' => $payment->payment_type,
                'status' => $payment->payment_status,
                'bank' => $payment->bank,
                'va_number' => $payment->va_number,
                'payment_link' => $payment->payment_link,
                'qr_string' => $payment->qr_string,
            ],
        ]);
    }

    public function callback(Request $request): JsonResponse
    {
        try {
            $this->checkout->handleMidtransCallback($request->all());
        } catch (CheckoutException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], $exception->status());
        }

        return response()->json(['message' => 'Webhook processed.']);
    }
}
