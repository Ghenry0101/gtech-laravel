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
        $totalProducts = Product::where('is_active', true)->count();
        $inactiveProducts = Product::where('is_active', false)->count();
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

        $topSellingProducts = $this->topSellingProducts();

        return view('admin.barang.dashboard', [
            'stats' => [
                'totalProducts' => $totalProducts,
                'inactiveProducts' => $inactiveProducts,
                'lowStockCount' => $lowStockProducts->count(),
            ],
            'lowStockProducts' => $lowStockProducts,
            'latestProducts' => $latestProducts,
            'topSellingProducts' => $topSellingProducts,
            'lowStockThreshold' => $lowStockThreshold,
        ]);
    }

    /**
     * Collect best-selling products ordered by quantity sold.
     *
     * @return \Illuminate\Support\Collection<int, array{
     *     product_id: int|null,
     *     product_name: string,
     *     total_qty: int,
     *     total_revenue: int
     * }>
     */
    protected function topSellingProducts(): Collection
    {
        return OrderItem::query()
            ->select([
                'product_id',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(subtotal) as total_revenue'),
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
                'total_revenue' => (int) $item->total_revenue,
            ])
            ->values();
    }
}
