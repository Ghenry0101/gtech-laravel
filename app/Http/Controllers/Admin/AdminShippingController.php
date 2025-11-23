<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ShipmentDetailRequest;
use App\Http\Requests\Admin\ShipmentStatusRequest;
use App\Models\Order;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminShippingController extends Controller
{
    /**
     * Halaman utama admin pengiriman: statistik, antrian, dan pencarian pesanan.
     */
    public function index(Request $request): View
    {
        $allowedFilters = ['all', 'processing', 'shipped', 'completed'];
        $filter = strtolower((string) $request->query('status', 'processing'));
        $search = trim((string) $request->query('q', ''));

        if (! in_array($filter, $allowedFilters, true)) {
            $filter = 'processing';
        }

        $baseQuery = Order::query()
            ->with([
                'user:id,name,email,phone',
                'items:id,order_id,product_id,product_name,quantity',
                'items.product:id,sku',
                'shipment:id,order_id,courier_name,courier_service,status,tracking_id,waybill_id,shipping_cost,shipped_at,delivered_at,updated_at',
                'payment:id,order_id,payment_status,gross_amount,paid_at',
            ])
            ->whereHas('shipment');

        $ordersQuery = clone $baseQuery;

        $ordersQuery->when($filter !== 'all', function (Builder $query) use ($filter): void {
            if ($filter === 'processing') {
                $query->whereIn('order_status', ['pending', 'processing']);
            } elseif ($filter === 'shipped') {
                $query->where('order_status', 'shipped');
            } elseif ($filter === 'completed') {
                $query->where('order_status', 'completed');
            }
        });

        if ($search !== '') {
            $ordersQuery->where(function (Builder $query) use ($search): void {
                $query->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function (Builder $userQuery) use ($search): void {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $orders = $ordersQuery
            ->latest('order_time')
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        $paymentSuccessStatuses = ['capture', 'settlement'];
        $statQuery = Order::query()->whereHas('shipment');
        $stats = [
            'queue' => (clone $statQuery)->whereIn('order_status', ['pending', 'processing'])->count(),
            'in_transit' => (clone $statQuery)->where('order_status', 'shipped')->count(),
            'completed' => (clone $statQuery)->where('order_status', 'completed')->count(),
            'delayed' => (clone $statQuery)
                ->whereIn('order_status', ['processing', 'shipped'])
                ->where('order_time', '<', now()->subDays(3))
                ->count(),
            'paid' => (clone $statQuery)->whereHas('payment', function (Builder $query) use ($paymentSuccessStatuses) {
                $query->whereIn('payment_status', $paymentSuccessStatuses);
            })->count(),
            'unpaid' => (clone $statQuery)->where(function (Builder $query) use ($paymentSuccessStatuses) {
                $query->whereDoesntHave('payment')
                    ->orWhereHas('payment', function (Builder $paymentQuery) use ($paymentSuccessStatuses) {
                        $paymentQuery->whereNotIn('payment_status', $paymentSuccessStatuses);
                    });
            })->count(),
        ];

        $paidOrdersSample = (clone $baseQuery)
            ->whereHas('payment', function (Builder $query) use ($paymentSuccessStatuses) {
                $query->whereIn('payment_status', $paymentSuccessStatuses);
            })
            ->latest('order_time')
            ->take(5)
            ->get();

        $unpaidOrdersSample = (clone $baseQuery)
            ->where(function (Builder $query) use ($paymentSuccessStatuses) {
                $query->whereDoesntHave('payment')
                    ->orWhereHas('payment', function (Builder $paymentQuery) use ($paymentSuccessStatuses) {
                        $paymentQuery->whereNotIn('payment_status', $paymentSuccessStatuses);
                    });
            })
            ->latest('order_time')
            ->take(5)
            ->get();

        $recentActivities = Order::query()
            ->with([
                'shipment:id,order_id,status,tracking_id,waybill_id,updated_at',
                'user:id,name',
            ])
            ->whereHas('shipment')
            ->latest('updated_at')
            ->take(5)
            ->get();

        return view('admin.pengiriman.dashboard', [
            'orders' => $orders,
            'stats' => $stats,
            'filter' => $filter,
            'search' => $search,
            'recentActivities' => $recentActivities,
            'paidSample' => $paidOrdersSample,
            'unpaidSample' => $unpaidOrdersSample,
        ]);
    }

    /**
     * Detail pesanan + pengiriman untuk admin pengiriman.
     */
    public function show(Order $order): View
    {
        abort_unless($order->shipment, 404);

        $order->loadMissing([
            'items.product',
            'shipment.trackings',
            'payment',
            'user',
        ]);

        return view('admin.pengiriman.orders.show', [
            'order' => $order,
        ]);
    }

    /**
     * Perbarui metadata pengiriman (kurir, layanan, resi, estimasi).
     */
    public function updateShipment(ShipmentDetailRequest $request, Order $order): RedirectResponse
    {
        abort(403, 'Admin pengiriman tidak dapat mengubah estimasi pengiriman.');
    }

    /**
     * Perbarui status pengiriman dan sinkronkan dengan status pesanan pelanggan.
     */
    public function updateStatus(ShipmentStatusRequest $request, Order $order): RedirectResponse
    {
        abort(403, 'Admin pengiriman tidak dapat mengubah status pengiriman.');
    }

    /**
     * Susun perubahan status pengiriman beserta efek ke pesanan.
     */
    private function prepareShipmentStatusUpdates(Order $order, string $status, ?string $trackingId, ?string $waybillId): array
    {
        $shipment = $order->shipment;
        $shipmentUpdates = [
            'status' => $status,
        ];

        if ($trackingId) {
            $shipmentUpdates['tracking_id'] = $trackingId;
        }

        if ($waybillId) {
            $shipmentUpdates['waybill_id'] = $waybillId;
        }

        $processingStatuses = ['courier_allocated', 'picking_up', 'picked'];
        $shippingStatuses = ['on_the_way', 'delivering', 'shipped'];

        if (in_array($status, $shippingStatuses, true)) {
            $shipmentUpdates['shipped_at'] = $shipment?->shipped_at ?? now();
            $shipmentUpdates['delivered_at'] = null;
            $orderStatus = 'shipped';
        } elseif ($status === 'delivered') {
            $shipmentUpdates['delivered_at'] = $shipment?->delivered_at ?? now();
            $orderStatus = 'completed';
        } elseif (in_array($status, $processingStatuses, true)) {
            $shipmentUpdates['shipped_at'] = null;
            $shipmentUpdates['delivered_at'] = null;
            $orderStatus = 'processing';
        } else {
            $shipmentUpdates['shipped_at'] = null;
            $shipmentUpdates['delivered_at'] = null;
            $orderStatus = 'processing';
        }

        return [$shipmentUpdates, $orderStatus];
    }
}
