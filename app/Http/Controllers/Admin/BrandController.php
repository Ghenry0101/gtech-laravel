<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBrandRequest;
use App\Http\Requests\Admin\UpdateBrandRequest;
use App\Models\Brand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());

        $brands = Brand::query()
            ->withCount('products')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.barang.brands.index', compact('brands', 'search'));
    }

    public function create(): View
    {
        return view('admin.barang.brands.create');
    }

    public function store(StoreBrandRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('brands', 'public');
        }

        Brand::create($data);

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
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            if ($brand->logo) {
                Storage::disk('public')->delete($brand->logo);
            }
            $data['logo'] = $request->file('logo')->store('brands', 'public');
        }

        $brand->update($data);

        return redirect()
            ->route('admin.barang.brands.index')
            ->with('status', __('Brand berhasil diperbarui.'));
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        if ($brand->products()->exists()) {
            return back()->withErrors([
                'brand' => __('Brand tidak dapat dihapus karena masih dipakai produk.'),
            ]);
        }

        if ($brand->logo) {
            Storage::disk('public')->delete($brand->logo);
        }

        $brand->delete();

        return redirect()
            ->route('admin.barang.brands.index')
            ->with('status', __('Brand berhasil dihapus.'));
    }
}
