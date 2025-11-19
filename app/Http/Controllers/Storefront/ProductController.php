<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function show(Request $request, Product $product): View
    {
        abort_unless($product->is_active, 404);

        $ratingFilter = $request->integer('rating');
        if ($ratingFilter < 1 || $ratingFilter > 5) {
            $ratingFilter = null;
        }

        $relatedProducts = Product::query()
            ->where('is_active', true)
            ->whereKeyNot($product->id)
            ->where('category_id', $product->category_id)
            ->latest()
            ->take(4)
            ->get();

        $reviewsQuery = $product->reviews()
            ->with(['orderItem.order.user', 'images'])
            ->latest('created_at');

        if ($ratingFilter !== null) {
            $reviewsQuery->where('rating', $ratingFilter);
        }

        $reviews = $reviewsQuery->take(6)->get();

        $reviewCount = $product->reviews()->count();
        $averageRating = $reviewCount > 0 ? round((float) $product->reviews()->avg('rating'), 1) : null;
        $ratingBuckets = $product->reviews()
            ->selectRaw('rating, COUNT(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating')
            ->toArray();

        $reviewStats = [
            'count' => $reviewCount,
            'average' => $averageRating,
        ];

        return view('products.show', [
            'product' => $product->load('category'),
            'relatedProducts' => $relatedProducts,
            'reviews' => $reviews,
            'reviewStats' => $reviewStats,
            'ratingFilter' => $ratingFilter,
            'ratingBuckets' => $ratingBuckets,
        ]);
    }
}