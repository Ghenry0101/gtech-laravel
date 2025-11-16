<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Support\CategoryMenu;
use App\ViewModels\ProductCardViewModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request, ?Category $category = null): View
    {
        if ($category && ! $category->is_active) {
            abort(404);
        }

        $searchInput = trim($request->string('q')->toString());
        if ($searchInput === '') {
            $searchInput = trim($request->string('query')->toString());
        }
        $hasQuery = $searchInput !== '';

        if (! Schema::hasTable('products') || ! Schema::hasTable('categories')) {
            return view('products.index', [
                'categories' => [],
                'items' => [],
                'active' => null,
                'search' => $searchInput,
                'hasQuery' => $hasQuery,
            ]);
        }

        $productCounts = Product::query()
            ->where('is_active', true)
            ->selectRaw('category_id, COUNT(*) as aggregate')
            ->groupBy('category_id')
            ->pluck('aggregate', 'category_id');

        $categories = collect(CategoryMenu::active())
            ->map(function (array $cat) use ($productCounts) {
                $categoryId = $cat['id'] ?? null;
                $cat['has_products'] = $categoryId ? ($productCounts->get($categoryId, 0) > 0) : false;

                return $cat;
            })
            ->values()
            ->all();

        $products = Product::query()
            ->where('is_active', true)
            ->when($category, fn ($query) => $query->where('category_id', $category->getKey()))
            ->when($hasQuery, function ($query) use ($searchInput) {
                $query->where(function ($builder) use ($searchInput) {
                    $builder
                        ->where('name', 'like', "%{$searchInput}%")
                        ->orWhere('description', 'like', "%{$searchInput}%")
                        ->orWhereHas('category', function ($categoryQuery) use ($searchInput) {
                            $categoryQuery->where('name', 'like', "%{$searchInput}%");
                        });
                });
            })
            ->latest('updated_at')
            ->get();

        return view('products.index', [
            'categories' => $categories,
            'items' => ProductCardViewModel::collection($products),
            'active' => $category?->slug,
            'search' => $searchInput,
            'hasQuery' => $hasQuery,
        ]);
    }

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
