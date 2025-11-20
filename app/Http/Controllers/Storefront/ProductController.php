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
        public function index(Request $request, ?string $categorySlug = null): View
    {
        $category = null;
        if ($categorySlug) {
            $category = Category::query()
                ->where('slug', $categorySlug)
                ->where('is_active', true)
                ->first();
        }
        $categoryMissing = $categorySlug !== null && $category === null;

        $searchInput = trim($request->string('q')->toString());
        if ($searchInput === '') {
            $searchInput = trim($request->string('query')->toString());
        }
        $hasQuery = $searchInput !== '';

        if (! Schema::hasTable('products') || ! Schema::hasTable('categories')) {
            return view('products.index', [
                'categories' => CategoryMenu::active(),
                'items' => [],
                'active' => $categorySlug,
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

        $productsQuery = Product::query()
            ->where('is_active', true)
            ->when($category, fn ($query) => $query->where('category_id', $category->getKey()));

        if (! $categoryMissing) {
            $productsQuery->when($hasQuery, function ($query) use ($searchInput) {
                $query->where(function ($builder) use ($searchInput) {
                    $builder
                        ->where('name', 'like', "%{$searchInput}%")
                        ->orWhere('description', 'like', "%{$searchInput}%")
                        ->orWhereHas('category', function ($categoryQuery) use ($searchInput) {
                            $categoryQuery->where('name', 'like', "%{$searchInput}%");
                        });
                });
            });
            $products = $productsQuery->latest('updated_at')->get();
        } else {
            $products = collect();
        }

        return view('products.index', [
            'categories' => $categories,
            'items' => ProductCardViewModel::collection($products),
            'active' => $categorySlug,
            'search' => $searchInput,
            'hasQuery' => $hasQuery,
            'categoryMissing' => $categoryMissing,
        ]);
    }

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
        $relatedProducts = ProductCardViewModel::collection($relatedProducts);

        $reviewsQuery = $product->reviews()
            ->with(['orderItem.order.user', 'images'])
            ->latest('created_at');

        if ($ratingFilter !== null) {
            $reviewsQuery->where('rating', $ratingFilter);
        }

        $reviews = $reviewsQuery
            ->paginate(6)
            ->withQueryString();

        $reviewCount = $product->reviews()->count();
        $averageRating = $reviewCount > 0 ? round((float) $product->reviews()->avg('rating'), 1) : null;
        $ratingBuckets = $product->reviews()
            ->selectRaw('rating, COUNT(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating')
            ->toArray();

        $ratingDistribution = collect(range(5, 1))
            ->mapWithKeys(fn ($rating) => [$rating => $ratingBuckets[$rating] ?? 0])
            ->toArray();

        $reviewStats = [
            'count' => $reviewCount,
            'average' => $averageRating,
            'distribution' => $ratingDistribution,
        ];

        return view('products.show', [
            'product' => $product->load('category'),
            'relatedProducts' => $relatedProducts,
            'reviews' => $reviews,
            'reviewStats' => $reviewStats,
            'ratingFilter' => $ratingFilter,
            'selectedRating' => $ratingFilter,
        ]);
    }
}
