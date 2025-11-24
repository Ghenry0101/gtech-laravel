<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Storefront\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orders
    ) {
    }

    public function index(Request $request): View
    {
        $orders = $this->orders->paginate($request->user());
        $statusMeta = $this->orders->statusMeta();

        return view('orders.index', [
            'orders' => $orders,
            'statusMeta' => $statusMeta,
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        $this->orders->ensureOwner($request->user(), $order);
        $order = $this->orders->loadDetails($order);
        $statusMeta = $this->orders->statusMeta();

        return view('orders.show', [
            'order' => $order,
            'statusMeta' => $statusMeta,
            'currentStatus' => $statusMeta[$order->order_status] ?? null,
            'paymentMethods' => config('midtrans.payment_methods', []),
            'cameFromCheckout' => $request->boolean('from_checkout'),
            'paymentExpiresAt' => $this->orders->paymentExpiresAt($order),
        ]);
    }

    public function complete(Request $request, Order $order): RedirectResponse
    {
        $this->orders->ensureOwner($request->user(), $order);

        abort_unless(in_array($order->order_status, ['processing', 'shipped'], true), 422, __('Pesanan belum dapat ditandai selesai.'));

        $this->orders->complete($order);

        return back()
            ->with('status', 'order-completed')
            ->with('status_message', __('Terima kasih! Pesanan Anda kami tandai selesai. Silakan bagikan ulasan.'));
    }
}
