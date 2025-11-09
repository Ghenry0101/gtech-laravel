<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());
        $categories = Category::query()
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%"))
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        return view('admin.barang.categories.index', compact('categories', 'search'));
    }

    public function create(): View
    {
        return view('admin.barang.categories.create');
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        Category::create($request->validated());

        return redirect()
            ->route('admin.barang.categories.index')
            ->with('status', __('Kategori berhasil ditambahkan.'));
    }

    public function edit(Category $category): View
    {
        return view('admin.barang.categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()
            ->route('admin.barang.categories.index')
            ->with('status', __('Kategori berhasil diperbarui.'));
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return back()->withErrors([
                'category' => __('Kategori tidak dapat dihapus karena masih dipakai produk.'),
            ]);
        }

        $category->delete();

        return redirect()
            ->route('admin.barang.categories.index')
            ->with('status', __('Kategori berhasil dihapus.'));
    }
}
