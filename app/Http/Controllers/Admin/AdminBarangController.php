<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminBarangController extends Controller
{
    /**
     * Display the Admin Barang dashboard.
     */
    public function index(): View
    {
        $now = now();
        $activeProductsQuery = Product::query()->where('is_active', true);

        $totalProducts = (clone $activeProductsQuery)->count();
        $inactiveProducts = Product::where('is_active', false)->count();

        $totalStockUnits = (clone $activeProductsQuery)->sum('stock');

        $discountBaseQuery = (clone $activeProductsQuery)->where(function ($query) {
            $query->whereNotNull('discount_percent')
                ->orWhereNotNull('discount_price');
        });

        $discountedProducts = (clone $discountBaseQuery)->count();
        $activeDiscounts = (clone $discountBaseQuery)
            ->where(function ($query) use ($now) {
                $query->whereNull('discount_start')->orWhere('discount_start', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('discount_end')->orWhere('discount_end', '>=', $now);
            })
            ->count();
        $upcomingDiscounts = (clone $discountBaseQuery)
            ->whereNotNull('discount_start')
            ->where('discount_start', '>', $now)
            ->count();

        $lowStockThreshold = 10;
        $lowStockProducts = Product::query()
            ->where('is_active', true)
            ->where('stock', '<=', $lowStockThreshold)
            ->orderBy('stock')
            ->take(5)
            ->get(['id', 'name', 'stock', 'slug']);

        $latestProducts = Product::query()
            ->where('is_active', true)
            ->latest()
            ->take(5)
            ->get(['id', 'name', 'price', 'stock', 'created_at']);

        $activeDiscountProducts = (clone $discountBaseQuery)
            ->orderByDesc(DB::raw('COALESCE(discount_percent, 0)'))
            ->take(5)
            ->get(['id', 'name', 'price', 'discount_percent', 'discount_price', 'discount_start', 'discount_end', 'stock']);

        $inactiveProductsList = Product::query()
            ->where('is_active', false)
            ->latest('updated_at')
            ->take(5)
            ->get(['id', 'name', 'price', 'updated_at']);

        $topSellingProducts = $this->topSellingProducts();

        return view('admin.barang.dashboard', [
            'stats' => [
                'totalProducts' => $totalProducts,
                'inactiveProducts' => $inactiveProducts,
                'lowStockCount' => $lowStockProducts->count(),
                'totalStockUnits' => (int) $totalStockUnits,
                'discountedProducts' => $discountedProducts,
                'activeDiscounts' => $activeDiscounts,
                'upcomingDiscounts' => $upcomingDiscounts,
            ],
            'lowStockProducts' => $lowStockProducts,
            'latestProducts' => $latestProducts,
            'topSellingProducts' => $topSellingProducts,
            'activeDiscountProducts' => $activeDiscountProducts,
            'inactiveProductsList' => $inactiveProductsList,
            'lowStockThreshold' => $lowStockThreshold,
        ]);
    }

    /**
     * Collects best-selling products summary for display.
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    protected function topSellingProducts(): Collection
    {
        return OrderItem::query()
            ->select([
                'product_id',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('COUNT(DISTINCT order_id) as total_orders'),
            ])
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->with('product:id,name,price')
            ->take(5)
            ->get()
            ->filter(fn (OrderItem $item) => $item->product !== null)
            ->map(fn (OrderItem $item) => [
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'total_qty' => (int) $item->total_qty,
                'total_orders' => (int) $item->total_orders,
            ])
            ->values();
    }
}
