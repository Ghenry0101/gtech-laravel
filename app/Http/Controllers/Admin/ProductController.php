<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());
        $products = Product::query()
            ->with('category')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();
        dd($products);
        return view('admin.barang.products.index', compact('products', 'search'));
    }

    public function create(): View
    {
        $categories = Category::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.barang.products.create', compact('categories'));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
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

        return view('admin.barang.products.edit', compact('product', 'categories'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();
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
}
