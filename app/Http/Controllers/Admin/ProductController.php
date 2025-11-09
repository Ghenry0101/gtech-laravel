<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        $this->normalizeDiscount($data);

        if ($request->hasFile('image_product')) {
            $data['image_product'] = $request->file('image_product')->store('products', 'public');
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
        $this->normalizeDiscount($data);

        if ($request->hasFile('image_product')) {
            if ($product->image_product) {
                Storage::disk('public')->delete($product->image_product);
            }
            $data['image_product'] = $request->file('image_product')->store('products', 'public');
        }

        $product->update($data);

        return redirect()
            ->route('admin.barang.products.index')
            ->with('status', __('Produk berhasil diperbarui.'));
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->image_product) {
            Storage::disk('public')->delete($product->image_product);
        }

        $product->delete();

        return redirect()
            ->route('admin.barang.products.index')
            ->with('status', __('Produk berhasil dihapus.'));
    }

    protected function normalizeDiscount(array &$data): void
    {
        $price = max((int) ($data['price'] ?? 0), 0);
        $enabled = filter_var($data['enable_discount'] ?? false, FILTER_VALIDATE_BOOLEAN);
        unset($data['enable_discount']);

        $source = $data['discount_source'] ?? null;
        unset($data['discount_source']);

        $percentage = array_key_exists('discount_percentage', $data)
            ? (float) $data['discount_percentage']
            : null;
        $amount = array_key_exists('discount_amount', $data)
            ? (int) $data['discount_amount']
            : null;

        if (! $enabled || ($percentage === null && $amount === null)) {
            $data['discount_type'] = null;
            $data['discount_percentage'] = null;
            $data['discount_amount'] = null;
            return;
        }

        $type = null;
        if ($source === 'amount' && $amount !== null) {
            $type = 'amount';
        } elseif ($source === 'percentage' && $percentage !== null) {
            $type = 'percentage';
        } elseif ($amount !== null && $amount > 0) {
            $type = 'amount';
        } elseif ($percentage !== null && $percentage > 0) {
            $type = 'percentage';
        } else {
            $type = null;
        }

        if ($type === 'percentage') {
            $percentage = min(max($percentage ?? 0, 0), 100);
            $amount = (int) round($price * ($percentage / 100));
        } elseif ($type === 'amount') {
            $amount = (int) min(max($amount ?? 0, 0), $price);
            $percentage = $price > 0
                ? round(($amount / $price) * 100, 2)
                : 0;
        } else {
            $percentage = null;
            $amount = null;
        }

        $data['discount_type'] = $type;
        $data['discount_percentage'] = $percentage;
        $data['discount_amount'] = $amount;
    }
}
