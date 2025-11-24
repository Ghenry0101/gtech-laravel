<?php

namespace App\Services\Storefront;

use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function paginate(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return Order::query()
            ->with([
                'items:id,order_id,product_name,quantity,price',
                'items.review:id,order_item_id,rating,title,comment,reviewed_at',
                'items.review.images:id,review_id,path,position',
                'shipment:id,order_id,courier_name,courier_service,status,tracking_id,waybill_id',
                'payment:id,order_id,payment_status,payment_type,paid_at',
            ])
            ->where('user_id', $user->id)
            ->orderByDesc('order_time')
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function loadDetails(Order $order): Order
    {
        $order->loadMissing([
            'user',
            'items.product',
            'items.review',
            'items.review.images',
            'complaints.images',
            'shipment.trackings',
            'payment',
        ]);

        return $order;
    }

    public function complete(Order $order): void
    {
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
    }

    public function ensureOwner(User $user, Order $order): void
    {
        abort_unless($order->user_id === $user->id, 404);
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function statusMeta(): array
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

    public function paymentExpiresAt(Order $order): ?Carbon
    {
        $reference = $order->order_time ?? $order->created_at;

        return $reference ? $reference->copy()->addDay() : null;
    }
}

