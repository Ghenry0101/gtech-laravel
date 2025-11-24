<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBrandRequest;
use App\Http\Requests\Admin\UpdateBrandRequest;
use App\Models\Brand;
use App\Services\Admin\BrandService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function __construct(
        private readonly BrandService $brands
    ) {
    }

    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());

        $brands = $this->brands->paginate($search);

        return view('admin.barang.brands.index', compact('brands', 'search'));
    }

    public function create(): View
    {
        return view('admin.barang.brands.create');
    }

    public function store(StoreBrandRequest $request): RedirectResponse
    {
        $this->brands->create($request->validated(), $request->file('logo'));

        return redirect()
            ->route('admin.barang.brands.index')
            ->with('status', __('Brand berhasil ditambahkan.'));
    }

    public function edit(Brand $brand): View
    {
        return view('admin.barang.brands.edit', compact('brand'));
    }

    public function update(UpdateBrandRequest $request, Brand $brand): RedirectResponse
    {
        $this->brands->update($brand, $request->validated(), $request->file('logo'));

        return redirect()
            ->route('admin.barang.brands.index')
            ->with('status', __('Brand berhasil diperbarui.'));
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        if (! $this->brands->delete($brand)) {
            return back()->withErrors([
                'brand' => __('Brand tidak dapat dihapus karena masih dipakai produk.'),
            ]);
        }

        return redirect()
            ->route('admin.barang.brands.index')
            ->with('status', __('Brand berhasil dihapus.'));
    }
}
