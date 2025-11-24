<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Product;
use App\Services\Admin\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $products
    ) {
    }

    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());
        $products = $this->products->paginate($search);

        return view('admin.barang.products.index', compact('products', 'search'));
    }

    public function create(): View
    {
        return view('admin.barang.products.create', $this->products->formSelections());
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->products->create(
            $request->validated(),
            $request->boolean('is_active'),
            $request->boolean('discount_active'),
            $request->file('product_image')
        );

        return redirect()
            ->route('admin.barang.products.index')
            ->with('status', __('Produk berhasil ditambahkan.'));
    }

    public function edit(Product $product): View
    {
        return view('admin.barang.products.edit', [
            'product' => $product,
            ...$this->products->formSelections(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->products->update(
            $product,
            $request->validated(),
            $request->boolean('is_active'),
            $request->boolean('discount_active'),
            $request->file('product_image')
        );

        return redirect()
            ->route('admin.barang.products.index')
            ->with('status', __('Produk berhasil diperbarui.'));
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->products->delete($product);

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
    public function generateSku(Request $request): JsonResponse
    {
        abort_unless($request->user()?->role?->posisi === 'admin_barang', 403);

        $sku = $this->products->generateSku(
            (string) $request->input('name', ''),
            $request->filled('category_id') ? (int) $request->input('category_id') : null,
            $request->filled('brand_id') ? (int) $request->input('brand_id') : null,
            $request->input('product_id')
        );

        return response()->json([
            'sku' => $sku,
        ]);
    }
}
