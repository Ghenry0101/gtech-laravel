<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $relatedProducts = Product::query()
            ->where('is_active', true)
            ->whereKeyNot($product->id)
            ->where('category_id', $product->category_id)
            ->latest()
            ->take(4)
            ->get();

        $reviews = $product->reviews()
            ->with(['orderItem.order.user'])
            ->latest('created_at')
            ->take(6)
            ->get();

        $reviewCount = $product->reviews()->count();
        $averageRating = $reviewCount > 0 ? round((float) $product->reviews()->avg('rating'), 1) : null;
        $reviewStats = [
            'count' => $reviewCount,
            'average' => $averageRating,
        ];

        return view('products.show', [
            'product' => $product->load('category'),
            'relatedProducts' => $relatedProducts,
            'reviews' => $reviews,
            'reviewStats' => $reviewStats,
        ]);
    }
}
