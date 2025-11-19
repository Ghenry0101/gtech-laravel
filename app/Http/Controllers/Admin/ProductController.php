<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Support\SkuGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());
        $products = Product::query()
            ->with(['category', 'brand'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('brand', fn ($brandQuery) => $brandQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();
        return view('admin.barang.products.index', compact('products', 'search'));
    }

    public function create(): View
    {
        $categories = Category::query()->orderBy('name')->get(['id', 'name']);
        $brands = Brand::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.barang.products.create', compact('categories', 'brands'));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $category = isset($data['category_id']) ? Category::find($data['category_id']) : null;
        $brand = isset($data['brand_id']) ? Brand::find($data['brand_id']) : null;
        $data['sku'] = $this->determineSku(null, $data['name'], $category, $brand);
        $data['is_active'] = $request->boolean('is_active');
        $data = $this->prepareDiscountPayload($data, $request);

        if ($request->hasFile('product_image')) {
            $data['product_image'] = $request->file('product_image')->store('products', 'public');
        }

        Product::create($data);

        return redirect()
            ->route('admin.barang.products.index')
            ->with('status', __('Produk berhasil ditambahkan.'));
    }

    public function edit(Product $product): View
    {
        $categories = Category::query()->orderBy('name')->get(['id', 'name']);
        $brands = Brand::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.barang.products.edit', compact('product', 'categories', 'brands'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();
        $category = isset($data['category_id']) ? Category::find($data['category_id']) : null;
        $brand = isset($data['brand_id']) ? Brand::find($data['brand_id']) : null;
        $data['sku'] = $this->determineSku($product->sku, $data['name'], $category, $brand, $product->getKey());
        $data['is_active'] = $request->boolean('is_active');
        $data = $this->prepareDiscountPayload($data, $request);

        if ($request->hasFile('product_image')) {
            if ($product->product_image) {
                Storage::disk('public')->delete($product->product_image);
            }
            $data['product_image'] = $request->file('product_image')->store('products', 'public');
        }

        $product->update($data);

        return redirect()
            ->route('admin.barang.products.index')
            ->with('status', __('Produk berhasil diperbarui.'));
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->product_image) {
            Storage::disk('public')->delete($product->product_image);
        }

        $product->delete();

        return redirect()
            ->route('admin.barang.products.index')
            ->with('status', __('Produk berhasil dihapus.'));
    }

    /**
     * Normalize and validate discount payload before persisting.
     *
     * @param  array<string, mixed>  $data
     * @throws \Illuminate\Validation\ValidationException
     * @return array<string, mixed>
     */
    protected function prepareDiscountPayload(array $data, Request $request): array
    {
        $hasDiscount = $request->boolean('discount_active');
        unset($data['discount_active']);

        if (! $hasDiscount) {
            $data['discount_percent'] = null;
            $data['discount_price'] = null;
            $data['discount_start'] = null;
            $data['discount_end'] = null;

            return $data;
        }

        $price = (float) ($data['price'] ?? 0);
        $percentInput = $data['discount_percent'] ?? null;
        $finalPriceInput = $data['discount_price'] ?? null;

        if ($percentInput === null && $finalPriceInput === null) {
            throw ValidationException::withMessages([
                'discount_percent' => __('Masukkan persentase atau harga setelah diskon.'),
            ]);
        }

        if ($percentInput !== null) {
            $percent = $this->clamp((float) $percentInput, 0, 100);
            $data['discount_percent'] = number_format($percent, 2, '.', '');
            $data['discount_price'] = number_format($this->priceAfterPercent($price, $percent), 2, '.', '');
        }

        if ($finalPriceInput !== null) {
            $finalPrice = $this->clamp((float) $finalPriceInput, 0, $price);
            $data['discount_price'] = number_format($finalPrice, 2, '.', '');

            if ($percentInput === null && $price > 0) {
                $percent = (($price - $finalPrice) / $price) * 100;
                $data['discount_percent'] = number_format($percent, 2, '.', '');
            }
        }

        $data['discount_start'] = $this->normalizeScheduleValue($data['discount_start'] ?? null);
        $data['discount_end'] = $this->normalizeScheduleValue($data['discount_end'] ?? null);

        return $data;
    }

    protected function normalizeScheduleValue(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    protected function priceAfterPercent(float $price, float $percent): float
    {
        $discounted = $price - ($price * $percent / 100);

        return max($discounted, 0);
    }

    protected function clamp(float $value, float $min, float $max): float
    {
        return max($min, min($max, $value));
    }

    protected function determineSku(?string $input, string $productName, ?Category $category, ?Brand $brand, ?string $ignoreProductId = null): string
    {
        $normalized = $this->normalizeSku($input);

        if ($normalized) {
            return $normalized;
        }

        return SkuGenerator::make()->generateUsingContext(
            productName: $productName,
            categoryName: $category?->name,
            brandName: $brand?->name,
            ignoreProductId: $ignoreProductId,
        );
    }

    protected function normalizeSku($value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        if ($value === '' || str_starts_with(strtolower($value), '[object ')) {
            return null;
        }

        $normalized = preg_replace('/\s+/', '-', $value);
        if ($normalized === null) {
            $normalized = $value;
        }
        $normalized = strtoupper(trim($normalized));
        $normalized = preg_replace('/[^A-Z0-9\-]/', '', $normalized) ?? $normalized;

        return $normalized !== '' ? $normalized : null;
    }

    public function generateSku(Request $request): JsonResponse
    {
        abort_unless($request->user()?->role?->posisi === 'admin_barang', 403);

        $name = trim((string) $request->input('name', ''));
        $productId = $request->input('product_id');
        $category = $request->filled('category_id') ? Category::find($request->input('category_id')) : null;
        $brand = $request->filled('brand_id') ? Brand::find($request->input('brand_id')) : null;

        $sku = SkuGenerator::make()->generateUsingContext(
            productName: $name !== '' ? $name : ($brand?->name ?? 'Produk'),
            categoryName: $category?->name,
            brandName: $brand?->name,
            ignoreProductId: $productId,
        );

        return response()->json([
            'sku' => $sku,
        ]);
    }
}
