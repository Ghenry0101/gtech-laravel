<?php

namespace App\Services\Storefront;

use App\Models\Category;
use App\Models\Product;
use App\Support\CategoryMenu;
use App\ViewModels\ProductCardViewModel;
use Illuminate\Support\Facades\Schema;

class ProductBrowseService
{
    /**
     * Build the data needed by the storefront product listing page.
     *
     * @return array<string, mixed>
     */
    public function listData(?string $categorySlug, string $searchInput): array
    {
        $category = $this->findCategory($categorySlug);
        $categoryMissing = $categorySlug !== null && $category === null;
        $hasQuery = $searchInput !== '';

        if (! Schema::hasTable('products') || ! Schema::hasTable('categories')) {
            return [
                'categories' => CategoryMenu::active(),
                'items' => [],
                'active' => $categorySlug,
                'search' => $searchInput,
                'hasQuery' => $hasQuery,
                'categoryMissing' => $categoryMissing,
            ];
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

        $products = collect();

        if (! $categoryMissing) {
            $productsQuery = Product::query()
                ->where('is_active', true)
                ->when($category, fn ($query) => $query->where('category_id', $category->getKey()));

            if ($hasQuery) {
                $productsQuery->where(function ($builder) use ($searchInput) {
                    $builder
                        ->where('name', 'like', "%{$searchInput}%")
                        ->orWhere('description', 'like', "%{$searchInput}%")
                        ->orWhereHas('category', function ($categoryQuery) use ($searchInput) {
                            $categoryQuery->where('name', 'like', "%{$searchInput}%");
                        });
                });
            }

            $products = $productsQuery->latest('updated_at')->get();
        }

        return [
            'categories' => $categories,
            'items' => ProductCardViewModel::collection($products),
            'active' => $categorySlug,
            'search' => $searchInput,
            'hasQuery' => $hasQuery,
            'categoryMissing' => $categoryMissing,
        ];
    }

    /**
     * Assemble the product detail data, including related products and reviews.
     *
     * @return array<string, mixed>
     */
    public function detailData(Product $product, ?int $ratingFilter): array
    {
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

        $reviewsRelation = $product->reviews();
        $reviewCount = (clone $reviewsRelation)->count();
        $averageRating = $reviewCount > 0 ? round((float) (clone $reviewsRelation)->avg('rating'), 1) : null;
        $ratingBuckets = (clone $reviewsRelation)
            ->selectRaw('rating, COUNT(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating')
            ->toArray();

        $ratingDistribution = collect(range(5, 1))
            ->mapWithKeys(fn ($rating) => [$rating => $ratingBuckets[$rating] ?? 0])
            ->toArray();

        return [
            'product' => $product->load('category'),
            'relatedProducts' => $relatedProducts,
            'reviews' => $reviews,
            'reviewStats' => [
                'count' => $reviewCount,
                'average' => $averageRating,
                'distribution' => $ratingDistribution,
            ],
            'ratingFilter' => $ratingFilter,
            'selectedRating' => $ratingFilter,
        ];
    }

    private function findCategory(?string $slug): ?Category
    {
        if (! $slug) {
            return null;
        }

        return Category::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }
}

