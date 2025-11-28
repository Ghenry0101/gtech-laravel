<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Storefront\ProductBrowseService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductBrowseService $products
    ) {
    }

    public function index(Request $request, ?string $categorySlug = null): View
    {
        $searchInput = trim($request->string('q')->toString());
        if ($searchInput === '') {
            $searchInput = trim($request->string('query')->toString());
        }

        return view('products.index', $this->products->listData($categorySlug, $searchInput));
    }

    public function show(Request $request, Product $product): View
    {
        abort_unless($product->is_active, 404);

        $ratingFilter = $request->integer('rating');
        if ($ratingFilter < 1 || $ratingFilter > 5) {
            $ratingFilter = null;
        }

        // Product detail view gets a single source of truth from ProductBrowseService (related items + reviews + stats)
        // so the Blade template can stay presentation-only when we present the page to clients.
        return view('products.show', $this->products->detailData($product, $ratingFilter));
    }
}
