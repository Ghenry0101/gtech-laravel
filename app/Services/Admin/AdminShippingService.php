<?php

namespace App\Services\Admin;

use App\Models\Order;
use Illuminate\Contracts\Database\Eloquent\Builder;

class AdminShippingService
{
    private const ALLOWED_FILTERS = ['all', 'processing', 'shipped', 'completed'];
    private const DEFAULT_FILTER = 'processing';
    private const PAGINATION_SIZE = 10;
    private const PAYMENT_SUCCESS_STATUSES = ['capture', 'settlement'];

    public function dashboardData(string $filter, string $search): array
    {
        $filter = $this->normalizeFilter($filter);
        $search = trim($search);

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

        $this->applyFilter($ordersQuery, $filter);
        $this->applySearch($ordersQuery, $search);

        $orders = $ordersQuery
            ->latest('order_time')
            ->latest('created_at')
            ->paginate(self::PAGINATION_SIZE)
            ->withQueryString();

        $statQuery = Order::query()->whereHas('shipment');

        $stats = [
            'queue' => (clone $statQuery)->whereIn('order_status', ['pending', 'processing'])->count(),
            'in_transit' => (clone $statQuery)->where('order_status', 'shipped')->count(),
            'completed' => (clone $statQuery)->where('order_status', 'completed')->count(),
            'delayed' => (clone $statQuery)
                ->whereIn('order_status', ['processing', 'shipped'])
                ->where('order_time', '<', now()->subDays(3))
                ->count(),
            'paid' => (clone $statQuery)->whereHas('payment', function (Builder $query): void {
                $query->whereIn('payment_status', self::PAYMENT_SUCCESS_STATUSES);
            })->count(),
            'unpaid' => (clone $statQuery)->where(function (Builder $query): void {
                $query->whereDoesntHave('payment')
                    ->orWhereHas('payment', function (Builder $paymentQuery): void {
                        $paymentQuery->whereNotIn('payment_status', self::PAYMENT_SUCCESS_STATUSES);
                    });
            })->count(),
        ];

        $paidOrdersSample = (clone $baseQuery)
            ->whereHas('payment', function (Builder $query): void {
                $query->whereIn('payment_status', self::PAYMENT_SUCCESS_STATUSES);
            })
            ->latest('order_time')
            ->take(5)
            ->get();

        $unpaidOrdersSample = (clone $baseQuery)
            ->where(function (Builder $query): void {
                $query->whereDoesntHave('payment')
                    ->orWhereHas('payment', function (Builder $paymentQuery): void {
                        $paymentQuery->whereNotIn('payment_status', self::PAYMENT_SUCCESS_STATUSES);
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

        return [
            'orders' => $orders,
            'stats' => $stats,
            'filter' => $filter,
            'search' => $search,
            'recentActivities' => $recentActivities,
            'paidSample' => $paidOrdersSample,
            'unpaidSample' => $unpaidOrdersSample,
        ];
    }

    public function hasShipment(Order $order): bool
    {
        return (bool) $order->shipment;
    }

    public function loadOrderDetails(Order $order): Order
    {
        $order->loadMissing([
            'items.product',
            'shipment.trackings',
            'payment',
            'user',
        ]);

        return $order;
    }

    private function normalizeFilter(?string $filter): string
    {
        $filter = strtolower((string) $filter);

        return in_array($filter, self::ALLOWED_FILTERS, true) ? $filter : self::DEFAULT_FILTER;
    }

    private function applyFilter(Builder $query, string $filter): void
    {
        if ($filter === 'all') {
            return;
        }

        if ($filter === 'processing') {
            $query->whereIn('order_status', ['pending', 'processing']);
        } elseif ($filter === 'shipped') {
            $query->where('order_status', 'shipped');
        } elseif ($filter === 'completed') {
            $query->where('order_status', 'completed');
        }
    }

    private function applySearch(Builder $query, string $search): void
    {
        if ($search === '') {
            return;
        }

        $query->where(function (Builder $builder) use ($search): void {
            $builder->where('order_number', 'like', "%{$search}%")
                ->orWhereHas('user', function (Builder $userQuery) use ($search): void {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
        });
    }
}

