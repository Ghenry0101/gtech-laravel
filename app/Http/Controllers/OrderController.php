<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::query()
            ->with([
                'items:id,order_id,product_name,quantity,price',
                'items.review:id,order_item_id,rating,title,comment,reviewed_at',
                'items.review.images:id,review_id,path,position',
                'shipment:id,order_id,courier_name,courier_service,status,tracking_id,waybill_id',
                'payment:id,order_id,payment_status,payment_type,paid_at',
            ])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('order_time')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('orders.index', [
            'orders' => $orders,
            'statusMeta' => $this->statusMeta(),
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        $this->ensureOwnerAccess($request, $order);

        $order->loadMissing([
            'items.product',
            'items.review',
            'items.review.images',
            'shipment',
            'payment',
            'address',
            'complaints',
        ]);

        $statusMeta = $this->statusMeta();

        return view('orders.show', [
            'order' => $order,
            'statusMeta' => $statusMeta,
            'currentStatus' => $statusMeta[$order->order_status] ?? null,
            'paymentMethods' => config('midtrans.payment_methods', []),
            'cameFromCheckout' => $request->boolean('from_checkout'),
        ]);
    }

    public function complete(Request $request, Order $order): RedirectResponse
    {
        $this->ensureOwnerAccess($request, $order);

        abort_unless(in_array($order->order_status, ['processing', 'shipped'], true), 422, __('Pesanan belum dapat ditandai selesai.'));

        DB::transaction(function () use ($order): void {
            $order->forceFill([
                'order_status' => 'completed',
            ])->save();

            if ($order->shipment) {
                $order->shipment->forceFill([
                    'status' => 'delivered',
                    'shipped_at' => $order->shipment->shipped_at ?? now(),
                    'delivered_at' => now(),
                ])->save();
            }
        });

        return back()
            ->with('status', 'order-completed')
            ->with('status_message', __('Terima kasih! Pesanan Anda kami tandai selesai. Silakan bagikan ulasan.'));
    }

    protected function ensureOwnerAccess(Request $request, Order $order): void
    {
        abort_unless($order->user_id === $request->user()->id, 404);
    }

    /**
     * @return array<string, array<string, string>>
     */
    protected function statusMeta(): array
    {
        return [
            'pending' => [
                'label' => __('Menunggu Pembayaran'),
                'badge_class' => 'bg-amber-100 text-amber-800',
                'description' => __('Pesanan berhasil dibuat dan menunggu konfirmasi pembayaran.'),
            ],
            'processing' => [
                'label' => __('Sedang Diproses'),
                'badge_class' => 'bg-sky-100 text-sky-800',
                'description' => __('Pembayaran telah diterima dan pesanan sedang disiapkan.'),
            ],
            'shipped' => [
                'label' => __('Dalam Pengiriman'),
                'badge_class' => 'bg-indigo-100 text-indigo-800',
                'description' => __('Kurir telah mengambil paket dan sedang menuju alamat Anda.'),
            ],
            'completed' => [
                'label' => __('Selesai'),
                'badge_class' => 'bg-emerald-100 text-emerald-800',
                'description' => __('Pesanan telah diterima pelanggan.'),
            ],
            'canceled' => [
                'label' => __('Dibatalkan'),
                'badge_class' => 'bg-rose-100 text-rose-800',
                'description' => __('Pesanan dibatalkan. Hubungi CS jika butuh bantuan.'),
            ],
        ];
    }
}
